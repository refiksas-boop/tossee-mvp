<?php
/**
 * DISABLE ALL WORDPRESS PLUGINS
 * Deactivates all plugins via database
 *
 * Upload to: /home/u234011694/domains/tossee.com/public_html/
 * Run: https://tossee.com/disable-all-plugins.php
 * Delete after use
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Disable All Plugins</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .box { background: #f0f0f0; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background: #c82333; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
    <h1>🔌 Disable All WordPress Plugins</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'disable') {
    echo '<div class="box">';

    // Get currently active plugins
    $active_plugins = get_option('active_plugins');

    if (!empty($active_plugins)) {
        // Disable all plugins
        update_option('active_plugins', array());

        echo '<div class="box success">';
        echo '<h2>✅ Success!</h2>';
        echo '<p><strong>' . count($active_plugins) . '</strong> plugins have been deactivated.</p>';
        echo '<p>Disabled plugins:</p>';
        echo '<ul>';
        foreach ($active_plugins as $plugin) {
            echo '<li>' . $plugin . '</li>';
        }
        echo '</ul>';
        echo '<p><a href="/">Visit Homepage</a></p>';
        echo '</div>';
    } else {
        echo '<div class="box error">';
        echo '<h2>ℹ️ No Active Plugins</h2>';
        echo '<p>There are no active plugins to disable.</p>';
        echo '</div>';
    }

    echo '</div>';
} else {
    // Show current status
    $active_plugins = get_option('active_plugins');

    echo '<div class="box">';
    echo '<h2>Currently Active Plugins:</h2>';

    if (!empty($active_plugins)) {
        echo '<p><strong>Total: ' . count($active_plugins) . '</strong></p>';
        echo '<ul>';
        foreach ($active_plugins as $plugin) {
            echo '<li>' . $plugin . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No active plugins.</p>';
    }
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Action:</h2>';
    echo '<p>This will deactivate ALL WordPress plugins by clearing the active_plugins option in the database.</p>';
    echo '<form method="get">';
    echo '<input type="hidden" name="action" value="disable">';
    echo '<button type="submit">🔌 DISABLE ALL PLUGINS</button>';
    echo '</form>';
    echo '</div>';
}
?>

    <div class="box">
        <h3>⚠️ After deactivation:</h3>
        <ol>
            <li>Visit <a href="/">tossee.com</a> - should work!</li>
            <li>If it works, login to <a href="/wp-admin/">wp-admin</a></li>
            <li>Go to Plugins and re-enable plugins one by one</li>
            <li><strong>Delete this file!</strong></li>
        </ol>
    </div>

</body>
</html>
