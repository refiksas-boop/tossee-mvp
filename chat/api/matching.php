<?php
/**
 * Tossee - Matching Endpoint (Stub)
 * Handles user matching for video chat
 * TODO: Implement full matching logic with queue system
 */

define('TOSSEE_API', true);
require_once __DIR__ . '/config.php';

// Get uid from request
$uid = $_GET['uid'] ?? $_POST['uid'] ?? null;

if (!$uid) {
    jsonResponse(['error' => 'Missing uid parameter'], 400);
}

// Validate user exists
$user = getUserByTosseeId($uid);
if (!$user) {
    jsonResponse(['error' => 'User not found'], 404);
}

// TODO: Implement queue logic here
// For now, return a placeholder response
jsonResponse([
    'ok' => true,
    'status' => 'waiting',
    'queueSize' => 0,
    'uid' => $uid,
    'message' => 'Matching endpoint - implementation pending'
]);
