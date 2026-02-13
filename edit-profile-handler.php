/* ================================
   TOSSEE – EDIT PROFILE HANDLER
================================ */

add_action('admin_post_nopriv_tossee_edit_profile', 'tossee_edit_profile_handler');
add_action('admin_post_tossee_edit_profile',        'tossee_edit_profile_handler');

function tossee_edit_profile_handler() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    /* --- Check session --- */
    if (!session_id()) {
        session_start();
    }

    if (empty($_SESSION['tossee_id'])) {
        $back = home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'not_logged_in', $back) );
        exit;
    }

    $tossee_id = $_SESSION['tossee_id'];

    /* --- Get user --- */
    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE tossee_id = %s", $tossee_id)
    );

    if (!$user) {
        $back = home_url('/login');
        wp_safe_redirect( add_query_arg('error', 'user_not_found', $back) );
        exit;
    }

    /* --- Collect data --- */
    $update_data = [];
    $update_format = [];

    // First name
    if (isset($_POST['first_name'])) {
        $update_data['first_name'] = sanitize_text_field($_POST['first_name']);
        $update_format[] = '%s';
    }

    // Last name
    if (isset($_POST['last_name'])) {
        $update_data['last_name'] = sanitize_text_field($_POST['last_name']);
        $update_format[] = '%s';
    }

    // Gender
    if (isset($_POST['gender'])) {
        $update_data['gender'] = sanitize_text_field($_POST['gender']);
        $update_format[] = '%s';
    }

    // Country
    if (isset($_POST['country'])) {
        $update_data['country'] = sanitize_text_field($_POST['country']);
        $update_format[] = '%s';
    }

    // City
    if (isset($_POST['city'])) {
        $update_data['city'] = sanitize_text_field($_POST['city']);
        $update_format[] = '%s';
    }

    // Hobbies
    if (isset($_POST['hobbies'])) {
        $update_data['hobbies'] = sanitize_textarea_field($_POST['hobbies']);
        $update_format[] = '%s';
    }

    // About
    if (isset($_POST['about'])) {
        $update_data['about'] = sanitize_textarea_field($_POST['about']);
        $update_format[] = '%s';
    }

    // Handle new photo upload
    if (!empty($_POST['photo']) && $_POST['photo'] !== $user->photo) {
        $photo = wp_unslash($_POST['photo']);
        // Validate base64 image
        if (preg_match('/^data:image\/(\w+);base64,/', $photo)) {
            $update_data['photo'] = $photo;
            $update_format[] = '%s';
        }
    }

    // Password change
    if (!empty($_POST['new_password'])) {
        // Verify old password first
        if (empty($_POST['current_password']) ||
            !password_verify($_POST['current_password'], $user->password_hash)) {
            $back = wp_get_referer() ?: home_url('/edit-profile');
            wp_safe_redirect( add_query_arg('error', 'invalid_current_password', $back) );
            exit;
        }

        // Validate new password
        if (strlen($_POST['new_password']) < 6) {
            $back = wp_get_referer() ?: home_url('/edit-profile');
            wp_safe_redirect( add_query_arg('error', 'password_too_short', $back) );
            exit;
        }

        $update_data['password_hash'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $update_format[] = '%s';
    }

    // Add updated timestamp
    $update_data['updated_at'] = current_time('mysql');
    $update_format[] = '%s';

    /* --- Update database --- */
    if (!empty($update_data)) {
        $updated = $wpdb->update(
            $table,
            $update_data,
            ['tossee_id' => $tossee_id],
            $update_format,
            ['%s']
        );

        if ($updated === false) {
            error_log('Profile update failed for user: ' . $tossee_id);
            $back = wp_get_referer() ?: home_url('/edit-profile');
            wp_safe_redirect( add_query_arg('error', 'update_failed', $back) );
            exit;
        }

        error_log('Profile updated successfully for user: ' . $tossee_id);
    }

    /* --- Redirect back with success --- */
    $redirect = wp_get_referer() ?: home_url('/profile.html');
    wp_safe_redirect( add_query_arg('success', 'profile_updated', $redirect) );
    exit;
}
