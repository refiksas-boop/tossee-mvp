<?php
/**
 * Tossee Chat API Configuration
 * Standalone config file (no WordPress dependencies)
 */

// Prevent direct access
if (!defined('TOSSEE_API')) {
    define('TOSSEE_API', true);
}

// ========================================
// DATABASE CONFIGURATION
// ========================================
// TODO: Update these with your actual database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u234011694_tossee');  // Update this
define('DB_USER', 'u234011694_user');    // Update this
define('DB_PASS', 'your_db_password');    // Update this
define('DB_CHARSET', 'utf8mb4');
define('DB_TABLE_PREFIX', 'wp_');

// ========================================
// STRIPE CONFIGURATION
// ========================================
// TODO: Get these from Stripe Dashboard (https://dashboard.stripe.com/apikeys)
define('STRIPE_SECRET_KEY', 'sk_test_...'); // Your Stripe Secret Key
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...'); // Your Stripe Publishable Key
define('STRIPE_WEBHOOK_SECRET', 'whsec_...'); // Your Webhook Signing Secret

// ========================================
// SITE CONFIGURATION
// ========================================
define('SITE_URL', 'https://tossee.com');
define('CHAT_URL', 'https://chat.tossee.com');

// ========================================
// PLAN CONFIGURATION
// ========================================
define('FREE_MINUTES', 30); // Free minutes for new users
define('FREE_SECONDS', FREE_MINUTES * 60);

// Plan definitions: [seconds, price_id, name]
$PLANS = [
    'addon_30min' => [
        'seconds' => 1800,  // 30 minutes
        'price' => 99,      // cents
        'name' => '+30 Minutes',
        'type' => 'addon'
    ],
    'unlimited_24h' => [
        'duration' => '24 hours',
        'price' => 199,
        'name' => '24h Unlimited',
        'type' => 'unlimited'
    ],
    'unlimited_7d' => [
        'duration' => '7 days',
        'price' => 999,
        'name' => '7 Days Unlimited',
        'type' => 'unlimited'
    ],
    'unlimited_30d' => [
        'duration' => '30 days',
        'price' => 1999,
        'name' => 'Monthly Unlimited',
        'type' => 'unlimited'
    ]
];

// ========================================
// PDO DATABASE CONNECTION HELPER
// ========================================
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }

    return $pdo;
}

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Get user by tossee_id
 */
function getUserByTosseeId($tossee_id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM " . DB_TABLE_PREFIX . "tossee_users WHERE tossee_id = ?");
    $stmt->execute([$tossee_id]);
    return $stmt->fetch();
}

/**
 * Update user payment fields
 */
function updateUserPayment($tossee_id, $data) {
    $pdo = getDB();

    $fields = [];
    $values = [];

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }

    $values[] = $tossee_id;

    $sql = "UPDATE " . DB_TABLE_PREFIX . "tossee_users SET " . implode(', ', $fields) . " WHERE tossee_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Get plan configuration
 */
function getPlan($plan_key) {
    global $PLANS;
    return isset($PLANS[$plan_key]) ? $PLANS[$plan_key] : null;
}

/**
 * Send JSON response
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log error to file
 */
function logError($message, $context = []) {
    $log = sprintf(
        "[%s] %s %s\n",
        date('Y-m-d H:i:s'),
        $message,
        !empty($context) ? json_encode($context) : ''
    );
    error_log($log, 3, __DIR__ . '/error.log');
}
