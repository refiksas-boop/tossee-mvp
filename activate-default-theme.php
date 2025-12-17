<?php
/**
 * ACTIVATE DEFAULT WORDPRESS THEME
 * Changes theme to twentytwentyfive (default WP theme)
 *
 * Upload to: /home/u234011694/domains/tossee.com/public_html/
 * Run: https://tossee.com/activate-default-theme.php
 * Delete after use
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Activate Default Theme</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .box { background: #f0f0f0; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005177; }
    </style>
</head>
<body>
    <h1>🎨 Activate Default WordPress Theme</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'activate') {
    echo '<div class="box">';

    // Get available themes
    $themes = wp_get_themes();
    $default_theme = null;

    // Try to find a default WP theme
    $preferred_themes = ['twentytwentyfive', 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo'];

    foreach ($preferred_themes as $theme_slug) {
        if (isset($themes[$theme_slug])) {
            $default_theme = $theme_slug;
            break;
        }
    }

    if ($default_theme) {
        // Switch theme
        update_option('template', $default_theme);
        update_option('stylesheet', $default_theme);

        echo '<div class="box success">';
        echo '<h2>✅ Success!</h2>';
        echo '<p>Theme changed to: <strong>' . $default_theme . '</strong></p>';
        echo '<p><a href="/">Visit Homepage</a></p>';
        echo '</div>';
    } else {
        echo '<div class="box error">';
        echo '<h2>❌ Error</h2>';
        echo '<p>No default WordPress theme found!</p>';
        echo '</div>';
    }

    echo '</div>';
} else {
    // Show current status
    $current_theme = wp_get_theme();

    echo '<div class="box">';
    echo '<h2>Current Theme:</h2>';
    echo '<p><strong>' . $current_theme->get('Name') . '</strong></p>';
    echo '<p>Template: ' . get_option('template') . '</p>';
    echo '<p>Stylesheet: ' . get_option('stylesheet') . '</p>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Available Themes:</h2>';
    $themes = wp_get_themes();
    echo '<ul>';
    foreach ($themes as $theme) {
        echo '<li>' . $theme->get('Name') . ' (' . $theme->get_stylesheet() . ')</li>';
    }
    echo '</ul>';
    echo '</div>';

    echo '<div class="box">';
    echo '<h2>Action:</h2>';
    echo '<p>This will activate a default WordPress theme (twentytwentyfive or similar)</p>';
    echo '<form method="get">';
    echo '<input type="hidden" name="action" value="activate">';
    echo '<button type="submit">🎨 ACTIVATE DEFAULT THEME</button>';
    echo '</form>';
    echo '</div>';
}
?>

    <div class="box">
        <h3>⚠️ After activation:</h3>
        <ol>
            <li>Visit <a href="/">tossee.com</a> - should work!</li>
            <li>Login to <a href="/wp-admin/">wp-admin</a></li>
            <li>Go to Appearance → Themes</li>
            <li>Activate your preferred theme</li>
            <li><strong>Delete this file!</strong></li>
        </ol>
    </div>

</body>
</html>
