/* ================================
   TOSSEE – CUSTOM REGISTER HANDLER
   (NE kuria WP users)
================================ */

add_action('admin_post_nopriv_tossee_custom_register', 'tossee_custom_register_handler');
add_action('admin_post_tossee_custom_register',        'tossee_custom_register_handler');

function tossee_custom_register_handler() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    tossee_log("Registration handler called", 'info');

    /* --- Validacija --- */
    if (
        empty($_POST['user_login']) ||
        empty($_POST['user_email']) ||
        empty($_POST['user_pass']) ||
        empty($_POST['dob']) ||
        empty($_POST['photo'])
    ) {
        tossee_log("Registration failed: missing fields", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'missing_fields', $back) );
        exit;
    }

    // Sanitize inputs
    $username = tossee_sanitize_username( wp_unslash($_POST['user_login']) );
    $email    = sanitize_email( wp_unslash($_POST['user_email']) );
    $pass     = (string) $_POST['user_pass'];
    $dob      = sanitize_text_field( $_POST['dob'] );
    $photo    = wp_unslash( $_POST['photo'] );

    // Validate username length
    if (strlen($username) < 3) {
        tossee_log("Registration failed: username too short", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'username_short', $back) );
        exit;
    }

    // Validate email format
    if (!is_email($email)) {
        tossee_log("Registration failed: invalid email", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'invalid_email', $back) );
        exit;
    }

    // Validate password length
    if (strlen($pass) < 6) {
        tossee_log("Registration failed: password too short", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'password_short', $back) );
        exit;
    }

    // Validate age (18+)
    if (!tossee_is_valid_age($dob)) {
        tossee_log("Registration failed: user under 18", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'age_restriction', $back) );
        exit;
    }

    // Validate photo
    if (!tossee_validate_photo($photo)) {
        tossee_log("Registration failed: invalid photo", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'invalid_photo', $back) );
        exit;
    }

    /* --- Check if username already exists --- */
    if (!tossee_is_username_available($username)) {
        tossee_log("Registration failed: username exists - {$username}", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'username_exists', $back) );
        exit;
    }

    /* --- Check if email already exists --- */
    if (!tossee_is_email_available($email)) {
        tossee_log("Registration failed: email exists - {$email}", 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'email_exists', $back) );
        exit;
    }

    /* --- Generate unique ID --- */
    $tossee_id = tossee_generate_id();

    /* --- Hash password --- */
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    /* --- Save to database --- */
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
        tossee_log("Registration failed: database insert error - " . $wpdb->last_error, 'error');
        $back = wp_get_referer() ?: home_url('/register');
        wp_safe_redirect( add_query_arg('error', 'save_failed', $back) );
        exit;
    }

    /* --- Set session using helper function --- */
    tossee_set_user_session($tossee_id);

    tossee_log("User registered successfully: {$username} ({$tossee_id})", 'info');

    /* --- Redirect to CHAT with uid --- */
    $redirect = "https://chat.tossee.com/?uid=" . urlencode($tossee_id);

    wp_safe_redirect($redirect);
    exit;
}
