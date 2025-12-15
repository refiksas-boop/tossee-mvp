<?php
/**
 * Matching API Proxy
 * Forwards requests to n8n webhook
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request body
$input = file_get_contents('php://input');

// n8n webhook URL
$n8nUrl = 'https://n8n.tossee.com/webhook/next';

// Forward to n8n
$ch = curl_init($n8nUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($input)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle errors
if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'error' => 'Failed to connect to n8n',
        'details' => $curlError
    ]);
    exit;
}

// Return n8n response
http_response_code($httpCode);
echo $response;
