/* ================================
   TOSSEE – CUSTOM USERS TABLE
================================ */
function tossee_create_users_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_users';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        tossee_id VARCHAR(40) NOT NULL,
        username VARCHAR(60) NOT NULL,
        email VARCHAR(120) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        dob DATE NOT NULL,
        photo LONGTEXT NOT NULL,
        first_name VARCHAR(60) NULL,
        last_name VARCHAR(60) NULL,
        gender VARCHAR(20) NULL,
        country VARCHAR(120) NULL,
        city VARCHAR(120) NULL,
        hobbies TEXT NULL,
        about TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        UNIQUE KEY tossee_id (tossee_id),
        KEY username (username)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('init', 'tossee_create_users_table');