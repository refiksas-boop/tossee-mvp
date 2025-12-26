<?php
/**
 * Tossee Report API
 * Uses WordPress database connection (like queue-manager.php)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load WordPress (same as queue-manager.php)
$wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'WordPress not found',
        'details' => 'wp-load.php not found at: ' . $wp_load_path
    ]);
    exit;
}

// Use WordPress database connection
global $wpdb;

if (!$wpdb) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'details' => 'WordPress $wpdb not available'
    ]);
    exit;
}

// Table names with WordPress prefix
$reports_table = $wpdb->prefix . 'tossee_reports';
$settings_table = $wpdb->prefix . 'tossee_settings';

// Create tables if they don't exist
$charset = $wpdb->get_charset_collate();

$wpdb->query("CREATE TABLE IF NOT EXISTS {$reports_table} (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id VARCHAR(40) NOT NULL,
    reported_user_id VARCHAR(40) NOT NULL,
    report_reason VARCHAR(100) NOT NULL,
    additional_details TEXT NULL,
    report_status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX(reporter_id),
    INDEX(reported_user_id),
    INDEX(report_status)
) {$charset}");

$wpdb->query("CREATE TABLE IF NOT EXISTS {$settings_table} (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) {$charset}");

// Insert default setting
$wpdb->query("INSERT IGNORE INTO {$settings_table} (setting_key, setting_value) VALUES ('report_button_enabled', '1')");

// ================================================================
// SUBMIT REPORT
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (empty($data['reporter_id']) || empty($data['reported_user_id']) || empty($data['report_reason'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $inserted = $wpdb->insert(
        $reports_table,
        array(
            'reporter_id' => sanitize_text_field($data['reporter_id']),
            'reported_user_id' => sanitize_text_field($data['reported_user_id']),
            'report_reason' => sanitize_text_field($data['report_reason']),
            'additional_details' => isset($data['additional_details']) ? sanitize_textarea_field($data['additional_details']) : null
        ),
        array('%s', '%s', '%s', '%s')
    );

    if ($inserted) {
        echo json_encode(['success' => true, 'message' => 'Report submitted', 'report_id' => $wpdb->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save report', 'details' => $wpdb->last_error]);
    }
    exit;
}

// ================================================================
// GET SETTINGS
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $enabled = $wpdb->get_var(
        $wpdb->prepare("SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", 'report_button_enabled')
    );

    echo json_encode(['success' => true, 'enabled' => ($enabled === '1')]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
