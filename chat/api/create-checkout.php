<?php
/**
 * Tossee - Create Stripe Checkout Session
 * Creates a Stripe checkout session with user identification
 *
 * Accepts: POST with JSON body { uid: "tossee_xxx", plan: "addon_30min" }
 * Returns: JSON with { sessionId, url } or { error }
 */

define('TOSSEE_API', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate required fields
if (empty($data['uid']) || empty($data['plan'])) {
    jsonResponse([
        'error' => 'Missing required fields',
        'required' => ['uid', 'plan']
    ], 400);
}

$uid = $data['uid'];
$planKey = $data['plan'];

// Validate tossee_id format
if (!preg_match('/^tossee_[a-zA-Z0-9]+$/', $uid)) {
    jsonResponse(['error' => 'Invalid uid format'], 400);
}

// Get plan configuration
$plan = getPlan($planKey);
if (!$plan) {
    jsonResponse(['error' => 'Invalid plan', 'plan' => $planKey], 400);
}

// Verify user exists
$user = getUserByTosseeId($uid);
if (!$user) {
    jsonResponse(['error' => 'User not found'], 404);
}

try {
    // Prepare line items for checkout
    $lineItems = [];

    if ($plan['type'] === 'addon') {
        // One-time purchase for addon minutes
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $plan['name'],
                    'description' => sprintf('+%d minutes of chat time', $plan['seconds'] / 60),
                ],
                'unit_amount' => $plan['price'], // cents
            ],
            'quantity' => 1,
        ];
    } else {
        // Unlimited time period
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $plan['name'],
                    'description' => sprintf('Unlimited chat for %s', $plan['duration']),
                ],
                'unit_amount' => $plan['price'],
            ],
            'quantity' => 1,
        ];
    }

    // Create Stripe Checkout Session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => CHAT_URL . '?payment=success&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => SITE_URL . '/pricing?payment=cancelled',
        'client_reference_id' => $uid, // CRITICAL: This links payment to user
        'metadata' => [
            'uid' => $uid,
            'plan' => $planKey,
            'username' => $user['username'],
            'email' => $user['email'],
        ],
        'customer_email' => $user['email'], // Pre-fill email
        'billing_address_collection' => 'auto',
    ]);

    // Log successful session creation
    logError('Checkout session created', [
        'session_id' => $session->id,
        'uid' => $uid,
        'plan' => $planKey,
        'amount' => $plan['price']
    ]);

    // Return session info
    jsonResponse([
        'success' => true,
        'sessionId' => $session->id,
        'url' => $session->url
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    logError('Stripe API error', [
        'error' => $e->getMessage(),
        'uid' => $uid,
        'plan' => $planKey
    ]);

    jsonResponse([
        'error' => 'Payment system error',
        'message' => $e->getMessage()
    ], 500);

} catch (Exception $e) {
    logError('Unexpected error in create-checkout', [
        'error' => $e->getMessage(),
        'uid' => $uid
    ]);

    jsonResponse([
        'error' => 'Internal server error'
    ], 500);
}
