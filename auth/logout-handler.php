<?php
/* ================================
   TOSSEE – CUSTOM LOGOUT HANDLER
   Destroys session for custom users
================================ */

add_action('admin_post_tossee_custom_logout', 'tossee_custom_logout_handler');
add_action('admin_post_nopriv_tossee_custom_logout', 'tossee_custom_logout_handler');

function tossee_custom_logout_handler() {
    $user_id = tossee_get_current_user_id();

    if ($user_id) {
        tossee_log("User logging out: {$user_id}", 'info');
    }

    // Destroy session
    tossee_destroy_session();

    // Redirect to login page
    $redirect = home_url('/login');

    // Check if custom redirect is specified
    if (!empty($_GET['redirect_to'])) {
        $redirect = esc_url_raw($_GET['redirect_to']);
    }

    wp_safe_redirect($redirect);
    exit;
}

/**
 * Template tag for logout URL
 * Usage: <a href="<?php echo tossee_logout_url(); ?>">Logout</a>
 */
function tossee_logout_url($redirect_to = null) {
    $url = admin_url('admin-post.php?action=tossee_custom_logout');

    if ($redirect_to) {
        $url = add_query_arg('redirect_to', urlencode($redirect_to), $url);
    }

    return $url;
}
