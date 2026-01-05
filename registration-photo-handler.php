<?php
/* ================================
   TOSSEE – GET REGISTRATION PHOTO
================================ */

add_action('admin_post_nopriv_tossee_get_registration_photo', 'tossee_get_registration_photo_handler');
add_action('admin_post_tossee_get_registration_photo', 'tossee_get_registration_photo_handler');

function tossee_get_registration_photo_handler() {
    header('Content-Type: application/json; charset=utf-8');

    // 1. Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. Check auth
    if (empty($_SESSION['tossee_id'])) {
        echo json_encode(['error' => 'no_auth']);
        exit;
    }

    $tossee_id = $_SESSION['tossee_id'];

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // 3. Get registration photo (NOT profile_photo)
    $photo = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT photo FROM $table WHERE tossee_id = %s LIMIT 1",
            $tossee_id
        )
    );

    if (!$photo) {
        echo json_encode(['error' => 'photo_not_found']);
        exit;
    }

    // 4. Return photo
    echo json_encode(['photo' => $photo]);
    exit;
}
