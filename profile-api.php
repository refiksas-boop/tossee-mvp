<?php
/**
 * Tossee - Profile API
 * Returns user profile data as JSON
 * Location: /profile-api.php (on live server)
 */

// Load WordPress
require_once('../wp-load.php');

// Start session
if (!session_id()) {
    session_start();
}

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'tossee_users';

try {
    // Get user ID from session or URL parameter
    $tossee_id = null;

    if (!empty($_GET['uid'])) {
        $tossee_id = sanitize_text_field($_GET['uid']);
    } elseif (isset($_SESSION['tossee_id'])) {
        $tossee_id = $_SESSION['tossee_id'];
    }

    if (!$tossee_id) {
        throw new Exception('User not logged in');
    }

    // Fetch user from database
    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE tossee_id = %s", $tossee_id),
        ARRAY_A
    );

    if (!$user) {
        throw new Exception('User not found');
    }

    // Remove sensitive data
    unset($user['password_hash']);

    // Format date of birth
    if (!empty($user['dob'])) {
        $user['dob_formatted'] = date('F j, Y', strtotime($user['dob']));
    }

    // Calculate age
    if (!empty($user['dob'])) {
        $dob = new DateTime($user['dob']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        $user['age'] = $age;
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
