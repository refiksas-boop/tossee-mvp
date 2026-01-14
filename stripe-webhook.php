<?php
/**
 * STRIPE WEBHOOK - Prideda sekundes po sėkmingo mokėjimo
 *
 * Setup:
 * 1. Upload į: /public_html/chat/api/stripe-webhook.php
 * 2. Stripe Dashboard → Developers → Webhooks → Add endpoint
 * 3. URL: https://tossee.com/chat/api/stripe-webhook.php
 * 4. Event: checkout.session.completed
 * 5. Copy webhook secret → config.php: define('STRIPE_WEBHOOK_SECRET', 'whsec_...');
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$endpoint_secret = defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : '';

if (empty($endpoint_secret)) {
    error_log('[webhook] STRIPE_WEBHOOK_SECRET not defined in config.php');
    http_response_code(500);
    exit;
}

// Verify webhook signature
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    error_log('[webhook] Invalid payload: ' . $e->getMessage());
    http_response_code(400);
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    error_log('[webhook] Invalid signature: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

// Handle checkout.session.completed
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    $user_id = $session->metadata->user_id ?? null;
    $plan = $session->metadata->plan ?? null;

    if (!$user_id || !$plan) {
        error_log('[webhook] ❌ Missing metadata: user_id=' . ($user_id ?? 'null') . ', plan=' . ($plan ?? 'null'));
        http_response_code(400);
        exit;
    }

    // Plan → sekundės
    $seconds_map = [
        'addon_30min'   => 1800,     // 30 min
        'unlimited_24h' => 86400,    // 24h = 86400s
        'unlimited_7d'  => 604800,   // 7 days = 604800s
        'unlimited_30d' => 2592000,  // 30 days = 2592000s
    ];

    if (!isset($seconds_map[$plan])) {
        error_log('[webhook] ❌ Unknown plan: ' . $plan);
        http_response_code(400);
        exit;
    }

    $seconds = $seconds_map[$plan];

    // Database update
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Update paid_seconds (dob kolona = UID)
        $stmt = $pdo->prepare("
            UPDATE wp_tossee_users
            SET paid_seconds = paid_seconds + :seconds
            WHERE dob = :uid
        ");

        $stmt->execute([
            'seconds' => $seconds,
            'uid' => $user_id
        ]);

        if ($stmt->rowCount() > 0) {
            error_log("[webhook] ✅ SUCCESS: Added {$seconds}s to user {$user_id} (plan: {$plan})");
        } else {
            error_log("[webhook] ⚠️ User not found: {$user_id}");
        }

    } catch (PDOException $e) {
        error_log('[webhook] ❌ DB error: ' . $e->getMessage());
        http_response_code(500);
        exit;
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
