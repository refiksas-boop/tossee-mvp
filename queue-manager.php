<?php
/**
 * Tossee - Queue Manager with MySQL
 * Manages user matching queue using WordPress database
 * Location: /chat/api/queue-manager.php
 */

// Load WordPress
$wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    // Fallback paths
    $possible_paths = [
        dirname(dirname(dirname(__FILE__))) . '/wp-load.php',
        '../../../wp-load.php',
        '../../wp-load.php'
    ];
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }
}

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session
if (!session_id()) {
    @session_start();
}

global $wpdb;

// Get request data
$request_body = file_get_contents('php://input');
$body = json_decode($request_body, true);

$action = $body['action'] ?? '';
$userId = $body['userId'] ?? '';

if (!$userId) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'Missing userId'
    ]);
    exit;
}

// Table names
$queue_table = $wpdb->prefix . 'tossee_queue';
$rooms_table = $wpdb->prefix . 'tossee_rooms';

// Create tables if they don't exist
$charset_collate = $wpdb->get_charset_collate();

$queue_sql = "CREATE TABLE IF NOT EXISTS $queue_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) UNIQUE KEY,
    joined_at BIGINT NOT NULL,
    INDEX idx_joined_at (joined_at)
) $charset_collate;";

$rooms_sql = "CREATE TABLE IF NOT EXISTS $rooms_table (
    room_id VARCHAR(50) PRIMARY KEY,
    user1_id VARCHAR(255) NOT NULL,
    user2_id VARCHAR(255) NOT NULL,
    created_at BIGINT NOT NULL,
    INDEX idx_users (user1_id, user2_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($queue_sql);
dbDelta($rooms_sql);

// Current timestamp
$now = time() * 1000;

// Clean old queue entries (older than 60 seconds)
$wpdb->query($wpdb->prepare(
    "DELETE FROM $queue_table WHERE joined_at < %d",
    $now - 60000
));

// Clean old rooms (older than 1 hour)
$wpdb->query($wpdb->prepare(
    "DELETE FROM $rooms_table WHERE created_at < %d",
    $now - 3600000
));

// Log request
error_log("[Tossee Queue] Action: $action | UserId: $userId");

// For 'join' action, always clean up old rooms first to start fresh
if ($action === 'join') {
    $deleted = $wpdb->delete($rooms_table, ['user1_id' => $userId], ['%s']);
    $deleted += $wpdb->delete($rooms_table, ['user2_id' => $userId], ['%s']);

    if ($deleted > 0) {
        error_log("[Tossee Queue] Deleted $deleted old room(s) for user $userId");
    }
}

// Check if user is already in a room (only for 'check' action)
if ($action === 'check') {
    $room = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $rooms_table WHERE user1_id = %s OR user2_id = %s LIMIT 1",
        $userId,
        $userId
    ));

    if ($room) {
        $partnerId = ($room->user1_id === $userId) ? $room->user2_id : $room->user1_id;

        error_log("[Tossee Queue] User $userId already in room: $room->room_id");

        echo json_encode([
            'ok' => true,
            'status' => 'matched',
            'roomId' => $room->room_id,
            'partnerId' => $partnerId,
            'users' => [$room->user1_id, $room->user2_id]
        ]);
        exit;
    }
}

//
// ACTION: CHECK
//
if ($action === 'check') {
    $inQueue = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $queue_table WHERE user_id = %s",
        $userId
    ));

    if ($inQueue > 0) {
        $queueSize = $wpdb->get_var("SELECT COUNT(*) FROM $queue_table");

        error_log("[Tossee Queue] User $userId in queue (size: $queueSize)");

        echo json_encode([
            'ok' => true,
            'status' => 'waiting',
            'queueSize' => (int)$queueSize
        ]);
    } else {
        error_log("[Tossee Queue] User $userId not in queue");

        echo json_encode([
            'ok' => true,
            'status' => 'not_in_queue',
            'message' => 'Use action=join to enter queue'
        ]);
    }
    exit;
}

//
// ACTION: JOIN
//
if ($action === 'join') {

    // Find a partner (someone other than this user)
    $partner = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $queue_table WHERE user_id != %s ORDER BY joined_at ASC LIMIT 1",
        $userId
    ));

    if (!$partner) {
        // No partner found - add to queue

        // Check if already in queue
        $alreadyInQueue = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $queue_table WHERE user_id = %s",
            $userId
        ));

        if (!$alreadyInQueue) {
            $wpdb->replace(
                $queue_table,
                [
                    'user_id' => $userId,
                    'joined_at' => $now
                ],
                ['%s', '%d']
            );
        }

        $queueSize = $wpdb->get_var("SELECT COUNT(*) FROM $queue_table");

        error_log("[Tossee Queue] User $userId added to queue (size: $queueSize)");

        echo json_encode([
            'ok' => true,
            'status' => 'waiting',
            'queueSize' => (int)$queueSize
        ]);
        exit;
    }

    // MATCHED! Create room
    $roomId = 'room_' . bin2hex(random_bytes(8));

    // Remove both users from queue
    $wpdb->delete($queue_table, ['user_id' => $partner->user_id], ['%s']);
    $wpdb->delete($queue_table, ['user_id' => $userId], ['%s']);

    // Create room
    $wpdb->insert(
        $rooms_table,
        [
            'room_id' => $roomId,
            'user1_id' => $userId,
            'user2_id' => $partner->user_id,
            'created_at' => $now
        ],
        ['%s', '%s', '%s', '%d']
    );

    error_log("[Tossee Queue] MATCHED! User $userId <-> $partner->user_id | Room: $roomId");

    echo json_encode([
        'ok' => true,
        'status' => 'matched',
        'roomId' => $roomId,
        'partnerId' => $partner->user_id,
        'users' => [$userId, $partner->user_id]
    ]);
    exit;
}

// Unknown action
http_response_code(400);
echo json_encode([
    'ok' => false,
    'message' => 'Unknown action: ' . $action
]);
