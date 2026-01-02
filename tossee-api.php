/* ================================
   TOSSEE – REST API ENDPOINTS
================================ */

add_action('rest_api_init', function() {
    // Get current user profile
    register_rest_route('tossee/v1', '/profile', [
        'methods'  => 'GET',
        'callback' => 'tossee_api_get_profile',
        'permission_callback' => '__return_true'
    ]);

    // Change password
    register_rest_route('tossee/v1', '/change-password', [
        'methods'  => 'POST',
        'callback' => 'tossee_api_change_password',
        'permission_callback' => '__return_true'
    ]);

    // Get registration photo
    register_rest_route('tossee/v1', '/photo', [
        'methods'  => 'GET',
        'callback' => 'tossee_api_get_photo',
        'permission_callback' => '__return_true'
    ]);
});

/* --- Get Profile Endpoint --- */
function tossee_api_get_profile() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['tossee_id'])) {
        return new WP_Error('not_logged_in', 'User not logged in', ['status' => 401]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';
    $tossee_id = $_SESSION['tossee_id'];

    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT id, tossee_id, username, email, dob, created_at FROM $table WHERE tossee_id = %s", $tossee_id)
    );

    if (!$user) {
        return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
    }

    return [
        'id' => $user->id,
        'tossee_id' => $user->tossee_id,
        'username' => $user->username,
        'email' => $user->email,
        'dob' => $user->dob,
        'created_at' => $user->created_at
    ];
}

/* --- Change Password Endpoint --- */
function tossee_api_change_password($request) {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['tossee_id'])) {
        return new WP_Error('not_logged_in', 'User not logged in', ['status' => 401]);
    }

    $params = $request->get_json_params();
    $current_password = $params['current_password'] ?? '';
    $new_password = $params['new_password'] ?? '';

    if (empty($current_password) || empty($new_password)) {
        return new WP_Error('missing_fields', 'Current and new password required', ['status' => 400]);
    }

    if (strlen($new_password) < 6) {
        return new WP_Error('password_too_short', 'Password must be at least 6 characters', ['status' => 400]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';
    $tossee_id = $_SESSION['tossee_id'];

    // Get current password hash
    $user = $wpdb->get_row(
        $wpdb->prepare("SELECT id, password_hash FROM $table WHERE tossee_id = %s", $tossee_id)
    );

    if (!$user) {
        return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
    }

    // Verify current password
    if (!password_verify($current_password, $user->password_hash)) {
        return new WP_Error('wrong_password', 'Current password is incorrect', ['status' => 403]);
    }

    // Update password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $updated = $wpdb->update(
        $table,
        ['password_hash' => $new_hash],
        ['id' => $user->id],
        ['%s'],
        ['%d']
    );

    if ($updated === false) {
        return new WP_Error('update_failed', 'Failed to update password', ['status' => 500]);
    }

    return ['success' => true, 'message' => 'Password changed successfully'];
}

/* --- Get Photo Endpoint --- */
function tossee_api_get_photo() {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['tossee_id'])) {
        return new WP_Error('not_logged_in', 'User not logged in', ['status' => 401]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';
    $tossee_id = $_SESSION['tossee_id'];

    $photo = $wpdb->get_var(
        $wpdb->prepare("SELECT photo FROM $table WHERE tossee_id = %s", $tossee_id)
    );

    if (!$photo) {
        return new WP_Error('photo_not_found', 'Photo not found', ['status' => 404]);
    }

    return ['photo' => $photo];
}
