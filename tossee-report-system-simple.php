<?php
/**
 * Plugin Name: Tossee Report System
 * Description: Simple chat report system
 * Version: 1.0.4
 * Author: Tossee Team
 */

if (!defined('ABSPATH')) exit;

// Activation - create tables
register_activation_hook(__FILE__, function() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset = $wpdb->get_charset_collate();

    // Reports table
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tossee_reports (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        reporter_id VARCHAR(40) NOT NULL,
        reported_user_id VARCHAR(40) NOT NULL,
        report_reason VARCHAR(100) NOT NULL,
        additional_details TEXT NULL,
        report_status VARCHAR(20) DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset");

    // Settings table
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tossee_settings (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL
    ) $charset");

    // Insert default setting - report button enabled
    $wpdb->query("INSERT IGNORE INTO {$wpdb->prefix}tossee_settings (setting_key, setting_value) VALUES ('report_button_enabled', '1')");
});

// Admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Tossee Reports',
        'Tossee Reports',
        'manage_options',
        'tossee-reports',
        function() {
            global $wpdb;

            // Handle settings update
            if (isset($_POST['tossee_update_settings']) && check_admin_referer('tossee_settings_action', 'tossee_settings_nonce')) {
                $enabled = isset($_POST['report_button_enabled']) ? '1' : '0';
                $wpdb->replace(
                    $wpdb->prefix . 'tossee_settings',
                    array('setting_key' => 'report_button_enabled', 'setting_value' => $enabled),
                    array('%s', '%s')
                );
                echo '<div class="notice notice-success"><p>Settings updated successfully!</p></div>';
            }

            // Get current setting
            $button_enabled = $wpdb->get_var("SELECT setting_value FROM {$wpdb->prefix}tossee_settings WHERE setting_key = 'report_button_enabled'");

            echo '<div class="wrap">';
            echo '<h1>Tossee Reports</h1>';

            // Settings section
            echo '<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 5px;">';
            echo '<h2>Settings</h2>';
            echo '<form method="post">';
            wp_nonce_field('tossee_settings_action', 'tossee_settings_nonce');
            echo '<label style="display: block; margin: 10px 0;">';
            echo '<input type="checkbox" name="report_button_enabled" value="1" ' . checked($button_enabled, '1', false) . '> ';
            echo '<strong>Enable Report Button</strong> (uncheck to hide report button from chat)';
            echo '</label>';
            echo '<button type="submit" name="tossee_update_settings" class="button button-primary">Save Settings</button>';
            echo '</form>';
            echo '</div>';

            // Reports table
            echo '<h2>Recent Reports</h2>';
            $reports = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tossee_reports ORDER BY created_at DESC LIMIT 20");

            if ($reports) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>ID</th><th>Reporter</th><th>Reported User</th><th>Reason</th><th>Status</th><th>Additional Details</th><th>Date</th></tr></thead><tbody>';
                foreach ($reports as $r) {
                    echo '<tr>';
                    echo '<td>' . $r->id . '</td>';
                    echo '<td>' . esc_html($r->reporter_id) . '</td>';
                    echo '<td>' . esc_html($r->reported_user_id) . '</td>';
                    echo '<td>' . esc_html($r->report_reason) . '</td>';
                    echo '<td>' . esc_html($r->report_status) . '</td>';
                    echo '<td>' . esc_html($r->additional_details) . '</td>';
                    echo '<td>' . esc_html($r->created_at) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No reports yet.</p>';
            }

            echo '</div>';
        },
        'dashicons-warning'
    );
});

// REST API - Submit report
add_action('rest_api_init', function() {
    // Submit report endpoint
    register_rest_route('tossee/v1', '/report', array(
        'methods' => 'POST',
        'callback' => function($request) {
            global $wpdb;

            $data = json_decode($request->get_body(), true);

            if (empty($data['reporter_id']) || empty($data['reported_user_id']) || empty($data['report_reason'])) {
                return new WP_Error('missing_fields', 'Missing required fields', array('status' => 400));
            }

            $wpdb->insert(
                $wpdb->prefix . 'tossee_reports',
                array(
                    'reporter_id' => sanitize_text_field($data['reporter_id']),
                    'reported_user_id' => sanitize_text_field($data['reported_user_id']),
                    'report_reason' => sanitize_text_field($data['report_reason']),
                    'additional_details' => isset($data['additional_details']) ? sanitize_textarea_field($data['additional_details']) : null
                )
            );

            return array('success' => true, 'message' => 'Report submitted');
        },
        'permission_callback' => '__return_true'
    ));

    // Check report button setting endpoint
    register_rest_route('tossee/v1', '/settings/report-button', array(
        'methods' => 'GET',
        'callback' => function($request) {
            global $wpdb;

            $setting = $wpdb->get_var("SELECT setting_value FROM {$wpdb->prefix}tossee_settings WHERE setting_key = 'report_button_enabled'");

            return array(
                'success' => true,
                'enabled' => ($setting === '1')
            );
        },
        'permission_callback' => '__return_true'
    ));
});
