<?php
/**
 * Tossee - Access Check Endpoint
 * Checks if user has access to chat based on payment status
 *
 * Accepts: GET/POST with uid parameter
 * Returns: JSON with { allowed, unlimited, remaining_seconds, unlimited_until }
 */

define('TOSSEE_API', true);
require_once __DIR__ . '/config.php';

// Get uid from request
$uid = $_GET['uid'] ?? $_POST['uid'] ?? null;

if (!$uid) {
    jsonResponse(['error' => 'Missing uid parameter'], 400);
}

// Validate tossee_id format
if (!preg_match('/^tossee_[a-zA-Z0-9]+$/', $uid)) {
    jsonResponse(['error' => 'Invalid uid format'], 400);
}

// Get user from database
$user = getUserByTosseeId($uid);
if (!$user) {
    jsonResponse(['error' => 'User not found'], 404);
}

// Calculate access
$now = new DateTime('now', new DateTimeZone('UTC'));
$response = [
    'uid' => $uid,
    'allowed' => false,
    'unlimited' => false,
    'remaining_seconds' => 0,
    'unlimited_until' => null,
    'free_remaining' => 0,
    'paid_remaining' => 0
];

// Check if user has unlimited access
if ($user['unlimited_until']) {
    $unlimitedUntil = new DateTime($user['unlimited_until'], new DateTimeZone('UTC'));

    if ($unlimitedUntil > $now) {
        $response['allowed'] = true;
        $response['unlimited'] = true;
        $response['unlimited_until'] = $user['unlimited_until'];
        $response['remaining_seconds'] = PHP_INT_MAX; // Effectively unlimited
        jsonResponse($response);
    }
}

// Calculate remaining free seconds
$freeUsed = (int)$user['free_used_seconds'];
$freeRemaining = max(0, FREE_SECONDS - $freeUsed);

// Calculate remaining paid seconds
$paidRemaining = (int)$user['paid_seconds'];

// Total available seconds
$totalRemaining = $freeRemaining + $paidRemaining;

$response['free_remaining'] = $freeRemaining;
$response['paid_remaining'] = $paidRemaining;
$response['remaining_seconds'] = $totalRemaining;
$response['allowed'] = $totalRemaining > 0;

jsonResponse($response);
