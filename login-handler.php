<?php
/* ================================
   TOSSEE – CUSTOM LOGIN HANDLER
================================ */

add_action('admin_post_nopriv_tossee_custom_login', 'tossee_custom_login_handler');
add_action('admin_post_tossee_custom_login',        'tossee_custom_login_handler');

if ( ! function_exists( 'tossee_custom_login_handler' ) ) {
    function tossee_custom_login_handler() {
        tossee_log( 'Login handler called', 'info' );
        tossee_log( 'POST data: ' . print_r($_POST, true), 'info' );
        tossee_log( 'Request method: ' . $_SERVER['REQUEST_METHOD'], 'info' );

        // Support both field names: user_email OR username_or_email
        $email_field = !empty($_POST['user_email']) ? $_POST['user_email'] :
                       (!empty($_POST['username_or_email']) ? $_POST['username_or_email'] : '');

        if ( empty( $email_field ) || empty( $_POST['user_pass'] ) ) {
            tossee_log( 'Login failed: missing fields', 'error' );
            tossee_log( 'email_field: ' . ($email_field ?: 'NOT SET'), 'error' );
            tossee_log( 'user_pass: ' . (isset($_POST['user_pass']) ? 'EXISTS' : 'NOT SET'), 'error' );
            $back = wp_get_referer() ?: home_url( '/login' );
            wp_safe_redirect( add_query_arg( 'error', 'missing_fields', $back ) );
            exit;
        }

        $email_or_username = sanitize_text_field( wp_unslash( $email_field ) );
        $pass  = (string) $_POST['user_pass'];

        // Try to find user by email first, then by username
        $user = tossee_get_user_by_email( $email_or_username );

        if ( ! $user ) {
            // Try by username
            global $wpdb;
            $table = $wpdb->prefix . 'tossee_users';
            $user = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM $table WHERE username = %s", $email_or_username )
            );
        }

        if ( ! $user ) {
            tossee_log( "Login failed: user not found - {$email_or_username}", 'error' );
            $back = wp_get_referer() ?: home_url( '/login' );
            wp_safe_redirect( add_query_arg( 'error', 'notfound', $back ) );
            exit;
        }

        if ( ! password_verify( $pass, $user->password_hash ) ) {
            tossee_log( "Login failed: wrong password - {$email_or_username}", 'error' );
            $back = wp_get_referer() ?: home_url( '/login' );
            wp_safe_redirect( add_query_arg( 'error', 'wrongpass', $back ) );
            exit;
        }

        // Check if user is blocked (if column exists)
        $is_blocked = isset($user->is_blocked) ? (int)$user->is_blocked : 0;
        if ( $is_blocked === 1 ) {
            tossee_log( "Login failed: user blocked - {$email_or_username}", 'error' );
            $back = wp_get_referer() ?: home_url( '/login' );
            wp_safe_redirect( add_query_arg( 'error', 'user_blocked', $back ) );
            exit;
        }

        tossee_set_uid_cookie( $user->tossee_id );

        tossee_log( "User logged in successfully: {$user->username} ({$user->tossee_id})", 'info' );

        // Redirect to chat subdomain with uid
        $redirect_url = "https://chat.tossee.com/?uid=" . urlencode($user->tossee_id);

        wp_safe_redirect( $redirect_url );
        exit;
    }
}
