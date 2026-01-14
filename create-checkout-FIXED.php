<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $uid  = isset($data['uid']) ? trim((string)$data['uid']) : '';
    $plan = isset($data['plan']) ? trim((string)$data['plan']) : '';

    if ($uid === '' || $plan === '') {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'missing_uid_or_plan']);
        exit;
    }

    // FIXED: Plan keys match pricing page
    $plans = [
        'addon_30min'      => ['amount' => 99,   'name' => '+30 Minutes'],
        'unlimited_24h'    => ['amount' => 199,  'name' => '24h Unlimited'],
        'unlimited_7d'     => ['amount' => 999,  'name' => '7 Days Unlimited'],
        'unlimited_30d'    => ['amount' => 1999, 'name' => 'Monthly Unlimited'],
    ];

    if (!isset($plans[$plan])) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'unknown_plan']);
        exit;
    }

    $session = \Stripe\Checkout\Session::create([
        'mode' => 'payment',
        'client_reference_id' => $uid,
        'metadata' => [
            'user_id' => $uid,   // ✅ FIXED: Added UID to metadata
            'plan' => $plan
        ],
        'line_items' => [[
            'quantity' => 1,
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $plans[$plan]['amount'],
                'product_data' => [
                    'name' => $plans[$plan]['name'],
                ],
            ],
        ]],
        'success_url' => 'https://tossee.com/chat/?paid=1&uid=' . urlencode($uid),  // ✅ FIXED: Added uid to success URL
        'cancel_url'  => 'https://tossee.com/pricing-plans/?canceled=1&uid=' . urlencode($uid),  // ✅ FIXED: Added uid to cancel URL
    ]);

    echo json_encode(['ok'=>true, 'url'=>$session->url]);
    exit;

} catch (Throwable $e) {
    error_log('[create-checkout] ERROR: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'server_error']);
    exit;
}
