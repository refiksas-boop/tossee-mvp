<?php
/**
 * Tossee - Stripe Webhook Handler
 * Processes Stripe events and updates user payment status
 *
 * Handles: checkout.session.completed
 * Updates: wp_tossee_users (paid_seconds, unlimited_until)
 */

define('TOSSEE_API', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get raw POST body
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Verify webhook signature
try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        STRIPE_WEBHOOK_SECRET
    );
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    logError('Webhook: Invalid payload', ['error' => $e->getMessage()]);
    http_response_code(400);
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    logError('Webhook: Invalid signature', ['error' => $e->getMessage()]);
    http_response_code(400);
    exit;
}

// Handle the event
$eventType = $event->type;

logError('Webhook received', [
    'type' => $eventType,
    'id' => $event->id
]);

// Process checkout.session.completed
if ($eventType === 'checkout.session.completed') {
    $session = $event->data->object;

    // Extract user identification
    $uid = $session->client_reference_id ?? $session->metadata->uid ?? null;
    $planKey = $session->metadata->plan ?? null;

    if (!$uid || !$planKey) {
        logError('Webhook: Missing uid or plan', [
            'session_id' => $session->id,
            'client_reference_id' => $session->client_reference_id,
            'metadata' => $session->metadata
        ]);
        http_response_code(200); // Still return 200 to acknowledge receipt
        exit;
    }

    // Get plan configuration
    $plan = getPlan($planKey);
    if (!$plan) {
        logError('Webhook: Invalid plan', [
            'plan' => $planKey,
            'uid' => $uid
        ]);
        http_response_code(200);
        exit;
    }

    // Verify user exists
    $user = getUserByTosseeId($uid);
    if (!$user) {
        logError('Webhook: User not found', ['uid' => $uid]);
        http_response_code(200);
        exit;
    }

    // Update user payment based on plan type
    try {
        if ($plan['type'] === 'addon') {
            // Add paid seconds
            $newPaidSeconds = $user['paid_seconds'] + $plan['seconds'];

            updateUserPayment($uid, [
                'paid_seconds' => $newPaidSeconds
            ]);

            logError('Webhook: Added paid time', [
                'uid' => $uid,
                'added_seconds' => $plan['seconds'],
                'total_paid_seconds' => $newPaidSeconds,
                'session_id' => $session->id
            ]);

        } else {
            // Set unlimited_until based on duration
            $now = new DateTime('now', new DateTimeZone('UTC'));

            switch ($planKey) {
                case 'unlimited_24h':
                    $now->modify('+24 hours');
                    break;
                case 'unlimited_7d':
                    $now->modify('+7 days');
                    break;
                case 'unlimited_30d':
                    $now->modify('+30 days');
                    break;
                default:
                    logError('Webhook: Unknown unlimited plan', ['plan' => $planKey]);
                    http_response_code(200);
                    exit;
            }

            $unlimitedUntil = $now->format('Y-m-d H:i:s');

            // If user already has unlimited_until, extend from that date instead of now
            if ($user['unlimited_until'] && strtotime($user['unlimited_until']) > time()) {
                $existingUntil = new DateTime($user['unlimited_until'], new DateTimeZone('UTC'));

                switch ($planKey) {
                    case 'unlimited_24h':
                        $existingUntil->modify('+24 hours');
                        break;
                    case 'unlimited_7d':
                        $existingUntil->modify('+7 days');
                        break;
                    case 'unlimited_30d':
                        $existingUntil->modify('+30 days');
                        break;
                }

                $unlimitedUntil = $existingUntil->format('Y-m-d H:i:s');
            }

            updateUserPayment($uid, [
                'unlimited_until' => $unlimitedUntil
            ]);

            logError('Webhook: Set unlimited access', [
                'uid' => $uid,
                'plan' => $planKey,
                'unlimited_until' => $unlimitedUntil,
                'session_id' => $session->id
            ]);
        }

        // Success - return 200
        http_response_code(200);
        echo json_encode(['received' => true]);

    } catch (Exception $e) {
        logError('Webhook: DB update failed', [
            'error' => $e->getMessage(),
            'uid' => $uid,
            'plan' => $planKey
        ]);
        http_response_code(200); // Still return 200 to prevent retries
        exit;
    }

} else {
    // Other event types - acknowledge but don't process
    logError('Webhook: Unhandled event type', ['type' => $eventType]);
    http_response_code(200);
    echo json_encode(['received' => true]);
}
