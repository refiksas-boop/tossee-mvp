<?php
/**
 * Tossee - Matching Proxy
 * Forwards matching requests to n8n webhook
 * Location: /chat/matching.php (on live server)
 *
 * IMPORTANT: This file should be placed in /chat/ directory on the WordPress server
 * Path: /home/u234011694/domains/tossee.com/public_html/chat/matching.php
 */

// CORS Headers (must be set before any output)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session if not already started
if (!session_id()) {
    @session_start();
}

// Helper function for sanitizing text (in case WordPress is not fully loaded)
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags(trim((string)$str));
    }
}

// Allow both GET and POST for testing
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST or GET.']);
    exit;
}

// Queue Manager URL (local PHP with MySQL)
$n8n_webhook_url = 'https://tossee.com/chat/api/queue-manager.php';

try {
    // Determine user ID
    $user_id = null;

    // Priority 1: From URL parameter
    if (!empty($_GET['uid'])) {
        $user_id = sanitize_text_field($_GET['uid']);
    }
    // Priority 2: From POST data
    elseif (!empty($_POST['uid'])) {
        $user_id = sanitize_text_field($_POST['uid']);
    }
    // Priority 3: From session
    elseif (isset($_SESSION['tossee_id'])) {
        $user_id = $_SESSION['tossee_id'];
    }

    if (!$user_id) {
        throw new Exception('User ID not provided');
    }

    // Get request body for POST requests
    $request_data = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $request_body = file_get_contents('php://input');
        if (!empty($request_body)) {
            $request_data = json_decode($request_body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON is invalid, try to use POST data
                $request_data = $_POST;
            }
        } else {
            $request_data = $_POST;
        }
    } else {
        // For GET requests, use query parameters
        $request_data = $_GET;
    }

    // Prepare data for n8n - use exact format expected by n8n workflow
    $n8n_data = [
        'userId' => $user_id,  // n8n expects 'userId' not 'user_id'
        'action' => $request_data['action'] ?? 'join',
        'timestamp' => time(),
        'source' => 'tossee_chat'
    ];

    // Add any additional data from request
    if (!empty($request_data)) {
        foreach ($request_data as $key => $value) {
            // Skip internal fields
            if (!in_array($key, ['uid', 'user_id', 'userId'])) {
                $n8n_data[$key] = $value;
            }
        }
    }

    // Always ensure userId is set correctly
    $n8n_data['userId'] = $user_id;

    // Log the request for debugging
    error_log('[Tossee Matching] Request from user: ' . $user_id . ' | Action: ' . $n8n_data['action']);

    // Initialize cURL
    $ch = curl_init();

    // Build URL with parameters for n8n
    $url_params = http_build_query($n8n_data);
    $full_url = $n8n_webhook_url . '?' . $url_params;

    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $n8n_webhook_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($n8n_data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Tossee-Chat/1.0'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

    // Log the full cURL info for debugging
    $curl_info = curl_getinfo($ch);
    error_log('[Tossee Matching] cURL info: ' . json_encode([
        'http_code' => $http_code,
        'total_time' => $curl_info['total_time'],
        'url' => $curl_info['url']
    ]));

    curl_close($ch);

    // Check for cURL errors
    if ($curl_errno) {
        error_log('[Tossee Matching] cURL error #' . $curl_errno . ': ' . $curl_error);
        throw new Exception('Connection error: ' . $curl_error . ' (Code: ' . $curl_errno . ')');
    }

    // Log raw response for debugging
    error_log('[Tossee Matching] Response HTTP ' . $http_code . ': ' . substr($response, 0, 200));

    // Check HTTP response code
    if ($http_code < 200 || $http_code >= 300) {
        throw new Exception('n8n webhook returned HTTP ' . $http_code . ' - Response: ' . substr($response, 0, 100));
    }

    // Try to parse response as JSON
    $response_data = json_decode($response, true);

    // If response is not JSON, wrap it
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('[Tossee Matching] Response is not valid JSON, wrapping it');
        $response_data = [
            'success' => true,
            'raw_response' => $response,
            'user_id' => $user_id
        ];
        $response = json_encode($response_data);
    }

    // Add debugging info in development
    if (isset($_GET['debug'])) {
        $response_data['_debug'] = [
            'user_id' => $user_id,
            'n8n_url' => $n8n_webhook_url,
            'http_code' => $http_code,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $response = json_encode($response_data);
    }

    // Log success
    error_log('[Tossee Matching] Success for user: ' . $user_id);

    // Return n8n response
    http_response_code($http_code);
    echo $response;

} catch (Exception $e) {
    // Log error with full details
    error_log('[Tossee Matching] ERROR: ' . $e->getMessage());
    error_log('[Tossee Matching] Stack trace: ' . $e->getTraceAsString());

    // Return detailed error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Matching service error',
        'message' => $e->getMessage(),
        'user_id' => $user_id ?? 'unknown',
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s'),
        // Include debug info if debug mode is enabled
        'debug' => isset($_GET['debug']) ? [
            'php_version' => PHP_VERSION,
            'method' => $_SERVER['REQUEST_METHOD'],
            'n8n_url' => $n8n_webhook_url
        ] : null
    ]);
}

// Test endpoint: /chat/matching.php?uid=test123&debug=1
