<?php
/**
 * Tossee - Queue Manager (Stub)
 * Manages the waiting queue for video chat matching
 * TODO: Implement queue management with Redis or database
 */

define('TOSSEE_API', true);
require_once __DIR__ . '/config.php';

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';
$uid = $_GET['uid'] ?? $_POST['uid'] ?? null;

if ($action === 'join' && $uid) {
    jsonResponse([
        'ok' => true,
        'action' => 'join',
        'uid' => $uid,
        'position' => 1,
        'message' => 'Added to queue'
    ]);
}

if ($action === 'leave' && $uid) {
    jsonResponse([
        'ok' => true,
        'action' => 'leave',
        'uid' => $uid,
        'message' => 'Removed from queue'
    ]);
}

// Default: return queue status
jsonResponse([
    'ok' => true,
    'action' => 'status',
    'queueSize' => 0,
    'message' => 'Queue manager - implementation pending'
]);
