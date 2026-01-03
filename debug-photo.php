<?php
/* ================================
   TOSSEE – DEBUG PHOTO
   Temporary debug endpoint
================================ */

add_action('admin_post_nopriv_tossee_debug_photo', 'tossee_debug_photo_handler');
add_action('admin_post_tossee_debug_photo', 'tossee_debug_photo_handler');

function tossee_debug_photo_handler() {
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $tossee_id = $_SESSION['tossee_id'] ?? null;

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // Get ALL data for this user
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE tossee_id = %s LIMIT 1",
            $tossee_id
        ),
        ARRAY_A
    );

    // Check if photo exists and its length
    $debug = [
        'session_tossee_id' => $tossee_id,
        'user_found' => $user ? 'yes' : 'no',
        'photo_exists' => isset($user['photo']) ? 'yes' : 'no',
        'photo_length' => isset($user['photo']) ? strlen($user['photo']) : 0,
        'photo_preview' => isset($user['photo']) ? substr($user['photo'], 0, 100) : null,
        'username' => $user['username'] ?? null,
    ];

    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}
