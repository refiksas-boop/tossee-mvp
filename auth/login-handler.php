<?php
/* ================================
   TOSSEE – CUSTOM LOGIN HANDLER
   Authenticates custom users (NOT WP users)
================================ */

add_action('admin_post_nopriv_tossee_custom_login', 'tossee_custom_login_handler');
add_action('admin_post_tossee_custom_login',        'tossee_custom_login_handler');

function tossee_custom_login_handler() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    tossee_log("Login handler called", 'info');

    // Validate inputs
    if (empty($_POST['user_login']) || empty($_POST['user_pass'])) {
        tossee_log("Login failed: missing fields", 'error');
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'missing_fields', $back) );
        exit;
    }

    $login = sanitize_text_field( wp_unslash($_POST['user_login']) );
    $pass  = (string) $_POST['user_pass'];

    // Try to find user by username or email
    $user = null;

    // Check if it's an email
    if (is_email($login)) {
        $user = tossee_get_user_by_email($login);
    } else {
        $user = tossee_get_user_by_username($login);
    }

    // User not found
    if (!$user) {
        tossee_log("Login failed: user not found - {$login}", 'error');
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'invalid_credentials', $back) );
        exit;
    }

    // Verify password
    if (!password_verify($pass, $user->password_hash)) {
        tossee_log("Login failed: invalid password for user {$user->username}", 'error');
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'invalid_credentials', $back) );
        exit;
    }

    // Login successful - set session
    tossee_set_user_session($user->tossee_id);

    tossee_log("User logged in successfully: {$user->username} ({$user->tossee_id})", 'info');

    // Redirect to profile or chat
    $redirect = home_url('/my-account');

    // Check if redirect_to parameter is set
    if (!empty($_POST['redirect_to'])) {
        $redirect = esc_url_raw($_POST['redirect_to']);
    } elseif (!empty($_GET['redirect_to'])) {
        $redirect = esc_url_raw($_GET['redirect_to']);
    }

    wp_safe_redirect($redirect);
    exit;
}
