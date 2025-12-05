/* ================================
   TOSSEE – CUSTOM REGISTER HANDLER
   (NE kuria WP users)
================================ */

add_action('admin_post_nopriv_tossee_custom_register', 'tossee_custom_register_handler');
add_action('admin_post_tossee_custom_register',        'tossee_custom_register_handler');

function tossee_custom_register_handler() {
	
	
	error_log("CUSTOM HANDLER PASIEKTAS");
error_log("POST DATA: " . print_r($_POST, true));


    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    /* --- Validacija --- */
    if (
        empty($_POST['user_login']) ||
        empty($_POST['user_email']) ||
        empty($_POST['user_pass']) ||
        empty($_POST['dob']) ||
        empty($_POST['photo'])
    ) {
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('reg_error', 'missing_fields', $back) );
        exit;
    }

    $username = sanitize_user( wp_unslash($_POST['user_login']) );
    $email    = sanitize_email( wp_unslash($_POST['user_email']) );
    $pass     = (string) $_POST['user_pass'];
    $dob      = sanitize_text_field( $_POST['dob'] );
    $photo    = wp_unslash( $_POST['photo'] );

    /* --- Tikrinam ar email jau egzistuoja --- */
    $exists = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email)
    );

    if ( $exists > 0 ) {
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('reg_error', 'email_exists', $back) );
        exit;
    }

    /* --- Generuojam ID --- */
    $tossee_id = 'tossee_' . wp_generate_password(8, false, false);

    /* --- Hashinam --- */
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    /* --- Išsaugom į custom DB --- */
    $inserted = $wpdb->insert(
        $table,
        [
            'tossee_id'     => $tossee_id,
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $hash,
            'dob'           => $dob,
            'photo'         => $photo,
            'created_at'    => current_time('mysql'),
        ],
        [ '%s','%s','%s','%s','%s','%s','%s' ]
    );

    if ( ! $inserted ) {
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('reg_error', 'save_failed', $back) );
        exit;
    }

    /* --- Set session --- */
    if ( ! session_id() ) session_start();
    $_SESSION['tossee_id'] = $tossee_id;

    /* --- Redirect į CHAT su uid --- */
    $redirect = "https://chat.tossee.com/?uid=" . urlencode($tossee_id);

    wp_safe_redirect($redirect);
    exit;
}
