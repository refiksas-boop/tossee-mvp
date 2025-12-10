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

// Include all Tossee files
require_once __DIR__ . '/database-setup.php';
require_once __DIR__ . '/register-handler.php';
require_once __DIR__ . '/register-form.php';
require_once __DIR__ . '/login-handler.php';
require_once __DIR__ . '/login-form.php';
require_once __DIR__ . '/admin-users.php';
