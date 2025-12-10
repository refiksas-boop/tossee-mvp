<?php
/**
 * Plugin Name: Tossee Core
 * Description: Custom Tossee user system (custom DB, registration, login, admin panel).
 * Author: Andrius / Tossee
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Įtraukiame visus failus
require_once __DIR__ . '/database-setup.php';      // DB lentelės kūrimas
require_once __DIR__ . '/register-handler.php';    // Registracijos handler
require_once __DIR__ . '/tossee-api.php';          // REST API endpoints
require_once __DIR__ . '/profile-shortcode.php';   // Profile puslapis
require_once __DIR__ . '/admin-users.php';         // Admin panel (jei yra)

// Session pradžia
add_action( 'init', 'tossee_start_session', 1 );
function tossee_start_session() {
    if ( ! session_id() ) {
        session_start();
    }
}

// Allowed redirect hosts
add_filter( 'allowed_redirect_hosts', function( $hosts ) {
    $hosts[] = 'chat.tossee.com';
    return $hosts;
} );
