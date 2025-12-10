<?php
/**
 * TOSSEE REST API ENDPOINTS
 * Grąžina vartotojo profilio duomenis
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registruojame REST API endpoint
 */
add_action( 'rest_api_init', 'tossee_register_api_routes' );

function tossee_register_api_routes() {
    register_rest_route( 'tossee/v1', '/profile', array(
        'methods'             => 'GET',
        'callback'            => 'tossee_api_get_profile',
        'permission_callback' => '__return_true', // Leidžiame visiems (tikriname sesijoje)
    ) );
}

/**
 * API endpoint: Gauna dabartinio vartotojo profilio duomenis
 */
function tossee_api_get_profile( $request ) {
    // Patikriname ar vartotojas prisijungęs
    $uid = tossee_get_current_user_id();

    if ( ! $uid ) {
        return new WP_Error(
            'not_logged_in',
            'User not logged in',
            array( 'status' => 401 )
        );
    }

    // Gauname vartotojo duomenis
    $user = tossee_get_user_by_id( $uid );

    if ( ! $user ) {
        return new WP_Error(
            'user_not_found',
            'User not found',
            array( 'status' => 404 )
        );
    }

    // Grąžiname profilio duomenis (BE password_hash!)
    return rest_ensure_response( array(
        'tossee_id'  => $user->tossee_id,
        'username'   => $user->username,
        'email'      => $user->email,
        'first_name' => isset($user->first_name) ? $user->first_name : '',
        'last_name'  => isset($user->last_name) ? $user->last_name : '',
        'gender'     => isset($user->gender) ? $user->gender : '',
        'country'    => isset($user->country) ? $user->country : '',
        'city'       => isset($user->city) ? $user->city : '',
        'dob'        => $user->dob,
        'photo'      => $user->photo,
        'created_at' => $user->created_at,
        'hobbies'    => '', // Pridėsite vėliau į DB
        'about'      => '', // Pridėsite vėliau į DB
    ) );
}

/**
 * HELPER FUNKCIJOS (jei jų dar nėra)
 * Šios funkcijos turi būti jūsų plugine
 */

if ( ! function_exists( 'tossee_get_current_user_id' ) ) {
    function tossee_get_current_user_id() {
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }

        // Pirmiausia tikriname session
        if ( ! empty( $_SESSION['tossee_uid'] ) ) {
            return $_SESSION['tossee_uid'];
        }

        // Jei session nėra, tikriname cookie
        if ( ! empty( $_COOKIE['tossee_uid'] ) ) {
            $_SESSION['tossee_uid'] = $_COOKIE['tossee_uid'];
            return $_COOKIE['tossee_uid'];
        }

        // Taip pat tikriname seną 'tossee_id' session key
        if ( ! empty( $_SESSION['tossee_id'] ) ) {
            $_SESSION['tossee_uid'] = $_SESSION['tossee_id'];
            return $_SESSION['tossee_id'];
        }

        return null;
    }
}

if ( ! function_exists( 'tossee_get_user_by_id' ) ) {
    function tossee_get_user_by_id( $tossee_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE tossee_id = %s", $tossee_id )
        );
    }
}
