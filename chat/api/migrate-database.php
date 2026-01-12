<?php
/**
 * Tossee Database Migration Script
 * Adds payment fields to wp_tossee_users table
 *
 * Run this ONCE to add new columns to existing table
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/../../wp-load.php');

global $wpdb;
$table_name = $wpdb->prefix . 'tossee_users';

echo "=== Tossee Database Migration ===\n\n";

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

if (!$table_exists) {
    echo "❌ Table $table_name does not exist!\n";
    echo "Please run database-setup.php first to create the table.\n";
    exit(1);
}

echo "✅ Table $table_name exists\n\n";

// Define columns to add
$columns_to_add = [
    'free_used_seconds' => "INT(11) NOT NULL DEFAULT 0 COMMENT 'Free time used in seconds (max 1800)'",
    'paid_seconds' => "INT(11) NOT NULL DEFAULT 0 COMMENT 'Paid time remaining in seconds'",
    'unlimited_until' => "DATETIME NULL DEFAULT NULL COMMENT 'Unlimited access valid until this date'",
    'call_started_at' => "DATETIME NULL DEFAULT NULL COMMENT 'Current call start timestamp'"
];

$added = 0;
$skipped = 0;
$errors = 0;

foreach ($columns_to_add as $column_name => $column_definition) {
    // Check if column exists
    $column_exists = $wpdb->get_results(
        $wpdb->prepare(
            "SHOW COLUMNS FROM $table_name LIKE %s",
            $column_name
        )
    );

    if (!empty($column_exists)) {
        echo "⏭  Column '$column_name' already exists - skipping\n";
        $skipped++;
        continue;
    }

    // Add column
    $sql = "ALTER TABLE $table_name ADD COLUMN $column_name $column_definition";

    echo "➕ Adding column '$column_name'... ";

    $result = $wpdb->query($sql);

    if ($result === false) {
        echo "❌ FAILED\n";
        echo "   Error: " . $wpdb->last_error . "\n";
        $errors++;
    } else {
        echo "✅ SUCCESS\n";
        $added++;
    }
}

echo "\n=== Migration Summary ===\n";
echo "✅ Added: $added columns\n";
echo "⏭  Skipped: $skipped columns (already exist)\n";
echo "❌ Errors: $errors\n";

if ($errors === 0) {
    echo "\n✅ Migration completed successfully!\n";
    exit(0);
} else {
    echo "\n❌ Migration completed with errors.\n";
    exit(1);
}
