<?php
/**
 * Tossee - Signaling Manager with MySQL
 * Manages WebRTC signaling messages using WordPress database
 */

// Load WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

global $wpdb;

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request data
$body = json_decode(file_get_contents('php://input'), true);
$roomId = $body['roomId'] ?? '';
$userId = $body['userId'] ?? $body['user_id'] ?? '';
$type = $body['type'] ?? '';

if (!$roomId) {
    echo json_encode(['ok' => false, 'message' => 'Missing roomId']);
    exit;
}

$signals_table = $wpdb->prefix . 'tossee_signals';

// Create table if not exists
$wpdb->query("CREATE TABLE IF NOT EXISTS $signals_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    signal_type VARCHAR(50) NOT NULL,
    signal_data TEXT NOT NULL,
    created_at BIGINT NOT NULL,
    INDEX idx_room (room_id),
    INDEX idx_created (created_at)
)");

$now = time() * 1000;

// Clean old signals (>5 minutes)
$wpdb->query($wpdb->prepare(
    "DELETE FROM $signals_table WHERE created_at < %d",
    $now - 300000
));

// ACTION: Send signal
if ($type === 'offer' || $type === 'answer' || $type === 'ice-candidate' || $type === 'candidate') {

    $signalData = $body['data'] ?? $body['offer'] ?? $body['answer'] ?? $body['candidate'] ?? null;

    if (!$signalData) {
        echo json_encode(['ok' => false, 'message' => 'Missing signal data']);
        exit;
    }

    // Store signal
    $wpdb->insert($signals_table, [
        'room_id' => $roomId,
        'user_id' => $userId,
        'signal_type' => $type,
        'signal_data' => json_encode($signalData),
        'created_at' => $now
    ], ['%s', '%s', '%s', '%s', '%d']);

    error_log("[Tossee Signal] Stored $type for room $roomId from user $userId");

    echo json_encode([
        'ok' => true,
        'message' => 'Signal stored'
    ]);
    exit;
}

// ACTION: Get signals (poll)
if ($type === 'poll' || $type === 'check') {

    // Get all signals for this room from OTHER users
    $signals = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $signals_table
         WHERE room_id = %s
         AND user_id != %s
         ORDER BY created_at ASC",
        $roomId,
        $userId
    ));

    $offers = [];
    $answers = [];
    $candidates = [];

    foreach ($signals as $signal) {
        $data = json_decode($signal->signal_data, true);

        switch ($signal->signal_type) {
            case 'offer':
                $offers[] = $data;
                break;
            case 'answer':
                $answers[] = $data;
                break;
            case 'ice-candidate':
            case 'candidate':
                $candidates[] = $data;
                break;
        }
    }

    echo json_encode([
        'ok' => true,
        'roomData' => [
            'roomId' => $roomId,
            'offers' => $offers,
            'answers' => $answers,
            'candidates' => $candidates
        ]
    ]);
    exit;
}

// Unknown action
echo json_encode(['ok' => false, 'message' => 'Unknown type: ' . $type]);
