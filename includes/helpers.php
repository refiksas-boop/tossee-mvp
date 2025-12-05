<?php
/**
 * Tossee Helper Functions
 * Utility functions used throughout the application
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Calculate age from date of birth
 * @param string $dob Date of birth in Y-m-d format
 * @return int Age in years
 */
function tossee_calculate_age($dob) {
    if (empty($dob)) {
        return 0;
    }

    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;

    return $age;
}

/**
 * Validate age requirement (18+)
 * @param string $dob Date of birth in Y-m-d format
 * @return bool True if 18 or older
 */
function tossee_is_valid_age($dob) {
    return tossee_calculate_age($dob) >= 18;
}

/**
 * Generate unique tossee_id
 * @return string
 */
function tossee_generate_id() {
    return 'tossee_' . wp_generate_password(12, false, false);
}

/**
 * Validate photo data (base64)
 * @param string $photo Base64 encoded photo
 * @return bool
 */
function tossee_validate_photo($photo) {
    if (empty($photo)) {
        return false;
    }

    // Check if it's a valid base64 data URL
    if (!preg_match('/^data:image\/(png|jpg|jpeg|gif);base64,/', $photo)) {
        return false;
    }

    // Extract base64 data
    $photo_data = preg_replace('/^data:image\/\w+;base64,/', '', $photo);
    $decoded = base64_decode($photo_data, true);

    if ($decoded === false) {
        return false;
    }

    // Check file size (max 5MB for base64)
    $size = strlen($decoded);
    $max_size = 5 * 1024 * 1024; // 5MB

    return $size <= $max_size;
}

/**
 * Sanitize username with additional validation
 * @param string $username
 * @return string
 */
function tossee_sanitize_username($username) {
    $username = sanitize_user($username, true);
    // Remove spaces and special characters
    $username = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
    return $username;
}

/**
 * Check if username is available
 * @param string $username
 * @param string $exclude_id Optional tossee_id to exclude (for updates)
 * @return bool True if available
 */
function tossee_is_username_available($username, $exclude_id = null) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE username = %s", $username);

    if ($exclude_id) {
        $sql .= $wpdb->prepare(" AND tossee_id != %s", $exclude_id);
    }

    $count = $wpdb->get_var($sql);
    return $count == 0;
}

/**
 * Check if email is available
 * @param string $email
 * @param string $exclude_id Optional tossee_id to exclude (for updates)
 * @return bool True if available
 */
function tossee_is_email_available($email, $exclude_id = null) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email);

    if ($exclude_id) {
        $sql .= $wpdb->prepare(" AND tossee_id != %s", $exclude_id);
    }

    $count = $wpdb->get_var($sql);
    return $count == 0;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function tossee_format_date($date, $format = 'F j, Y') {
    if (empty($date)) {
        return '-';
    }

    return date($format, strtotime($date));
}

/**
 * Get user display name
 * @param object $user User object
 * @return string
 */
function tossee_get_display_name($user) {
    if (!$user) {
        return 'Unknown User';
    }

    $first = isset($user->first_name) ? trim($user->first_name) : '';
    $last = isset($user->last_name) ? trim($user->last_name) : '';

    if ($first && $last) {
        return $first . ' ' . $last;
    } elseif ($first) {
        return $first;
    } else {
        return $user->username;
    }
}

/**
 * Parse hobbies (can be JSON array or comma-separated string)
 * @param mixed $hobbies
 * @return array
 */
function tossee_parse_hobbies($hobbies) {
    if (empty($hobbies)) {
        return array();
    }

    if (is_array($hobbies)) {
        return $hobbies;
    }

    // Try JSON decode
    $decoded = json_decode($hobbies, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    // Try comma-separated
    return array_map('trim', explode(',', $hobbies));
}

/**
 * Format hobbies for storage
 * @param array $hobbies
 * @return string JSON encoded
 */
function tossee_format_hobbies_for_storage($hobbies) {
    if (empty($hobbies) || !is_array($hobbies)) {
        return '';
    }

    return json_encode($hobbies);
}

/**
 * Log custom message for debugging
 * @param string $message
 * @param string $type
 */
function tossee_log($message, $type = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        error_log("[TOSSEE-{$type}] " . $message);
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function tossee_generate_csrf_token() {
    tossee_start_session();
    if (!isset($_SESSION['tossee_csrf_token'])) {
        $_SESSION['tossee_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['tossee_csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function tossee_verify_csrf_token($token) {
    tossee_start_session();
    if (!isset($_SESSION['tossee_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['tossee_csrf_token'], $token);
}

/**
 * Get photo URL or base64 data
 * @param string $photo
 * @return string
 */
function tossee_get_photo_url($photo) {
    if (empty($photo)) {
        return 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
    }

    // If it's already a data URL, return as is
    if (strpos($photo, 'data:image') === 0) {
        return $photo;
    }

    // If it's a file path, convert to URL
    if (file_exists($photo)) {
        return content_url(str_replace(WP_CONTENT_DIR, '', $photo));
    }

    return $photo;
}
