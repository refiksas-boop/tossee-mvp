<?php
/* ================================
   TOSSEE – REST API ENDPOINTS
   Secure profile management API
================================ */

// Register REST API endpoints
add_action('rest_api_init', function() {

    // Save profile endpoint
    register_rest_route('tossee/v1', '/save-profile', array(
        'methods' => 'POST',
        'callback' => 'tossee_api_save_profile',
        'permission_callback' => 'tossee_api_check_auth'
    ));

    // Get profile endpoint - support both /profile and /get-profile
    register_rest_route('tossee/v1', '/profile', array(
        'methods' => 'GET',
        'callback' => 'tossee_api_get_profile',
        'permission_callback' => 'tossee_api_check_auth'
    ));

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

    if ( empty( $uid ) ) {
        tossee_log( 'API auth failed: No user ID found', 'error' );
        return false;
    }

    tossee_log( "API auth success: User {$uid}", 'info' );
    return true;
}

/**
 * Save profile data - SECURE VERSION
 */
function tossee_api_save_profile( $request ) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // Get authenticated user ID
    $uid = tossee_get_current_user_id();
    if ( ! $uid ) {
        tossee_log( 'Save profile failed: Not authenticated', 'error' );
        return new WP_Error( 'not_authenticated', 'Not authenticated', array( 'status' => 401 ) );
    }

    // Verify user exists
    $user = tossee_get_user_by_id( $uid );
    if ( ! $user ) {
        tossee_log( "Save profile failed: User not found - {$uid}", 'error' );
        return new WP_Error( 'user_not_found', 'User not found', array( 'status' => 404 ) );
    }

    // Get JSON params
    $params = $request->get_json_params();
    if ( empty( $params ) ) {
        tossee_log( 'Save profile failed: Empty request body', 'error' );
        return array(
            'status' => 'error',
            'message' => 'Empty request body'
        );
    }

    tossee_log( "Saving profile for user: {$uid}", 'info' );

    // Prepare update data with validation
    $update_data = array();
    $update_format = array();

    // First name
    if ( isset( $params['first_name'] ) ) {
        $first_name = sanitize_text_field( $params['first_name'] );
        if ( strlen( $first_name ) > 0 && strlen( $first_name ) <= 60 ) {
            $update_data['first_name'] = $first_name;
            $update_format[] = '%s';
        }
    }

    // Last name
    if ( isset( $params['last_name'] ) ) {
        $last_name = sanitize_text_field( $params['last_name'] );
        if ( strlen( $last_name ) > 0 && strlen( $last_name ) <= 60 ) {
            $update_data['last_name'] = $last_name;
            $update_format[] = '%s';
        }
    }

    // Gender
    if ( isset( $params['gender'] ) ) {
        $gender = sanitize_text_field( $params['gender'] );
        $allowed_genders = array( 'male', 'female', 'other', '' );
        if ( in_array( $gender, $allowed_genders ) ) {
            $update_data['gender'] = $gender;
            $update_format[] = '%s';
        }
    }

    // Country
    if ( isset( $params['country'] ) ) {
        $country = sanitize_text_field( $params['country'] );
        if ( strlen( $country ) <= 80 ) {
            $update_data['country'] = $country;
            $update_format[] = '%s';
        }
    }

    // City
    if ( isset( $params['city'] ) ) {
        $city = sanitize_text_field( $params['city'] );
        if ( strlen( $city ) <= 80 ) {
            $update_data['city'] = $city;
            $update_format[] = '%s';
        }
    }

    // About
    if ( isset( $params['about'] ) ) {
        $about = sanitize_textarea_field( $params['about'] );
        if ( strlen( $about ) <= 1000 ) {  // Limit to 1000 chars
            $update_data['about'] = $about;
            $update_format[] = '%s';
        }
    }

    // Hobbies
    if ( isset( $params['hobbies'] ) && is_array( $params['hobbies'] ) ) {
        $hobbies = array_map( 'sanitize_text_field', $params['hobbies'] );
        $hobbies = array_filter( $hobbies );  // Remove empty values
        $update_data['hobbies'] = implode( ',', $hobbies );
        $update_format[] = '%s';
    }

    // Photo - store as base64 for consistency with registration
    if ( isset( $params['photo'] ) && ! empty( $params['photo'] ) ) {
        $photo = $params['photo'];

        // Validate it's a proper data URL
        if ( strpos( $photo, 'data:image/' ) === 0 ) {
            // Optional: limit photo size (e.g., 5MB base64 = ~3.75MB file)
            if ( strlen( $photo ) <= 5000000 ) {
                $update_data['photo'] = $photo;
                $update_format[] = '%s';
                tossee_log( "Photo updated for user: {$uid}", 'info' );
            } else {
                tossee_log( "Photo too large for user: {$uid}", 'warning' );
            }
        } else {
            tossee_log( "Invalid photo format for user: {$uid}", 'warning' );
        }
    }

    // DOB - DO NOT ALLOW CHANGES (security - prevents age manipulation)
    // User registered with DOB, cannot change it later

    // Check if there's anything to update
    if ( empty( $update_data ) ) {
        tossee_log( "Save profile: No valid data to update for user {$uid}", 'warning' );
        return array(
            'status' => 'error',
            'message' => 'No valid data to update'
        );
    }

    // Update timestamp
    $update_data['updated_at'] = current_time( 'mysql' );
    $update_format[] = '%s';

    // Perform update
    $result = $wpdb->update(
        $table,
        $update_data,
        array( 'tossee_id' => $uid ),
        $update_format,
        array( '%s' )
    );

    // Check result
    if ( $result === false ) {
        $error = $wpdb->last_error;
        tossee_log( "Profile update DB error for user {$uid}: {$error}", 'error' );
        return array(
            'status' => 'error',
            'message' => 'Database error: Failed to update profile'
        );
    }

    tossee_log( "Profile updated successfully for user: {$uid} ({$result} rows affected)", 'info' );

    return array(
        'status' => 'ok',
        'message' => 'Profile updated successfully',
        'updated_fields' => array_keys( $update_data )
    );
}

/**
 * Get profile data - SECURE VERSION
 */
function tossee_api_get_profile() {
    $uid = tossee_get_current_user_id();

    if ( ! $uid ) {
        tossee_log( 'Get profile failed: Not authenticated', 'error' );
        return new WP_Error( 'not_authenticated', 'Not authenticated', array( 'status' => 401 ) );
    }

    $user = tossee_get_user_by_id( $uid );

    if ( ! $user ) {
        tossee_log( "Get profile failed: User not found - {$uid}", 'error' );
        return new WP_Error( 'user_not_found', 'User not found', array( 'status' => 404 ) );
    }

    tossee_log( "Profile retrieved for user: {$uid}", 'info' );

    // Return sanitized user data
    return array(
        'status' => 'ok',
        'user' => array(
            'tossee_id'  => $user->tossee_id,
            'username'   => $user->username,
            'email'      => $user->email,
            'first_name' => $user->first_name ?? '',
            'last_name'  => $user->last_name ?? '',
            'gender'     => $user->gender ?? '',
            'country'    => $user->country ?? '',
            'city'       => $user->city ?? '',
            'dob'        => $user->dob ?? '',
            'about'      => $user->about ?? '',
            'hobbies'    => $user->hobbies ?? '',
            'photo'      => $user->photo ?? '',
            'created_at' => $user->created_at ?? '',
            'updated_at' => $user->updated_at ?? ''
        )
    );
}
