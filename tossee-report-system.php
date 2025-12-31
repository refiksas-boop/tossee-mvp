<?php
/**
 * Plugin Name: Tossee Report System
 * Plugin URI: https://tossee.com
 * Description: Complete chat report system for Tossee - includes chat interface, admin panel, user blocking, messaging, and report management
 * Version: 1.0.0
 * Author: Tossee Development Team
 * Author URI: https://tossee.com
 * License: GPL v2 or later
 * Text Domain: tossee-report
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('TOSSEE_REPORT_VERSION', '1.0.0');
define('TOSSEE_REPORT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TOSSEE_REPORT_PLUGIN_URL', plugin_dir_url(__FILE__));

/* ================================
   DATABASE TABLES SETUP
================================ */

/**
 * Create database tables on plugin activation
 */
function tossee_report_activate() {
    tossee_create_users_table();
    tossee_create_reports_table();
    tossee_create_messages_table();
    tossee_create_settings_table();

    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'tossee_report_activate');

/**
 * Create or update users table
 */
function tossee_create_users_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_users';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        tossee_id VARCHAR(40) NOT NULL,
        username VARCHAR(60) NOT NULL,
        email VARCHAR(120) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        dob DATE NOT NULL,
        photo LONGTEXT NOT NULL,
        is_blocked TINYINT(1) NOT NULL DEFAULT 0,
        block_reason TEXT NULL,
        blocked_at DATETIME NULL,
        blocked_by BIGINT(20) UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        UNIQUE KEY tossee_id (tossee_id),
        KEY is_blocked (is_blocked)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Create reports table
 */
function tossee_create_reports_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_reports';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        reporter_id VARCHAR(40) NOT NULL,
        reported_user_id VARCHAR(40) NOT NULL,
        report_reason VARCHAR(100) NOT NULL,
        additional_details TEXT NULL,
        report_status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME NULL,
        reviewed_by BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY (id),
        KEY reporter_id (reporter_id),
        KEY reported_user_id (reported_user_id),
        KEY report_status (report_status)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Create admin messages table
 */
function tossee_create_messages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_admin_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id VARCHAR(40) NOT NULL,
        admin_id BIGINT(20) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY admin_id (admin_id),
        KEY is_read (is_read)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Create settings table
 */
function tossee_create_settings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_settings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY setting_key (setting_key)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Insert default settings
    $wpdb->replace(
        $table_name,
        array(
            'setting_key' => 'report_button_enabled',
            'setting_value' => '1'
        ),
        array('%s', '%s')
    );
}

/**
 * Create notifications table
 */
function tossee_create_notifications_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_notifications';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        notification_type VARCHAR(50) NOT NULL,
        reference_id BIGINT(20) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY notification_type (notification_type),
        KEY is_read (is_read)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('init', 'tossee_create_notifications_table');

/* ================================
   INCLUDE API AND PAGES
================================ */

// Include API endpoints
require_once TOSSEE_REPORT_PLUGIN_DIR . 'includes/api.php';

// Include admin menu
require_once TOSSEE_REPORT_PLUGIN_DIR . 'includes/admin-menu.php';

/* ================================
   CUSTOM PAGE TEMPLATES
================================ */

/**
 * Register custom template for chat page
 */
function tossee_register_templates() {
    // Register chat page template
    if (isset($_GET['tossee_chat'])) {
        include TOSSEE_REPORT_PLUGIN_DIR . 'templates/chat.php';
        exit;
    }
}
add_action('template_redirect', 'tossee_register_templates');

/**
 * Add rewrite rules for pretty URLs
 */
function tossee_rewrite_rules() {
    add_rewrite_rule('^tossee-chat/?', 'index.php?tossee_chat=1', 'top');
}
add_action('init', 'tossee_rewrite_rules');

/**
 * Add query vars
 */
function tossee_query_vars($vars) {
    $vars[] = 'tossee_chat';
    return $vars;
}
add_filter('query_vars', 'tossee_query_vars');

/**
 * Plugin deactivation
 */
function tossee_report_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'tossee_report_deactivate');
