<?php
/**
 * SWITCH TO ASTRA THEME
 * Changes active WordPress theme from astra-child to astra
 *
 * Upload to: /home/u234011694/domains/tossee.com/public_html/
 * Run: https://tossee.com/switch-to-astra.php
 * Delete after use
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Switch to Astra Theme</title>
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
    <h1>🎨 Pakeisti Temą į Astra</h1>

<?php

$action = $_GET['action'] ?? '';

if ($action === '') {
    // Show current status
    $currentTheme = get_option('template');
    $currentStylesheet = get_option('stylesheet');

    echo '<div class="box">';
    echo '<h2>Dabartinė tema:</h2>';
    echo '<pre>';
    echo "Template (parent): $currentTheme\n";
    echo "Stylesheet (child): $currentStylesheet\n";
    echo '</pre>';

    if ($currentStylesheet === 'astra-child') {
        echo '<p class="warning">⚠️ Aktyvi tema: astra-child (probleminė!)</p>';
    } elseif ($currentTheme === 'astra' && $currentStylesheet === 'astra') {
        echo '<p class="success">✅ Aktyvi tema: astra (gerai!)</p>';
    }
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Prieinamos temos:</h2>';
    echo '<ul>';

    $themes = wp_get_themes();
    foreach ($themes as $theme_slug => $theme) {
        echo '<li>';
        echo '<strong>' . esc_html($theme->get('Name')) . '</strong> ';
        echo '(' . esc_html($theme_slug) . ')';
        if ($theme_slug === $currentStylesheet) {
            echo ' <span class="warning">← AKTYVI</span>';
        }
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Veiksmai:</h2>';
    echo '<form method="get">';
    echo '<button type="submit" name="action" value="switch">🎨 PAKEISTI Į ASTRA</button>';
    echo '</form>';
    echo '<p><small>Pakeisti aktyvią temą į "astra" (be child)</small></p>';
    echo '</div>';

} elseif ($action === 'switch') {
    // Switch to Astra theme
    echo '<div class="box">';
    echo '<h2>🔄 Keičiama tema...</h2>';
    echo '<pre>';

    // Check if astra theme exists
    $astraTheme = wp_get_theme('astra');

    if (!$astraTheme->exists()) {
        echo "❌ KLAIDA: Astra tema nerasta!\n";
        echo "\nPatikrink ar egzistuoja:\n";
        echo "wp-content/themes/astra/\n";
        exit;
    }

    echo "✓ Astra tema rasta: " . $astraTheme->get('Name') . "\n";
    echo "  Version: " . $astraTheme->get('Version') . "\n\n";

    // Switch theme
    echo "Keičiama tema į 'astra'...\n";

    // Set both template and stylesheet to astra
    update_option('template', 'astra');
    update_option('stylesheet', 'astra');

    // Verify
    $newTemplate = get_option('template');
    $newStylesheet = get_option('stylesheet');

    echo "\nPatikrinimas:\n";
    echo "  Template: $newTemplate\n";
    echo "  Stylesheet: $newStylesheet\n\n";

    if ($newTemplate === 'astra' && $newStylesheet === 'astra') {
        echo "✅ SĖKMINGAI PAKEISTA!\n";
        echo "\nAktyvi tema: Astra (be child)\n";
    } else {
        echo "❌ Kažkas nepavyko...\n";
    }

    echo '</pre>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>✅ Dabar:</h2>';
    echo '<ol>';
    echo '<li>Atidaryti: <a href="https://tossee.com/" target="_blank">https://tossee.com/</a></li>';
    echo '<li>Puslapis turėtų veikti be "astra-child" klaidos!</li>';
    echo '<li>Prisijungti į wp-admin: <a href="https://tossee.com/wp-admin/" target="_blank">wp-admin</a></li>';
    echo '<li><strong>Ištrink šį failą:</strong> switch-to-astra.php</li>';
    echo '</ol>';
    echo '</div>';

} elseif ($action === 'check') {
    // Just show database values
    global $wpdb;

    echo '<div class="box">';
    echo '<h2>🔍 Duomenų bazės tikrinimas</h2>';
    echo '<pre>';

    $template = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'template'");
    $stylesheet = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'stylesheet'");

    echo "Duomenų bazėje:\n";
    echo "  template = " . ($template ?: 'NULL') . "\n";
    echo "  stylesheet = " . ($stylesheet ?: 'NULL') . "\n";

    echo '</pre>';
    echo '<form method="get"><button type="submit">← Grįžti</button></form>';
    echo '</div>';
}

?>

    <div class="box" style="background: #fff3cd; border-left: 4px solid #f39c12;">
        <h3>ℹ️  Kas bus padaryta?</h3>
        <p>Šis skriptas pakeičia WordPress nustatyme aktyvią temą:</p>
        <ul>
            <li>Iš: <code>astra-child</code> (neegzistuoja)</li>
            <li>Į: <code>astra</code> (veikia)</li>
        </ul>
        <p>Po to puslapis veiks normaliai, be child temos.</p>
    </div>

    <div class="box" style="font-size: 12px; color: #7f8c8d;">
        <p>Script: <?php echo __FILE__; ?></p>
        <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>
