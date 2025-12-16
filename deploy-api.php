<?php
/**
 * DEPLOY API FILES AND HTACCESS
 * Creates API proxy files and .htaccess configuration
 *
 * Upload to: /home/u234011694/domains/tossee.com/public_html/chat/
 * Run: https://chat.tossee.com/deploy-api.php
 * Delete after use
 */

header('Content-Type: text/html; charset=utf-8');

$action = $_GET['action'] ?? '';
$baseDir = __DIR__;
$apiDir = $baseDir . '/api';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Deploy API Files</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        button { background: #3498db; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #2980b9; }
        code { background: #ecf0f1; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🚀 Deploy API Files</h1>

<?php

if ($action === '') {
    echo '<div class="box">';
    echo '<h2>Dabartinė būsena:</h2>';
    echo '<ul>';

    if (is_dir($apiDir)) {
        echo '<li class="success">✅ api/ aplankas egzistuoja</li>';

        if (file_exists($apiDir . '/matching.php')) {
            echo '<li class="success">✅ matching.php egzistuoja</li>';
        } else {
            echo '<li class="error">❌ matching.php NERASTAS</li>';
        }

        if (file_exists($apiDir . '/signaling.php')) {
            echo '<li class="success">✅ signaling.php egzistuoja</li>';
        } else {
            echo '<li class="error">❌ signaling.php NERASTAS</li>';
        }

        if (file_exists($apiDir . '/.htaccess')) {
            echo '<li class="success">✅ api/.htaccess egzistuoja</li>';
        } else {
            echo '<li class="warning">⚠️ api/.htaccess NERASTAS</li>';
        }
    } else {
        echo '<li class="error">❌ api/ aplankas NERASTAS</li>';
    }

    if (file_exists($baseDir . '/.htaccess')) {
        echo '<li class="success">✅ chat/.htaccess egzistuoja</li>';
    } else {
        echo '<li class="warning">⚠️ chat/.htaccess NERASTAS</li>';
    }

    echo '</ul>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Veiksmai:</h2>';
    echo '<form method="get">';
    echo '<button type="submit" name="action" value="deploy">🚀 SUKURTI API FAILUS</button>';
    echo '</form>';
    echo '<p><small>Sukurs: api/matching.php, api/signaling.php, .htaccess failus</small></p>';
    echo '</div>';

} elseif ($action === 'deploy') {
    echo '<div class="box">';
    echo '<h2>🚀 Kuriami failai...</h2>';
    echo '<pre>';

    // Create api directory
    if (!is_dir($apiDir)) {
        if (mkdir($apiDir, 0755, true)) {
            echo "✅ Sukurtas: api/\n";
        } else {
            echo "❌ KLAIDA: Nepavyko sukurti api/\n";
            exit;
        }
    } else {
        echo "✓ api/ jau egzistuoja\n";
    }

    echo "\n";

    // Create matching.php
    $matchingPHP = <<<'PHP'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$n8nUrl = 'https://n8n.tossee.com/webhook/next';

$ch = curl_init($n8nUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($input)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Failed to connect to n8n']);
    exit;
}

http_response_code($httpCode);
echo $response;
PHP;

    if (file_put_contents($apiDir . '/matching.php', $matchingPHP)) {
        echo "✅ Sukurtas: api/matching.php\n";
    } else {
        echo "❌ KLAIDA: Nepavyko sukurti matching.php\n";
    }

    // Create signaling.php
    $signalingPHP = <<<'PHP'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$n8nUrl = 'https://n8n.tossee.com/webhook/signal';

$ch = curl_init($n8nUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($input)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Failed to connect to n8n']);
    exit;
}

http_response_code($httpCode);
echo $response;
PHP;

    if (file_put_contents($apiDir . '/signaling.php', $signalingPHP)) {
        echo "✅ Sukurtas: api/signaling.php\n";
    } else {
        echo "❌ KLAIDA: Nepavyko sukurti signaling.php\n";
    }

    echo "\n";

    // Create chat/.htaccess
    $chatHtaccess = <<<'HTACCESS'
# Disable WordPress rewrites for chat directory
RewriteEngine Off

# Allow direct file access
<FilesMatch "\.(php|html|js|css)$">
    Require all granted
</FilesMatch>

# CORS headers
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "POST, GET, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type"
</IfModule>
HTACCESS;

    if (file_put_contents($baseDir . '/.htaccess', $chatHtaccess)) {
        echo "✅ Sukurtas: chat/.htaccess\n";
    } else {
        echo "❌ KLAIDA: Nepavyko sukurti chat/.htaccess\n";
    }

    // Create api/.htaccess
    $apiHtaccess = <<<'HTACCESS'
# Direct PHP execution - no WordPress routing
RewriteEngine Off

<FilesMatch "\.php$">
    Require all granted
</FilesMatch>

# CORS headers
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "POST, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type"
</IfModule>
HTACCESS;

    if (file_put_contents($apiDir . '/.htaccess', $apiHtaccess)) {
        echo "✅ Sukurtas: api/.htaccess\n";
    } else {
        echo "❌ KLAIDA: Nepavyko sukurti api/.htaccess\n";
    }

    echo "\n";

    // Set permissions
    chmod($apiDir, 0755);
    chmod($apiDir . '/matching.php', 0644);
    chmod($apiDir . '/signaling.php', 0644);
    chmod($apiDir . '/.htaccess', 0644);
    chmod($baseDir . '/.htaccess', 0644);
    echo "✅ Nustatytos teisės (755/644)\n";

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ DEPLOYMENT SĖKMINGAS!\n";
    echo '</pre>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>✅ Sukurti failai:</h2>';
    echo '<ul>';
    echo '<li><code>chat/api/matching.php</code> - Matching API proxy</li>';
    echo '<li><code>chat/api/signaling.php</code> - Signaling API proxy</li>';
    echo '<li><code>chat/.htaccess</code> - Chat directory config</li>';
    echo '<li><code>chat/api/.htaccess</code> - API directory config</li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>🧪 Testuoti:</h2>';
    echo '<pre>';
    echo 'curl -X POST https://chat.tossee.com/api/matching.php \\\n';
    echo '  -H "Content-Type: application/json" \\\n';
    echo '  -d \'{"userId":"test","action":"join"}\'';
    echo '</pre>';
    echo '<p>Turėtų grąžinti: HTTP 200 arba 502 (jei n8n workflow neaktyvus)</p>';
    echo '</div>';

    echo '<div class="box" style="background: #fff3cd; border-left: 4px solid #f39c12;">';
    echo '<h3>⚠️ SVARBU - n8n workflows!</h3>';
    echo '<p>API failai sukurti, bet <strong>n8n workflows turi būti aktyvūs</strong>:</p>';
    echo '<ol>';
    echo '<li>Eik į: <a href="https://n8n.tossee.com/" target="_blank">https://n8n.tossee.com/</a></li>';
    echo '<li>Prisijunk</li>';
    echo '<li>Aktyvuok "Matching" workflow (toggle į "Active")</li>';
    echo '<li>Aktyvuok "Signaling" workflow (toggle į "Active")</li>';
    echo '<li>Status turi rodyti: <strong>2/2 Active</strong></li>';
    echo '</ol>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>🗑️ Ištrinti šį failą:</h2>';
    echo '<p>Po deployment ištrink <code>deploy-api.php</code> saugumo sumetimais!</p>';
    echo '<form method="get">';
    echo '<button type="submit" name="action" value="delete" style="background: #e74c3c;">Ištrinti deploy-api.php</button>';
    echo '</form>';
    echo '</div>';

} elseif ($action === 'delete') {
    echo '<div class="box">';
    echo '<h2>🗑️ Trinamas deploy-api.php...</h2>';
    if (unlink(__FILE__)) {
        echo '<p class="success">✅ Failas ištrintas!</p>';
    } else {
        echo '<p class="error">❌ Nepavyko ištrinti. Ištrink per File Manager.</p>';
    }
    echo '</div>';
}

?>

    <div class="box" style="font-size: 12px; color: #7f8c8d;">
        <p>Current directory: <?php echo $baseDir; ?></p>
        <p>Script: <?php echo __FILE__; ?></p>
        <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>
