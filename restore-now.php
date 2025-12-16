<?php
/**
 * TOSSEE RESTORE SCRIPT
 * Grąžina visus pluginus ir Astra temą atgal
 *
 * NAUDOJIMAS:
 * 1. Upload šį failą į: /home/u234011694/domains/tossee.com/public_html/
 * 2. Atidaryti: https://tossee.com/restore-now.php
 * 3. Ištrinti failą po naudojimo
 */

// Security - only allow from localhost or specific IP
$allowed_ips = ['127.0.0.1', '::1'];
// Comment out the next 3 lines if you can't access
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
//     die('Access denied. Run from server or add your IP to allowed list.');
// }

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tossee Restore</title>
    <style>
        body {
            font-family: Arial;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
        }
        button:hover { background: #2980b9; }
        .danger { background: #e74c3c; }
        .danger:hover { background: #c0392b; }
    </style>
</head>
<body>
    <h1>🔄 Tossee Restore Script</h1>

<?php

$wpContentPath = __DIR__ . '/wp-content';
$pluginsDisabled = $wpContentPath . '/plugins.DISABLED';
$pluginsActive = $wpContentPath . '/plugins';
$themeDisabled = $wpContentPath . '/themes/astra.DISABLED';
$themeActive = $wpContentPath . '/themes/astra';

// Check if action requested
$action = $_GET['action'] ?? '';

if ($action === '') {
    // Show status and options
    ?>
    <div class="box">
        <h2>Dabartinė būsena:</h2>
        <ul>
            <?php if (is_dir($pluginsDisabled)): ?>
                <li class="warning">⚠️ Plugins išjungti (plugins.DISABLED egzistuoja)</li>
            <?php else: ?>
                <li class="success">✅ Plugins įjungti</li>
            <?php endif; ?>

            <?php if (is_dir($themeDisabled)): ?>
                <li class="warning">⚠️ Astra tema išjungta (astra.DISABLED egzistuoja)</li>
            <?php else: ?>
                <li class="success">✅ Astra tema įjungta</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="box">
        <h2>Veiksmai:</h2>
        <form method="get">
            <button type="submit" name="action" value="restore">
                🔄 GRĄŽINTI VISKĄ ATGAL
            </button>
            <button type="submit" name="action" value="check" style="background: #95a5a6;">
                🔍 Patikrinti būseną
            </button>
        </form>

        <p><small>Tai grąžins plugins.DISABLED → plugins ir astra.DISABLED → astra</small></p>
    </div>

    <div class="box">
        <h3>⚠️ Po restauravimo:</h3>
        <ol>
            <li>Patikrink ar veikia: <a href="https://tossee.com/" target="_blank">https://tossee.com/</a></li>
            <li><strong>IŠTRINK ŠĮ FAILĄ</strong> (restore-now.php) - saugumo sumetimais!</li>
        </ol>
    </div>
    <?php

} elseif ($action === 'restore') {
    // Perform restoration
    echo '<div class="box">';
    echo '<h2>🔄 Vykdomas restauravimas...</h2>';
    echo '<pre>';

    $errors = [];
    $success = [];

    // Restore plugins
    echo "1. Grąžiname plugins...\n";
    if (is_dir($pluginsDisabled)) {
        // Remove empty plugins dir if exists
        if (is_dir($pluginsActive)) {
            if (rmdir($pluginsActive)) {
                echo "   ✓ Pašalintas tuščias plugins aplankas\n";
            } else {
                // Try to remove recursively
                exec("rm -rf " . escapeshellarg($pluginsActive), $output, $ret);
                if ($ret === 0) {
                    echo "   ✓ Pašalintas plugins aplankas\n";
                }
            }
        }

        // Rename plugins.DISABLED to plugins
        if (rename($pluginsDisabled, $pluginsActive)) {
            echo "   ✅ Plugins grąžinti! (plugins.DISABLED → plugins)\n";
            $success[] = 'plugins restored';
        } else {
            echo "   ❌ KLAIDA: Nepavyko pervadinti plugins.DISABLED\n";
            $errors[] = 'plugins restore failed';
        }
    } else {
        echo "   ℹ️  plugins.DISABLED nerastas (galbūt jau grąžintas?)\n";
    }

    echo "\n2. Grąžiname Astra temą...\n";
    if (is_dir($themeDisabled)) {
        // Remove current astra if exists
        if (is_dir($themeActive)) {
            exec("rm -rf " . escapeshellarg($themeActive), $output, $ret);
            if ($ret === 0) {
                echo "   ✓ Pašalintas astra aplankas\n";
            }
        }

        // Rename astra.DISABLED to astra
        if (rename($themeDisabled, $themeActive)) {
            echo "   ✅ Astra tema grąžinta! (astra.DISABLED → astra)\n";
            $success[] = 'astra theme restored';
        } else {
            echo "   ❌ KLAIDA: Nepavyko pervadinti astra.DISABLED\n";
            $errors[] = 'astra restore failed';
        }
    } else {
        echo "   ℹ️  astra.DISABLED nerastas (galbūt jau grąžintas?)\n";
    }

    echo "\n3. Tikriname teises...\n";
    if (is_dir($pluginsActive)) {
        chmod($pluginsActive, 0755);
        echo "   ✓ Plugins teisės: 0755\n";
    }
    if (is_dir($themeActive)) {
        chmod($themeActive, 0755);
        echo "   ✓ Astra teisės: 0755\n";
    }

    echo "\n";
    echo str_repeat("=", 50) . "\n";

    if (empty($errors)) {
        echo "✅ RESTAURAVIMAS SĖKMINGAS!\n";
        echo "\nSėkmingai atlikta:\n";
        foreach ($success as $s) {
            echo "  ✓ $s\n";
        }
    } else {
        echo "⚠️ RESTAURAVIMAS BAIGTAS SU KLAIDOMIS\n";
        echo "\nKlaidos:\n";
        foreach ($errors as $e) {
            echo "  ✗ $e\n";
        }
    }

    echo '</pre>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Kiti veiksmai:</h2>';
    echo '<ol>';
    echo '<li>✅ Patikrink puslapį: <a href="https://tossee.com/" target="_blank">https://tossee.com/</a></li>';
    echo '<li>✅ Patikrink wp-admin: <a href="https://tossee.com/wp-admin/" target="_blank">wp-admin</a></li>';
    echo '<li>⚠️ <strong>IŠTRINK ŠĮ FAILĄ!</strong> - restore-now.php</li>';
    echo '</ol>';
    echo '<form method="get"><button type="submit" name="action" value="delete" class="danger">🗑️ Ištrinti restore-now.php</button></form>';
    echo '</div>';

} elseif ($action === 'delete') {
    // Delete this script
    echo '<div class="box">';
    echo '<h2>🗑️ Trinamas restore-now.php...</h2>';

    if (unlink(__FILE__)) {
        echo '<p class="success">✅ Failas sėkmingai ištrintas!</p>';
        echo '<p>Šis puslapis dabar nebeprieinamas.</p>';
    } else {
        echo '<p class="error">❌ Nepavyko ištrinti failo automatiškai.</p>';
        echo '<p>Ištrink rankiniu būdu per File Manager arba SSH:</p>';
        echo '<pre>rm ' . __FILE__ . '</pre>';
    }
    echo '</div>';

} elseif ($action === 'check') {
    // Just check status
    echo '<div class="box">';
    echo '<h2>🔍 Būsenos patikrinimas</h2>';
    echo '<pre>';

    echo "WP Content kelias: $wpContentPath\n\n";

    echo "Plugins:\n";
    if (is_dir($pluginsActive)) {
        echo "  ✅ plugins/ - EGZISTUOJA (aktyvus)\n";
        $count = count(glob($pluginsActive . '/*'));
        echo "     Pluginų kiekis: $count\n";
    } else {
        echo "  ❌ plugins/ - NERASTAS\n";
    }

    if (is_dir($pluginsDisabled)) {
        echo "  ⚠️  plugins.DISABLED/ - EGZISTUOJA (išjungti)\n";
        $count = count(glob($pluginsDisabled . '/*'));
        echo "     Pluginų kiekis: $count\n";
    }

    echo "\nTemos:\n";
    if (is_dir($themeActive)) {
        echo "  ✅ astra/ - EGZISTUOJA (aktyvi)\n";
    } else {
        echo "  ❌ astra/ - NERASTAS\n";
    }

    if (is_dir($themeDisabled)) {
        echo "  ⚠️  astra.DISABLED/ - EGZISTUOJA (išjungta)\n";
    }

    echo '</pre>';
    echo '<form method="get"><button type="submit">← Grįžti</button></form>';
    echo '</div>';
}

?>

    <div class="box" style="background: #fff3cd; border-left: 4px solid #f39c12;">
        <h3>⚠️ Svarbu:</h3>
        <ul>
            <li>Po sėkmingo restauravimo <strong>ištrink šį failą</strong></li>
            <li>Jis skirtas tik vienkartiniam naudojimui</li>
            <li>Saugumo sumetimais nepalik jo serveryje</li>
        </ul>
    </div>

    <div class="box" style="font-size: 12px; color: #7f8c8d;">
        <p>Script location: <?php echo __FILE__; ?></p>
        <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>
