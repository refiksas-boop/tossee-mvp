<?php
/**
 * Fix Astra Theme Redirect Issue
 *
 * This script removes the forced redirect code from Astra theme's functions.php
 * that was causing all non-logged-in users to be redirected to /login
 *
 * Upload to: /home/u234011694/domains/tossee.com/public_html/
 * Run via: https://tossee.com/fix-astra-theme.php
 * Delete after use for security
 */

// Path to Astra theme
$themePath = __DIR__ . '/wp-content/themes/astra.DISABLED/functions.php';
$newThemePath = __DIR__ . '/wp-content/themes/astra/functions.php';

echo "<h1>Astra Theme Redirect Fix</h1>\n";
echo "<pre>\n";

// Check if disabled theme exists
if (!file_exists($themePath)) {
    echo "❌ Error: astra.DISABLED/functions.php not found\n";
    echo "Looking in: $themePath\n";
    exit;
}

// Read the file
$content = file_get_contents($themePath);
if ($content === false) {
    echo "❌ Error: Could not read functions.php\n";
    exit;
}

echo "✅ Original file read successfully\n";
echo "File size: " . strlen($content) . " bytes\n\n";

// Backup original
$backupPath = $themePath . '.backup.' . date('Y-m-d-His');
file_put_contents($backupPath, $content);
echo "✅ Backup created: $backupPath\n\n";

// Pattern to find and remove the redirect code
// Looking for the block that forces redirect to login
$patterns = [
    // Pattern 1: Full redirect block with wp_clear_auth_cookie()
    '/wp_clear_auth_cookie\(\);[\s\S]*?wp_redirect\([\'"]https:\/\/tossee\.com\/login[\'"]\);[\s\S]*?exit;/i',

    // Pattern 2: template_redirect hook forcing login
    '/add_action\(\s*[\'"]template_redirect[\'"]\s*,\s*function\s*\(\)\s*{[\s\S]*?wp_redirect\([\'"]https:\/\/tossee\.com\/login[\'"]\);[\s\S]*?}\s*\);/i',

    // Pattern 3: Any redirect to /login
    '/if\s*\(!\s*is_user_logged_in\(\)\s*\)[\s\S]{0,100}wp_redirect\([\'"]https:\/\/tossee\.com\/login[\'"]\);[\s\S]{0,50}exit;/i'
];

$fixed = $content;
$removedCount = 0;

foreach ($patterns as $i => $pattern) {
    $before = $fixed;
    $fixed = preg_replace($pattern, '// Redirect code removed by fix script', $fixed);
    if ($before !== $fixed) {
        $removedCount++;
        echo "✅ Removed redirect pattern #" . ($i + 1) . "\n";
    }
}

if ($removedCount === 0) {
    echo "⚠️  No redirect patterns found - manual check needed\n";
    echo "\nSearching for common redirect keywords:\n";

    if (stripos($content, 'wp_redirect') !== false) {
        echo "  - Found 'wp_redirect'\n";
    }
    if (stripos($content, 'template_redirect') !== false) {
        echo "  - Found 'template_redirect'\n";
    }
    if (stripos($content, '/login') !== false) {
        echo "  - Found '/login'\n";
    }
} else {
    echo "\n✅ Total patterns removed: $removedCount\n";
}

// Create the astra directory if it doesn't exist
$astraDir = __DIR__ . '/wp-content/themes/astra';
if (!is_dir($astraDir)) {
    mkdir($astraDir, 0755, true);
    echo "\n✅ Created directory: $astraDir\n";
}

// Write fixed version
if (file_put_contents($newThemePath, $fixed)) {
    echo "✅ Fixed functions.php written to: $newThemePath\n";
    echo "\nFile size: " . strlen($fixed) . " bytes\n";
    echo "Bytes removed: " . (strlen($content) - strlen($fixed)) . "\n";
} else {
    echo "❌ Error: Could not write fixed file\n";
    exit;
}

// Copy other theme files from disabled to active
echo "\n--- Copying other theme files ---\n";
$disabledDir = __DIR__ . '/wp-content/themes/astra.DISABLED';
$enabledDir = __DIR__ . '/wp-content/themes/astra';

if (is_dir($disabledDir)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($disabledDir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $copiedCount = 0;
    foreach ($files as $file) {
        if ($file->isDir()) continue;

        $filePath = $file->getPathname();
        // Skip functions.php (we already fixed it)
        if (basename($filePath) === 'functions.php') continue;

        $relativePath = str_replace($disabledDir, '', $filePath);
        $newPath = $enabledDir . $relativePath;

        // Create directory if needed
        $newDir = dirname($newPath);
        if (!is_dir($newDir)) {
            mkdir($newDir, 0755, true);
        }

        // Copy file
        if (copy($filePath, $newPath)) {
            $copiedCount++;
        }
    }
    echo "✅ Copied $copiedCount theme files\n";
}

echo "\n=== Fix Complete ===\n";
echo "\nNext steps:\n";
echo "1. Activate Astra theme in wp-admin\n";
echo "2. Test https://tossee.com/ - should load without redirect\n";
echo "3. Delete this fix script for security\n";
echo "\n</pre>";
