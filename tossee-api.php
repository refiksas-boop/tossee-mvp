<?php
/* ================================
   TOSSEE – REST API ENDPOINTS
================================ */

// Register REST API endpoints
add_action('rest_api_init', function() {

    // Save profile endpoint
    register_rest_route('tossee/v1', '/save-profile', array(
        'methods' => 'POST',
        'callback' => 'tossee_api_save_profile',
        'permission_callback' => 'tossee_api_check_auth'
    ));

    // Get profile endpoint
    register_rest_route('tossee/v1', '/get-profile', array(
        'methods' => 'GET',
        'callback' => 'tossee_api_get_profile',
        'permission_callback' => 'tossee_api_check_auth'
    ));

});

/**
 * Check if user is authenticated
 */
function tossee_api_check_auth() {
    $uid = tossee_get_current_user_id();
    return !empty($uid);
}

/**
 * Save profile data
 */
function tossee_api_save_profile($request) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $uid = tossee_get_current_user_id();
    if (!$uid) {
        return new WP_Error('not_authenticated', 'Not authenticated', array('status' => 401));
    }

    $params = $request->get_json_params();

    // Prepare update data
    $update_data = array();
    $update_format = array();

    if (isset($params['first_name'])) {
        $update_data['first_name'] = sanitize_text_field($params['first_name']);
        $update_format[] = '%s';
    }

    if (isset($params['last_name'])) {
        $update_data['last_name'] = sanitize_text_field($params['last_name']);
        $update_format[] = '%s';
    }

    if (isset($params['gender'])) {
        $update_data['gender'] = sanitize_text_field($params['gender']);
        $update_format[] = '%s';
    }

    if (isset($params['country'])) {
        $update_data['country'] = sanitize_text_field($params['country']);
        $update_format[] = '%s';
    }

    if (isset($params['city'])) {
        $update_data['city'] = sanitize_text_field($params['city']);
        $update_format[] = '%s';
    }

    if (isset($params['about'])) {
        $update_data['about'] = sanitize_textarea_field($params['about']);
        $update_format[] = '%s';
    }

    if (isset($params['hobbies']) && is_array($params['hobbies'])) {
        $update_data['hobbies'] = implode(',', array_map('sanitize_text_field', $params['hobbies']));
        $update_format[] = '%s';
    }

    if (isset($params['photo']) && !empty($params['photo'])) {
        // Photo is base64 data URL
        $update_data['photo'] = $params['photo'];
        $update_format[] = '%s';
    }

    // Update timestamp
    $update_data['updated_at'] = current_time('mysql');
    $update_format[] = '%s';

    // Perform update
    $result = $wpdb->update(
        $table,
        $update_data,
        array('tossee_id' => $uid),
        $update_format,
        array('%s')
    );

    if ($result === false) {
        tossee_log('Profile update failed: ' . $wpdb->last_error, 'error');
        return array(
            'status' => 'error',
            'message' => 'Failed to update profile'
        );
    }

    tossee_log("Profile updated successfully for user: {$uid}", 'info');

    return array(
        'status' => 'ok',
        'message' => 'Profile updated successfully'
    );
}

/**
 * Get profile data
 */
function tossee_api_get_profile() {
    $uid = tossee_get_current_user_id();
    if (!$uid) {
        return new WP_Error('not_authenticated', 'Not authenticated', array('status' => 401));
    }

    $user = tossee_get_user_by_id($uid);
    if (!$user) {
        return new WP_Error('user_not_found', 'User not found', array('status' => 404));
    }

    return array(
        'status' => 'ok',
        'user' => array(
            'tossee_id' => $user->tossee_id,
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'gender' => $user->gender ?? '',
            'country' => $user->country ?? '',
            'city' => $user->city ?? '',
            'dob' => $user->dob ?? '',
            'about' => $user->about ?? '',
            'hobbies' => $user->hobbies ?? '',
            'photo' => $user->photo ?? '',
            'created_at' => $user->created_at ?? ''
        )
    );
}
