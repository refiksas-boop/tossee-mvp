<?php
/* ================================
   TOSSEE – EDIT PROFILE HANDLER
================================ */

add_action( 'admin_post_nopriv_tossee_edit_profile', 'tossee_edit_profile_handler' );
add_action( 'admin_post_tossee_edit_profile',        'tossee_edit_profile_handler' );

function tossee_edit_profile_handler() {
    if ( ! session_id() ) session_start();

    $tossee_id = $_SESSION['tossee_id'] ?? '';
    if ( ! $tossee_id ) {
        wp_safe_redirect( home_url( '/login' ) );
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    /* --- Sanitizacija --- */
    $first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
    $last_name  = sanitize_text_field( wp_unslash( $_POST['last_name']  ?? '' ) );
    $gender     = sanitize_text_field( wp_unslash( $_POST['gender']     ?? '' ) );
    $country    = sanitize_text_field( wp_unslash( $_POST['country']    ?? '' ) );
    $city       = sanitize_text_field( wp_unslash( $_POST['city']       ?? '' ) );
    $hobbies    = sanitize_textarea_field( wp_unslash( $_POST['hobbies'] ?? '' ) );
    $about      = sanitize_textarea_field( wp_unslash( $_POST['about']   ?? '' ) );

    /* --- Leistinos reikšmės --- */
    $allowed_genders = [ '', 'male', 'female', 'other' ];
    if ( ! in_array( $gender, $allowed_genders, true ) ) {
        $gender = '';
    }

    /* --- Ilgio patikrinimas --- */
    if ( mb_strlen( $first_name ) > 60 || mb_strlen( $last_name ) > 60 ) {
        wp_safe_redirect( add_query_arg( 'edit_error', 'name_too_long', wp_get_referer() ?: home_url( '/edit-profile' ) ) );
        exit;
    }
    if ( mb_strlen( $country ) > 120 || mb_strlen( $city ) > 120 ) {
        wp_safe_redirect( add_query_arg( 'edit_error', 'location_too_long', wp_get_referer() ?: home_url( '/edit-profile' ) ) );
        exit;
    }

    $data = [
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'gender'     => $gender,
        'country'    => $country,
        'city'       => $city,
        'hobbies'    => $hobbies,
        'about'      => $about,
        'updated_at' => current_time( 'mysql' ),
    ];
    $formats = [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ];

    /* --- Nuotrauka jei buvo atnaujinta --- */
    if ( ! empty( $_POST['photo'] ) ) {
        $data['photo'] = wp_unslash( $_POST['photo'] );
        $formats[]     = '%s';
    }

    $updated = $wpdb->update(
        $table,
        $data,
        [ 'tossee_id' => $tossee_id ],
        $formats,
        [ '%s' ]
    );

    if ( $updated === false ) {
        wp_safe_redirect( add_query_arg( 'edit_error', 'save_failed', wp_get_referer() ?: home_url( '/edit-profile' ) ) );
        exit;
    }

    wp_safe_redirect( add_query_arg( 'edit_success', '1', home_url( '/profile' ) ) );
    exit;
}
