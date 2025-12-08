<?php
/**
 * TOSSEE DEBUG SCRIPT
 * Upload šį failą į WordPress root ir paleiskite per naršyklę
 * URL: https://jūsų-domain.com/debug-tossee.php
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

header('Content-Type: text/html; charset=UTF-8');

echo '<h1>Tossee Debug Information</h1>';
echo '<style>body{font-family:monospace;background:#f5f5f5;padding:20px;}h2{color:#0073aa;border-bottom:2px solid #0073aa;padding-bottom:5px;}pre{background:#fff;padding:15px;border:1px solid #ddd;overflow:auto;}.success{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}</style>';

global $wpdb;
$table = $wpdb->prefix . 'tossee_users';

// 1. Check if plugin is active
echo '<h2>1. Plugin Status</h2>';
if (is_plugin_active('tossee-core/tossee-core.php')) {
    echo '<p class="success">✓ Plugin is ACTIVE</p>';
} else {
    echo '<p class="error">✗ Plugin is NOT ACTIVE</p>';
    echo '<p>Please activate the plugin first!</p>';
}

// 2. Check if table exists
echo '<h2>2. Database Table</h2>';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;

if ($table_exists) {
    echo '<p class="success">✓ Table exists: ' . $table . '</p>';

    // Show table structure
    $columns = $wpdb->get_results("DESCRIBE $table");
    echo '<h3>Table Structure:</h3>';
    echo '<pre>';
    foreach ($columns as $col) {
        echo $col->Field . ' | ' . $col->Type . ' | ' . $col->Null . ' | ' . $col->Key . "\n";
    }
    echo '</pre>';

    // Count users
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    echo '<p>Total users in table: <strong>' . $count . '</strong></p>';

    // Show recent users
    if ($count > 0) {
        echo '<h3>Recent Users:</h3>';
        $users = $wpdb->get_results("SELECT id, tossee_id, username, email, created_at FROM $table ORDER BY created_at DESC LIMIT 5");
        echo '<pre>';
        print_r($users);
        echo '</pre>';
    }
} else {
    echo '<p class="error">✗ Table does NOT exist: ' . $table . '</p>';
    echo '<p>Trying to create table...</p>';

    // Try to create table
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        tossee_id VARCHAR(40) NOT NULL,
        username VARCHAR(60) NOT NULL,
        email VARCHAR(120) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(60) DEFAULT '' NOT NULL,
        last_name VARCHAR(60) DEFAULT '' NOT NULL,
        gender VARCHAR(10) DEFAULT '' NOT NULL,
        country VARCHAR(80) DEFAULT '' NOT NULL,
        city VARCHAR(80) DEFAULT '' NOT NULL,
        dob DATE NOT NULL,
        photo LONGTEXT NOT NULL,
        is_blocked TINYINT(1) NOT NULL DEFAULT 0,
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        UNIQUE KEY tossee_id (tossee_id),
        KEY username (username)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Check again
    $table_exists_now = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    if ($table_exists_now) {
        echo '<p class="success">✓ Table created successfully!</p>';
    } else {
        echo '<p class="error">✗ Failed to create table. Error: ' . $wpdb->last_error . '</p>';
    }
}

// 3. Check PHP requirements
echo '<h2>3. PHP Requirements</h2>';
echo '<p>PHP Version: ' . PHP_VERSION . ' ' . (version_compare(PHP_VERSION, '7.4', '>=') ? '<span class="success">✓</span>' : '<span class="error">✗ Need 7.4+</span>') . '</p>';
echo '<p>PDO MySQL: ' . (extension_loaded('pdo_mysql') ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . '</p>';
echo '<p>GD Library: ' . (extension_loaded('gd') ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . '</p>';

// 4. Check POST limits
echo '<h2>4. POST/Upload Limits</h2>';
echo '<p>post_max_size: ' . ini_get('post_max_size') . '</p>';
echo '<p>upload_max_filesize: ' . ini_get('upload_max_filesize') . '</p>';
echo '<p>max_input_vars: ' . ini_get('max_input_vars') . '</p>';
echo '<p class="error">⚠ If post_max_size is less than 20M, large photos may fail!</p>';

// 5. Check admin-post.php
echo '<h2>5. Form Action URL</h2>';
$form_action = admin_url('admin-post.php');
echo '<p>Form should submit to: <code>' . $form_action . '</code></p>';

// 6. Test database insert
echo '<h2>6. Test Database Insert</h2>';
echo '<p>Testing if we can insert data...</p>';

$test_id = 'test_' . time();
$test_result = $wpdb->insert(
    $table,
    [
        'tossee_id' => $test_id,
        'username' => 'testuser_' . time(),
        'email' => 'test_' . time() . '@example.com',
        'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
        'first_name' => 'Test',
        'last_name' => 'User',
        'gender' => 'other',
        'country' => 'Test',
        'city' => 'Test',
        'dob' => '1990-01-01',
        'photo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        'created_at' => current_time('mysql'),
    ],
    ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s']
);

if ($test_result) {
    echo '<p class="success">✓ Test insert successful! Insert ID: ' . $wpdb->insert_id . '</p>';

    // Clean up test data
    $wpdb->delete($table, ['tossee_id' => $test_id], ['%s']);
    echo '<p>Test data cleaned up.</p>';
} else {
    echo '<p class="error">✗ Test insert FAILED!</p>';
    echo '<p>Error: ' . $wpdb->last_error . '</p>';
}

// 7. Check error log
echo '<h2>7. WordPress Debug</h2>';
echo '<p>WP_DEBUG: ' . (defined('WP_DEBUG') && WP_DEBUG ? '<span class="success">ON</span>' : '<span class="error">OFF</span>') . '</p>';
echo '<p>WP_DEBUG_LOG: ' . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '<span class="success">ON</span>' : '<span class="error">OFF</span>') . '</p>';

if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    $log_file = WP_CONTENT_DIR . '/debug.log';
    if (file_exists($log_file)) {
        echo '<p>Log file: ' . $log_file . '</p>';
        echo '<h3>Last 20 lines of debug.log (Tossee related):</h3>';
        $log_content = file_get_contents($log_file);
        $lines = explode("\n", $log_content);
        $tossee_lines = array_filter($lines, function($line) {
            return stripos($line, 'TOSSEE') !== false || stripos($line, 'tossee') !== false;
        });
        $last_lines = array_slice($tossee_lines, -20);
        echo '<pre style="max-height:300px;overflow:auto;">';
        echo htmlspecialchars(implode("\n", $last_lines));
        echo '</pre>';
    } else {
        echo '<p>No debug.log file found yet.</p>';
    }
} else {
    echo '<p class="error">Enable WP_DEBUG and WP_DEBUG_LOG in wp-config.php to see errors!</p>';
    echo '<pre>define(\'WP_DEBUG\', true);
define(\'WP_DEBUG_LOG\', true);
define(\'WP_DEBUG_DISPLAY\', false);</pre>';
}

// 8. Test form submission
echo '<h2>8. Test Registration Form</h2>';
echo '<p>Test the registration form below:</p>';

echo '<form method="post" action="' . admin_url('admin-post.php') . '" style="background:#fff;padding:20px;border:1px solid #ddd;">
    <input type="hidden" name="action" value="tossee_custom_register">

    <p><input type="text" name="user_login" placeholder="Username" required style="width:100%;padding:8px;"></p>
    <p><input type="email" name="user_email" placeholder="Email" required style="width:100%;padding:8px;"></p>
    <p><input type="password" name="user_pass" placeholder="Password" required style="width:100%;padding:8px;"></p>
    <p><input type="date" name="dob" value="1990-01-01" required style="width:100%;padding:8px;"></p>
    <p><input type="text" name="photo" value="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" required style="width:100%;padding:8px;"></p>

    <p><button type="submit" style="padding:10px 20px;background:#0073aa;color:#fff;border:none;cursor:pointer;">Test Register</button></p>
</form>';

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li>Make sure plugin is activated</li>';
echo '<li>Make sure table exists (check above)</li>';
echo '<li>Enable WP_DEBUG to see errors</li>';
echo '<li>Try the test form above</li>';
echo '<li>Check if user appears in Tossee Users admin panel</li>';
echo '</ol>';
