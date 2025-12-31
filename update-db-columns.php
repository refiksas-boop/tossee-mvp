<?php
/**
 * Tossee DB Update Script
 * Prideda trūkstamus stulpelius prie wp_tossee_users lentelės
 *
 * NAUDOJIMAS:
 * Įkelkite šį failą į WordPress šakninį katalogą ir paleiskite per naršyklę vieną kartą:
 * https://jūsų-domenas.com/update-db-columns.php
 */

// WordPress load
require_once 'wp-load.php';

// Security check - tik admin
if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Access denied. You must be logged in as administrator.' );
}

global $wpdb;
$table_name = $wpdb->prefix . 'tossee_users';

echo '<h1>Tossee DB Column Update</h1>';
echo '<p>Updating table: <strong>' . esc_html( $table_name ) . '</strong></p>';

// Patikrinam ar lentelė egzistuoja
$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );

if ( ! $table_exists ) {
    echo '<p style="color:red;">ERROR: Table does not exist!</p>';
    die();
}

echo '<h2>Adding missing columns...</h2>';

$columns_to_add = [
    'first_name' => "VARCHAR(60) DEFAULT '' NOT NULL",
    'last_name'  => "VARCHAR(60) DEFAULT '' NOT NULL",
    'gender'     => "VARCHAR(10) DEFAULT '' NOT NULL",
    'country'    => "VARCHAR(80) DEFAULT '' NOT NULL",
    'state'      => "VARCHAR(80) DEFAULT '' NOT NULL",
    'city'       => "VARCHAR(80) DEFAULT '' NOT NULL",
    'about'      => "TEXT NULL",
];

foreach ( $columns_to_add as $column => $definition ) {
    // Tikrinam ar stulpelis jau egzistuoja
    $column_exists = $wpdb->get_results(
        $wpdb->prepare( "SHOW COLUMNS FROM `$table_name` LIKE %s", $column )
    );

    if ( empty( $column_exists ) ) {
        // Pridedame stulpelį
        $sql = "ALTER TABLE `$table_name` ADD COLUMN `$column` $definition";
        $result = $wpdb->query( $sql );

        if ( $result === false ) {
            echo '<p style="color:red;">❌ Failed to add column: ' . esc_html( $column ) . '</p>';
            echo '<p>Error: ' . esc_html( $wpdb->last_error ) . '</p>';
        } else {
            echo '<p style="color:green;">✅ Added column: ' . esc_html( $column ) . '</p>';
        }
    } else {
        echo '<p style="color:blue;">ℹ️ Column already exists: ' . esc_html( $column ) . '</p>';
    }
}

echo '<h2>Current table structure:</h2>';
$columns = $wpdb->get_results( "SHOW COLUMNS FROM `$table_name`" );
echo '<table border="1" cellpadding="5" cellspacing="0">';
echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>';
foreach ( $columns as $col ) {
    echo '<tr>';
    echo '<td>' . esc_html( $col->Field ) . '</td>';
    echo '<td>' . esc_html( $col->Type ) . '</td>';
    echo '<td>' . esc_html( $col->Null ) . '</td>';
    echo '<td>' . esc_html( $col->Default ) . '</td>';
    echo '</tr>';
}
echo '</table>';

echo '<h2 style="color:green;">✅ Update Complete!</h2>';
echo '<p><strong>IMPORTANT:</strong> Please delete this file (update-db-columns.php) after use for security reasons.</p>';
