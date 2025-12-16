<?php
/**
 * FIX ASTRA-CHILD THEME ERROR
 * Creates missing astra-child theme directory and files
 *
 * Upload to: /home/u234011694/domains/tossee.com/public_html/
 * Run: https://tossee.com/fix-astra-child.php
 * Delete after use
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Astra-Child Theme</title>
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
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
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
        }
        button:hover { background: #2980b9; }
    </style>
</head>
<body>
    <h1>🔧 Fix Astra-Child Theme</h1>

<?php

$themesPath = __DIR__ . '/wp-content/themes';
$astraChildPath = $themesPath . '/astra-child';
$action = $_GET['action'] ?? '';

if ($action === '') {
    // Show current status
    echo '<div class="box">';
    echo '<h2>Dabartinė situacija:</h2>';
    echo '<ul>';

    if (is_dir($themesPath . '/astra')) {
        echo '<li class="success">✅ Astra tema egzistuoja</li>';
    } else {
        echo '<li class="error">❌ Astra tema NERASTA!</li>';
    }

    if (is_dir($astraChildPath)) {
        echo '<li class="success">✅ Astra-child tema egzistuoja</li>';
    } else {
        echo '<li class="error">❌ Astra-child tema NERASTA (problema!)</li>';
    }

    echo '</ul>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Veiksmas:</h2>';
    echo '<form method="get">';
    echo '<button type="submit" name="action" value="create">📁 Sukurti astra-child temą</button>';
    echo '</form>';
    echo '<p><small>Tai sukurs trūkstamus failus ir aplankus</small></p>';
    echo '</div>';

} elseif ($action === 'create') {
    echo '<div class="box">';
    echo '<h2>🔨 Kuriama astra-child tema...</h2>';
    echo '<pre>';

    // Create astra-child directory
    if (!is_dir($astraChildPath)) {
        if (mkdir($astraChildPath, 0755, true)) {
            echo "✅ Sukurtas aplankas: astra-child/\n";
        } else {
            echo "❌ KLAIDA: Nepavyko sukurti aplanko\n";
            exit;
        }
    } else {
        echo "ℹ️  astra-child/ aplankas jau egzistuoja\n";
    }

    // Create style.css
    $styleCss = <<<CSS
/*
Theme Name: Astra Child
Theme URI: https://tossee.com/
Description: Astra Child Theme for Tossee
Template: astra
Author: Tossee
Author URI: https://tossee.com/
Version: 1.0.0
Text Domain: astra-child
*/

/* Custom styles */

CSS;

    $styleFile = $astraChildPath . '/style.css';
    if (file_put_contents($styleFile, $styleCss)) {
        echo "✅ Sukurtas failas: style.css\n";
    } else {
        echo "❌ KLAIDA: Nepavyko sukurti style.css\n";
    }

    // Create functions.php
    $functionsPHP = <<<'PHP'
<?php
/**
 * Astra Child Theme Functions
 */

// Enqueue parent and child theme styles
function astra_child_enqueue_styles() {
    // Parent theme style
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');

    // Child theme style
    wp_enqueue_style('astra-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-parent-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

PHP;

    $functionsFile = $astraChildPath . '/functions.php';
    if (file_put_contents($functionsFile, $functionsPHP)) {
        echo "✅ Sukurtas failas: functions.php\n";
    } else {
        echo "❌ KLAIDA: Nepavyko sukurti functions.php\n";
    }

    // Set permissions
    chmod($astraChildPath, 0755);
    chmod($styleFile, 0644);
    chmod($functionsFile, 0644);
    echo "✅ Nustatytos teisės (755/644)\n";

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ ASTRA-CHILD TEMA SUKURTA!\n";
    echo "</pre>";
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>✅ Dabar:</h2>';
    echo '<ol>';
    echo '<li>Atidaryti: <a href="https://tossee.com/" target="_blank">https://tossee.com/</a></li>';
    echo '<li>Turėtų veikti be klaidų!</li>';
    echo '<li><strong>Ištrink šį failą:</strong> fix-astra-child.php</li>';
    echo '</ol>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h3>Sukurti failai:</h3>';
    echo '<ul>';
    echo '<li>wp-content/themes/astra-child/</li>';
    echo '<li>wp-content/themes/astra-child/style.css</li>';
    echo '<li>wp-content/themes/astra-child/functions.php</li>';
    echo '</ul>';
    echo '</div>';
}

?>

    <div class="box" style="background: #fff3cd; border-left: 4px solid #f39c12;">
        <h3>ℹ️  Kas yra Child Theme?</h3>
        <p>Child theme leidžia pritaikyti temą nekeičiant originalaus kodo. WordPress ieškojo "astra-child" temos, bet jos nebuvo, todėl metė klaidą.</p>
        <p>Šis skriptas sukuria minimalią astra-child temą, kuri veiks su Astra.</p>
    </div>

    <div class="box" style="font-size: 12px; color: #7f8c8d;">
        <p>Script: <?php echo __FILE__; ?></p>
        <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>
