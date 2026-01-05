<?php
/* ================================
   TOSSEE – SIMPLE PROFILE GETTER
   Paprastas profile endpoint per admin-post.php
================================ */

add_action('admin_post_nopriv_tossee_get_profile', 'tossee_simple_get_profile');
add_action('admin_post_tossee_get_profile', 'tossee_simple_get_profile');

function tossee_simple_get_profile() {
    header('Content-Type: application/json; charset=utf-8');

    // 1. Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. Check auth
    if (empty($_SESSION['tossee_id'])) {
        echo json_encode(['error' => 'not_logged_in']);
        exit;
    }

    $tossee_id = $_SESSION['tossee_id'];

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // 3. Get ALL user data including photo
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, tossee_id, username, email, dob, photo, created_at FROM $table WHERE tossee_id = %s LIMIT 1",
            $tossee_id
        ),
        ARRAY_A
    );

    if (!$user) {
        echo json_encode(['error' => 'user_not_found']);
        exit;
    }

    // 4. Return user data
    echo json_encode($user);
    exit;
}
