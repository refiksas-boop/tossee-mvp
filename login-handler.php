/* ================================
   TOSSEE – CUSTOM LOGIN HANDLER
================================ */

add_action('admin_post_nopriv_tossee_custom_login', 'tossee_custom_login_handler');
add_action('admin_post_tossee_custom_login',        'tossee_custom_login_handler');

function tossee_custom_login_handler() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    /* --- Validacija --- */
    if (empty($_POST['user_login']) || empty($_POST['user_pass'])) {
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('login_error', 'missing_fields', $back) );
        exit;
    }

    $user_login = sanitize_text_field( wp_unslash($_POST['user_login']) );
    $user_pass  = (string) $_POST['user_pass'];

    /* --- Tikrinam ar vartotojas egzistuoja (by email or username) --- */
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE email = %s OR username = %s LIMIT 1",
            $user_login,
            $user_login
        )
    );

    if (!$user) {
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('login_error', 'user_not_found', $back) );
        exit;
    }

    /* --- Tikrinam slaptažodį --- */
    if (!password_verify($user_pass, $user->password_hash)) {
        $back = wp_get_referer() ?: home_url('/login');
        wp_safe_redirect( add_query_arg('login_error', 'invalid_credentials', $back) );
        exit;
    }

    /* --- Set session --- */
    if (!session_id()) {
        session_start();
    }
    $_SESSION['tossee_id'] = $user->tossee_id;
    $_SESSION['username'] = $user->username;

    /* --- Log successful login --- */
    error_log("User logged in: " . $user->tossee_id);

    /* --- Redirect į chat --- */
    $redirect = "https://chat.tossee.com/?uid=" . urlencode($user->tossee_id);
    wp_safe_redirect($redirect);
    exit;
}
