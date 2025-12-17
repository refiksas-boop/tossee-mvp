<?php
/**
 * Tossee - Signaling Proxy
 * Forwards WebRTC signaling messages to n8n webhook
 * Location: /chat/signaling.php (on live server)
 */

// Start session if not already started
if (!session_id()) {
    session_start();
}

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// n8n webhook URL for signaling
$n8n_webhook_url = 'https://n8n.tossee.com/webhook/signal';

try {
    // Get request body
    $request_body = file_get_contents('php://input');

    // Decode to validate JSON
    $request_data = json_decode($request_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON in request body');
    }

    // Validate signaling data
    if (!isset($request_data['type'])) {
        throw new Exception('Missing signaling type');
    }

    // Add session info if available
    if (isset($_SESSION['tossee_id'])) {
        $request_data['tossee_id'] = $_SESSION['tossee_id'];
    }

    // Add uid from GET/POST if provided
    if (!empty($_GET['uid'])) {
        $request_data['tossee_id'] = sanitize_text_field($_GET['uid']);
    } elseif (!empty($_POST['uid'])) {
        $request_data['tossee_id'] = sanitize_text_field($_POST['uid']);
    }

    // Add timestamp
    $request_data['timestamp'] = time();

    // Log the signaling message (optional - remove in production)
    error_log('Signaling message: ' . json_encode([
        'type' => $request_data['type'],
        'from' => $request_data['tossee_id'] ?? 'unknown',
        'to' => $request_data['target_id'] ?? 'broadcast'
    ]));

    // Initialize cURL
    $ch = curl_init($n8n_webhook_url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($request_data))
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    // Check for cURL errors
    if ($curl_error) {
        throw new Exception('cURL error: ' . $curl_error);
    }

    // Check HTTP response code
    if ($http_code !== 200 && $http_code !== 201) {
        throw new Exception('n8n webhook returned HTTP ' . $http_code);
    }

    // Validate response JSON
    $response_data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON in n8n response');
    }

    // Log success (optional - remove in production)
    error_log('Signaling response received');

    // Return n8n response
    http_response_code($http_code);
    echo $response;

} catch (Exception $e) {
    // Log error
    error_log('Signaling proxy error: ' . $e->getMessage());

    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Signaling service error',
        'message' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
