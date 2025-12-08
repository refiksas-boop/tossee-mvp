<?php
/**
 * Tossee REST API Endpoints
 * Registers all custom REST API endpoints for profile management
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register REST API routes
 */
add_action('rest_api_init', 'tossee_register_rest_routes');

function tossee_register_rest_routes() {
    // GET /wp-json/tossee/v1/profile
    register_rest_route('tossee/v1', '/profile', array(
        'methods'  => 'GET',
        'callback' => 'tossee_api_get_profile',
        'permission_callback' => 'tossee_api_check_auth',
    ));

    // POST /wp-json/tossee/v1/save-profile
    register_rest_route('tossee/v1', '/save-profile', array(
        'methods'  => 'POST',
        'callback' => 'tossee_api_save_profile',
        'permission_callback' => 'tossee_api_check_auth',
    ));

    // GET /wp-json/tossee/v1/user/{tossee_id}
    register_rest_route('tossee/v1', '/user/(?P<tossee_id>[a-zA-Z0-9_]+)', array(
        'methods'  => 'GET',
        'callback' => 'tossee_api_get_user',
        'permission_callback' => 'tossee_api_check_auth',
    ));
}

/**
 * Permission callback - check if user is authenticated via session
 */
function tossee_api_check_auth() {
    tossee_start_session();
    return tossee_is_authenticated();
}

/**
 * GET /wp-json/tossee/v1/profile
 * Returns current user's profile data
 */
function tossee_api_get_profile(WP_REST_Request $request) {
    $user = tossee_get_current_user();

    if (!$user) {
        return new WP_Error(
            'not_authenticated',
            'User not authenticated',
            array('status' => 401)
        );
    }

    // Parse hobbies
    $hobbies = tossee_parse_hobbies($user->hobbies);

    // Build response
    $response = array(
        'tossee_id'   => $user->tossee_id,
        'username'    => $user->username,
        'email'       => $user->email,
        'dob'         => $user->dob,
        'first_name'  => $user->first_name ?: '',
        'last_name'   => $user->last_name ?: '',
        'gender'      => $user->gender ?: '',
        'country'     => $user->country ?: '',
        'city'        => $user->city ?: '',
        'hobbies'     => $hobbies,
        'about'       => $user->about ?: '',
        'photo'       => tossee_get_photo_url($user->photo),
        'created_at'  => $user->created_at,
        'updated_at'  => $user->updated_at ?: '',
    );

    return rest_ensure_response($response);
}

/**
 * POST /wp-json/tossee/v1/save-profile
 * Updates current user's profile data
 */
function tossee_api_save_profile(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $user = tossee_get_current_user();

    if (!$user) {
        return new WP_Error(
            'not_authenticated',
            'User not authenticated',
            array('status' => 401)
        );
    }

    // Get parameters from request
    $params = $request->get_json_params();

    // Validate required fields
    if (empty($params['first_name']) || empty($params['last_name'])) {
        return new WP_Error(
            'missing_fields',
            'First name and last name are required',
            array('status' => 400)
        );
    }

    // Prepare update data
    $update_data = array(
        'first_name' => sanitize_text_field($params['first_name']),
        'last_name'  => sanitize_text_field($params['last_name']),
        'gender'     => sanitize_text_field($params['gender']),
        'country'    => sanitize_text_field($params['country']),
        'city'       => sanitize_text_field($params['city']),
        'about'      => sanitize_textarea_field($params['about']),
        'updated_at' => current_time('mysql'),
    );

    // Handle hobbies (array to JSON)
    if (isset($params['hobbies']) && is_array($params['hobbies'])) {
        $update_data['hobbies'] = tossee_format_hobbies_for_storage($params['hobbies']);
    }

    // Handle photo update
    if (!empty($params['photo'])) {
        if (tossee_validate_photo($params['photo'])) {
            $update_data['photo'] = $params['photo'];
        }
    }

    // Update database
    $updated = $wpdb->update(
        $table,
        $update_data,
        array('tossee_id' => $user->tossee_id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
        array('%s')
    );

    if ($updated === false) {
        tossee_log("Profile update failed for user {$user->tossee_id}: " . $wpdb->last_error, 'error');
        return new WP_Error(
            'update_failed',
            'Failed to update profile',
            array('status' => 500)
        );
    }

    tossee_log("Profile updated successfully for user {$user->username}", 'info');

    return rest_ensure_response(array(
        'status'  => 'ok',
        'message' => 'Profile updated successfully'
    ));
}

/**
 * GET /wp-json/tossee/v1/user/{tossee_id}
 * Get user by tossee_id (for admin or matching)
 */
function tossee_api_get_user(WP_REST_Request $request) {
    $tossee_id = sanitize_text_field($request['tossee_id']);
    $user = tossee_get_user_by_id($tossee_id);

    if (!$user) {
        return new WP_Error(
            'user_not_found',
            'User not found',
            array('status' => 404)
        );
    }

    // Parse hobbies
    $hobbies = tossee_parse_hobbies($user->hobbies);

    // Build response (exclude sensitive data)
    $response = array(
        'tossee_id'   => $user->tossee_id,
        'username'    => $user->username,
        'first_name'  => $user->first_name ?: '',
        'last_name'   => $user->last_name ?: '',
        'gender'      => $user->gender ?: '',
        'country'     => $user->country ?: '',
        'city'        => $user->city ?: '',
        'hobbies'     => $hobbies,
        'about'       => $user->about ?: '',
        'photo'       => tossee_get_photo_url($user->photo),
        'age'         => tossee_calculate_age($user->dob),
    );

    return rest_ensure_response($response);
}
