<?php
/**
 * Tossee Initialization
 * Main entry point that loads all Tossee components
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Tossee constants
define('TOSSEE_VERSION', '1.0.0');
define('TOSSEE_PATH', dirname(__FILE__));
define('TOSSEE_URL', get_template_directory_uri() . '/tossee-mvp');

// Load core includes
require_once TOSSEE_PATH . '/includes/session.php';
require_once TOSSEE_PATH . '/includes/helpers.php';

// Load database setup
require_once TOSSEE_PATH . '/database-setup.php';

// Load authentication handlers
require_once TOSSEE_PATH . '/register-form.php';
require_once TOSSEE_PATH . '/register-handler.php';

// Load auth directory files if they exist
if (file_exists(TOSSEE_PATH . '/auth/login-form.php')) {
    require_once TOSSEE_PATH . '/auth/login-form.php';
}
if (file_exists(TOSSEE_PATH . '/auth/login-handler.php')) {
    require_once TOSSEE_PATH . '/auth/login-handler.php';
}
if (file_exists(TOSSEE_PATH . '/auth/logout-handler.php')) {
    require_once TOSSEE_PATH . '/auth/logout-handler.php';
}

// Load API endpoints if they exist
if (file_exists(TOSSEE_PATH . '/api/endpoints.php')) {
    require_once TOSSEE_PATH . '/api/endpoints.php';
}

// Load chat pairing system
if (file_exists(TOSSEE_PATH . '/chat/pairing.php')) {
    require_once TOSSEE_PATH . '/chat/pairing.php';
}

// Load admin files
if (is_admin()) {
    if (file_exists(TOSSEE_PATH . '/admin/users-list.php')) {
        require_once TOSSEE_PATH . '/admin/users-list.php';
    }
    if (file_exists(TOSSEE_PATH . '/admin/user-detail.php')) {
        require_once TOSSEE_PATH . '/admin/user-detail.php';
    }
}

// Log initialization
tossee_log('Tossee MVP initialized successfully', 'info');
