<?php
/**
 * Tossee Setup Checker
 * Validates configuration and environment
 *
 * Usage: php check-setup.php
 */

define('TOSSEE_API', true);
require_once __DIR__ . '/config.php';

echo "=== Tossee Setup Checker ===\n\n";

$checks = [];
$errors = 0;
$warnings = 0;

// 1. Check PHP version
echo "[1/10] Checking PHP version... ";
if (version_compare(PHP_VERSION, '7.4', '>=')) {
    echo "✅ " . PHP_VERSION . "\n";
    $checks['php_version'] = true;
} else {
    echo "❌ PHP 7.4+ required (found " . PHP_VERSION . ")\n";
    $checks['php_version'] = false;
    $errors++;
}

// 2. Check Stripe library
echo "[2/10] Checking Stripe library... ";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Found\n";
    $checks['stripe_vendor'] = true;
} else {
    echo "❌ Missing\n";
    echo "         Run: cd " . __DIR__ . " && composer require stripe/stripe-php\n";
    $checks['stripe_vendor'] = false;
    $errors++;
}

// 3. Check database configuration
echo "[3/10] Checking database config... ";
if (DB_NAME === 'u234011694_tossee' || DB_NAME === 'your_db_name') {
    echo "⚠️  Default value - needs updating\n";
    $checks['db_config'] = false;
    $warnings++;
} else {
    echo "✅ Configured\n";
    $checks['db_config'] = true;
}

// 4. Test database connection
echo "[4/10] Testing database connection... ";
try {
    $pdo = getDB();
    $pdo->query('SELECT 1');
    echo "✅ Connected\n";
    $checks['db_connection'] = true;
} catch (Exception $e) {
    echo "❌ Failed: " . $e->getMessage() . "\n";
    $checks['db_connection'] = false;
    $errors++;
}

// 5. Check if table exists
echo "[5/10] Checking wp_tossee_users table... ";
if ($checks['db_connection']) {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("SHOW TABLES LIKE '" . DB_TABLE_PREFIX . "tossee_users'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Exists\n";
            $checks['table_exists'] = true;
        } else {
            echo "❌ Not found\n";
            echo "         Run database-setup.php or migrate-database.php\n";
            $checks['table_exists'] = false;
            $errors++;
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        $checks['table_exists'] = false;
        $errors++;
    }
} else {
    echo "⏭  Skipped (DB not connected)\n";
    $checks['table_exists'] = false;
}

// 6. Check payment columns
echo "[6/10] Checking payment columns... ";
if ($checks['table_exists']) {
    try {
        $pdo = getDB();
        $table = DB_TABLE_PREFIX . 'tossee_users';
        $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);

        $required_columns = ['free_used_seconds', 'paid_seconds', 'unlimited_until', 'call_started_at'];
        $missing = array_diff($required_columns, $columns);

        if (empty($missing)) {
            echo "✅ All present\n";
            $checks['payment_columns'] = true;
        } else {
            echo "❌ Missing: " . implode(', ', $missing) . "\n";
            echo "         Run: php migrate-database.php\n";
            $checks['payment_columns'] = false;
            $errors++;
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        $checks['payment_columns'] = false;
        $errors++;
    }
} else {
    echo "⏭  Skipped (table missing)\n";
    $checks['payment_columns'] = false;
}

// 7. Check Stripe API key
echo "[7/10] Checking Stripe API key... ";
if (STRIPE_SECRET_KEY === 'sk_test_...' || empty(STRIPE_SECRET_KEY)) {
    echo "⚠️  Default/empty value - needs updating\n";
    $checks['stripe_key'] = false;
    $warnings++;
} else if (strpos(STRIPE_SECRET_KEY, 'sk_') === 0) {
    $mode = strpos(STRIPE_SECRET_KEY, 'sk_live_') === 0 ? 'LIVE' : 'TEST';
    echo "✅ Configured ($mode mode)\n";
    $checks['stripe_key'] = true;
} else {
    echo "❌ Invalid format\n";
    $checks['stripe_key'] = false;
    $errors++;
}

// 8. Check Stripe webhook secret
echo "[8/10] Checking webhook secret... ";
if (STRIPE_WEBHOOK_SECRET === 'whsec_...' || empty(STRIPE_WEBHOOK_SECRET)) {
    echo "⚠️  Default/empty value - needs updating\n";
    $checks['webhook_secret'] = false;
    $warnings++;
} else if (strpos(STRIPE_WEBHOOK_SECRET, 'whsec_') === 0) {
    echo "✅ Configured\n";
    $checks['webhook_secret'] = true;
} else {
    echo "❌ Invalid format\n";
    $checks['webhook_secret'] = false;
    $errors++;
}

// 9. Check .htaccess
echo "[9/10] Checking .htaccess... ";
if (file_exists(__DIR__ . '/.htaccess')) {
    $htaccess = file_get_contents(__DIR__ . '/.htaccess');
    if (strpos($htaccess, 'RewriteEngine Off') !== false) {
        echo "✅ Configured\n";
        $checks['htaccess'] = true;
    } else {
        echo "⚠️  Missing 'RewriteEngine Off'\n";
        $checks['htaccess'] = false;
        $warnings++;
    }
} else {
    echo "❌ Missing\n";
    $checks['htaccess'] = false;
    $errors++;
}

// 10. Check permissions
echo "[10/10] Checking file permissions... ";
if (is_writable(__DIR__)) {
    echo "✅ Writable\n";
    $checks['permissions'] = true;
} else {
    echo "⚠️  Directory not writable (error.log may fail)\n";
    $checks['permissions'] = false;
    $warnings++;
}

// Summary
echo "\n=== Summary ===\n";
echo "✅ Passed: " . count(array_filter($checks)) . "/" . count($checks) . "\n";
echo "❌ Errors: $errors\n";
echo "⚠️  Warnings: $warnings\n";

if ($errors === 0 && $warnings === 0) {
    echo "\n🎉 All checks passed! System ready for testing.\n";
    exit(0);
} else if ($errors === 0) {
    echo "\n⚠️  System functional but configuration incomplete.\n";
    echo "Update config.php with production values before going live.\n";
    exit(0);
} else {
    echo "\n❌ Critical errors found. Fix them before proceeding.\n";
    exit(1);
}
