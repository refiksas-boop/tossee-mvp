<?php
/**
 * TEST SESSION PAGE
 * Parodo sesijos duomenis
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1a1a1a;
            color: #00ff00;
        }
        .box {
            background: #000;
            border: 2px solid #00ff00;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
        }
        h2 { color: #00ffff; }
    </style>
</head>
<body>
    <h1>🔍 TOSSEE SESSION DEBUG</h1>

    <div class="box">
        <h2>SESSION DATA:</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>

    <div class="box">
        <h2>COOKIES:</h2>
        <pre><?php print_r($_COOKIE); ?></pre>
    </div>

    <div class="box">
        <h2>SESSION STATUS:</h2>
        <p>Session ID: <?php echo session_id(); ?></p>
        <p>Session Status: <?php echo session_status(); ?></p>
        <p>Has tossee_id: <?php echo isset($_SESSION['tossee_id']) ? 'YES ✅' : 'NO ❌'; ?></p>
        <?php if (isset($_SESSION['tossee_id'])): ?>
            <p>Tossee ID: <?php echo htmlspecialchars($_SESSION['tossee_id']); ?></p>
        <?php endif; ?>
    </div>

    <div class="box">
        <h2>API TEST:</h2>
        <button onclick="testAPI()">Test /wp-json/tossee/v1/profile</button>
        <pre id="apiResult"></pre>
    </div>

    <script>
        async function testAPI() {
            const result = document.getElementById('apiResult');
            result.textContent = 'Loading...';

            try {
                const response = await fetch('/wp-json/tossee/v1/profile', {
                    credentials: 'include'
                });

                const data = await response.json();
                result.textContent = JSON.stringify(data, null, 2);
            } catch (err) {
                result.textContent = 'ERROR: ' + err.message;
            }
        }
    </script>
</body>
</html>
