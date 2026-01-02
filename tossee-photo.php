<?php
/* ================================
   TOSSEE – PHOTO HANDLER
   Serves registration photos from database
================================ */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

if (!session_id()) session_start();

// Check if user is logged in
if (empty($_SESSION['tossee_id'])) {
    http_response_code(401);
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'tossee_users';
$tossee_id = $_SESSION['tossee_id'];

// Get user's photo from database
$photo = $wpdb->get_var(
    $wpdb->prepare("SELECT photo FROM $table WHERE tossee_id = %s", $tossee_id)
);

if (!$photo) {
    http_response_code(404);
    exit;
}

// Photo is stored as base64 data URL (e.g., "data:image/jpeg;base64,/9j/4AAQ...")
// Extract the base64 part
if (strpos($photo, 'data:image') === 0) {
    // Extract content type and base64 data
    preg_match('/data:(image\/[a-z]+);base64,(.+)/', $photo, $matches);

    if (count($matches) === 3) {
        $content_type = $matches[1];
        $base64_data = $matches[2];

        // Decode base64
        $image_data = base64_decode($base64_data);

        // Set appropriate headers
        header('Content-Type: ' . $content_type);
        header('Content-Length: ' . strlen($image_data));
        header('Cache-Control: private, max-age=86400');

        // Output image
        echo $image_data;
        exit;
    }
}

// If we get here, photo format is invalid
http_response_code(500);
exit;
