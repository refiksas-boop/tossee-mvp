<?php
/* ================================
   TOSSEE – CUSTOM LOGIN HANDLER
================================ */

add_action('admin_post_nopriv_tossee_custom_login', 'tossee_custom_login_handler');
add_action('admin_post_tossee_custom_login',        'tossee_custom_login_handler');

function tossee_custom_login_handler() {

    error_log("LOGIN HANDLER PASIEKTAS");
    error_log("POST DATA: " . print_r($_POST, true));

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    /* --- Validacija --- */
    if ( empty($_POST['user_email']) || empty($_POST['user_pass']) ) {
        error_log("LOGIN FAILED: Missing fields");
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'missing_fields', $back) );
        exit;
    }

    $email = sanitize_email( wp_unslash($_POST['user_email']) );
    $pass  = (string) $_POST['user_pass'];

    /* --- Tikrinam ar user egzistuoja --- */
    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE email = %s", $email)
    );

    if ( ! $user ) {
        error_log("LOGIN FAILED: User not found - {$email}");
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'invalid_credentials', $back) );
        exit;
    }

    /* --- Tikrinam password --- */
    if ( ! password_verify($pass, $user->password_hash) ) {
        error_log("LOGIN FAILED: Wrong password - {$email}");
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'invalid_credentials', $back) );
        exit;
    }

    /* --- Tikrinam ar blocked --- */
    // Check if user has is_blocked column (might not exist in old table structure)
    $is_blocked = isset($user->is_blocked) ? (int)$user->is_blocked : 0;

    if ( $is_blocked === 1 ) {
        error_log("LOGIN FAILED: User blocked - {$email}");
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'user_blocked', $back) );
        exit;
    }

    /* --- Set session --- */
    if ( ! session_id() ) session_start();
    $_SESSION['tossee_id'] = $user->tossee_id;

    error_log("LOGIN SUCCESS: {$user->username} ({$user->tossee_id})");

    /* --- Redirect į CHAT su uid --- */
    $redirect = "https://chat.tossee.com/?uid=" . urlencode($user->tossee_id);

    wp_safe_redirect($redirect);
    exit;
}
