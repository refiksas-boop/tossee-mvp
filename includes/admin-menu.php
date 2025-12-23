<?php
/**
 * Admin Menu for Tossee Report System
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu items
 */
function tossee_add_admin_menu() {
    add_menu_page(
        'Tossee Reports',           // Page title
        'Tossee Reports',           // Menu title
        'manage_options',           // Capability
        'tossee-reports',           // Menu slug
        'tossee_reports_page',      // Callback function
        'dashicons-warning',        // Icon
        25                          // Position
    );

    add_submenu_page(
        'tossee-reports',
        'All Reports',
        'All Reports',
        'manage_options',
        'tossee-reports',
        'tossee_reports_page'
    );

    add_submenu_page(
        'tossee-reports',
        'Settings',
        'Settings',
        'manage_options',
        'tossee-settings',
        'tossee_settings_page'
    );
}
add_action('admin_menu', 'tossee_add_admin_menu');

/**
 * Reports page callback
 */
function tossee_reports_page() {
    include TOSSEE_REPORT_PLUGIN_DIR . 'templates/admin-reports.php';
}

/**
 * Settings page callback
 */
function tossee_settings_page() {
    ?>
    <div class="wrap">
        <h1>Tossee Report System Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('tossee_settings');
            do_settings_sections('tossee_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register settings
 */
function tossee_register_settings() {
    register_setting('tossee_settings', 'tossee_report_button_enabled');

    add_settings_section(
        'tossee_main_settings',
        'Main Settings',
        'tossee_main_settings_callback',
        'tossee_settings'
    );

    add_settings_field(
        'report_button_enabled',
        'Enable Report Button',
        'tossee_report_button_callback',
        'tossee_settings',
        'tossee_main_settings'
    );
}
add_action('admin_init', 'tossee_register_settings');

function tossee_main_settings_callback() {
    echo '<p>Configure your Tossee report system settings below.</p>';
}

function tossee_report_button_callback() {
    $enabled = get_option('tossee_report_button_enabled', 1);
    ?>
    <label>
        <input type="checkbox" name="tossee_report_button_enabled" value="1" <?php checked($enabled, 1); ?>>
        Enable report button for all users
    </label>
    <?php
}

/**
 * Add admin notice for unread reports
 */
function tossee_admin_notices() {
    global $wpdb;
    $reports_table = $wpdb->prefix . 'tossee_reports';

    $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM $reports_table WHERE report_status = 'pending'");

    if ($pending_count > 0) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>Tossee Reports:</strong> You have <?php echo $pending_count; ?> pending report(s) to review.
            <a href="<?php echo admin_url('admin.php?page=tossee-reports'); ?>">View Reports</a></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'tossee_admin_notices');
