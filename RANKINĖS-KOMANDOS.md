# Deployment Instructions for chat.tossee.com

## Option 1: Manual Commands (Copy-paste)

### 1️⃣ Connect to VPS
```bash
ssh -p 65002 u234011694@62.72.34.8
```

### 2️⃣ Create directories
```bash
mkdir -p /var/www/tossee.com/chat/api
cd /var/www/tossee.com/chat
```

### 3️⃣ Create matching.php
```bash
cat > api/matching.php << 'PHPEOF'
<?php
/**
 * N8N Matching Proxy
 * Handles CORS for n8n webhook
 */

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
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

// Validate JSON
$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Forward to n8n
$n8nUrl = 'https://n8n.tossee.com/webhook/matching';

$ch = curl_init($n8nUrl);
curl_setopt($ch, CURLOPT_POST, true);
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
$error = curl_error($ch);
curl_close($ch);

// Handle errors
if ($error) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'N8N connection failed: ' . $error
    ]);
    exit;
}

// Return n8n response
http_response_code($httpCode);
echo $response;
PHPEOF
```

### 4️⃣ Create signaling.php
```bash
cat > api/signaling.php << 'PHPEOF'
<?php
/**
 * N8N Signaling Proxy
 * Handles CORS for n8n webhook
 */

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
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

// Validate JSON
$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Forward to n8n
$n8nUrl = 'https://n8n.tossee.com/webhook/signal';

$ch = curl_init($n8nUrl);
curl_setopt($ch, CURLOPT_POST, true);
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
$error = curl_error($ch);
curl_close($ch);

// Handle errors
if ($error) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'N8N connection failed: ' . $error
    ]);
    exit;
}

// Return n8n response
http_response_code($httpCode);
echo $response;
PHPEOF
```

### 5️⃣ Set permissions
```bash
chmod 644 /var/www/tossee.com/chat/api/*.php
```

### 6️⃣ Download index.html from Git
```bash
cd /var/www/tossee.com/chat
wget https://raw.githubusercontent.com/refiksas-boop/tossee-mvp/claude/init-workflow-storage-01CSdmmpqgG8rCgDRg9DXb8u/chat.tossee.com/index.html
chmod 644 index.html
```

### 7️⃣ Verify files
```bash
ls -la /var/www/tossee.com/chat/
ls -la /var/www/tossee.com/chat/api/
```

---

## ⚠️ IMPORTANT: Configure chat.tossee.com subdomain!

### A) DNS Settings (Cloudflare/Domain Registrar):
```
Type: A
Name: chat
Value: 62.72.34.8
TTL: Auto
```

### B) Web Server Configuration:

If you're using **hosting panel** (cPanel/Plesk), create subdomain pointing to `/var/www/tossee.com/chat/`

If you're using **nginx** directly, ask ChatGPT to create virtual host config.

---

## 🧪 Test after deployment:
```bash
# Test from VPS:
curl -X POST http://localhost/chat/api/matching.php -H "Content-Type: application/json" -d '{"action":"join","userId":"test123"}'

# Test from browser:
https://chat.tossee.com/api/matching.php
```
