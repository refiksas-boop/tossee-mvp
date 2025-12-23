<?php
/**
 * Tossee Report API
 * Simple PHP endpoint (no WordPress dependency)
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

// Database configuration (replace with your actual credentials)
$DB_HOST = 'localhost';
$DB_NAME = 'tossee_db';
$DB_USER = 'tossee_user';
$DB_PASS = 'your_password';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Create tables if they don't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS tossee_reports (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS tossee_settings (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert default setting
$pdo->exec("INSERT IGNORE INTO tossee_settings (setting_key, setting_value) VALUES ('report_button_enabled', '1')");

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

    $stmt = $pdo->prepare("
        INSERT INTO tossee_reports (reporter_id, reported_user_id, report_reason, additional_details)
        VALUES (:reporter_id, :reported_user_id, :report_reason, :additional_details)
    ");

    $stmt->execute([
        ':reporter_id' => htmlspecialchars($data['reporter_id']),
        ':reported_user_id' => htmlspecialchars($data['reported_user_id']),
        ':report_reason' => htmlspecialchars($data['report_reason']),
        ':additional_details' => isset($data['additional_details']) ? htmlspecialchars($data['additional_details']) : null
    ]);

    echo json_encode(['success' => true, 'message' => 'Report submitted', 'report_id' => $pdo->lastInsertId()]);
    exit;
}

// ================================================================
// GET SETTINGS
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT setting_value FROM tossee_settings WHERE setting_key = 'report_button_enabled'");
    $stmt->execute();
    $enabled = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'enabled' => ($enabled === '1')]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
