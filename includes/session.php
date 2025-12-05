<?php
/**
 * Tossee Session Management
 * Handles all session-related functionality for custom user authentication
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Start session if not already started
 */
function tossee_start_session() {
    if (!session_id()) {
        session_start();
    }
}

/**
 * Check if user is authenticated
 * @return bool
 */
function tossee_is_authenticated() {
    tossee_start_session();
    return isset($_SESSION['tossee_id']) && !empty($_SESSION['tossee_id']);
}

/**
 * Get current logged-in user's tossee_id
 * @return string|null
 */
function tossee_get_current_user_id() {
    tossee_start_session();
    return isset($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null;
}

/**
 * Get full user data for currently logged-in user
 * @return object|null User data or null if not authenticated
 */
function tossee_get_current_user() {
    if (!tossee_is_authenticated()) {
        return null;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';
    $tossee_id = tossee_get_current_user_id();

    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE tossee_id = %s", $tossee_id)
    );

    return $user;
}

/**
 * Set user session after successful login/registration
 * @param string $tossee_id
 */
function tossee_set_user_session($tossee_id) {
    tossee_start_session();

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['tossee_id'] = $tossee_id;
    $_SESSION['tossee_login_time'] = time();
}

/**
 * Destroy user session (logout)
 */
function tossee_destroy_session() {
    tossee_start_session();

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();
}

/**
 * Require authentication - redirect to login if not authenticated
 * @param string $redirect_url Where to redirect if not authenticated
 */
function tossee_require_auth($redirect_url = null) {
    if (!tossee_is_authenticated()) {
        if ($redirect_url === null) {
            $redirect_url = home_url('/login');
        }
        wp_safe_redirect($redirect_url);
        exit;
    }
}

/**
 * Check if user exists by tossee_id
 * @param string $tossee_id
 * @return bool
 */
function tossee_user_exists($tossee_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $count = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE tossee_id = %s", $tossee_id)
    );

    return $count > 0;
}

/**
 * Get user by tossee_id
 * @param string $tossee_id
 * @return object|null
 */
function tossee_get_user_by_id($tossee_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE tossee_id = %s", $tossee_id)
    );
}

/**
 * Get user by email
 * @param string $email
 * @return object|null
 */
function tossee_get_user_by_email($email) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE email = %s", $email)
    );
}

/**
 * Get user by username
 * @param string $username
 * @return object|null
 */
function tossee_get_user_by_username($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE username = %s", $username)
    );
}

// Initialize session on every request
add_action('init', 'tossee_start_session', 1);
