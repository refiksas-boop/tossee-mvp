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
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        UNIQUE KEY tossee_id (tossee_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('init', 'tossee_create_users_table');

/* ================================
   TOSSEE – CHAT REPORTS TABLE
================================ */
function tossee_create_reports_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tossee_reports';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        reporter_id VARCHAR(40) NOT NULL,
        reported_user_id VARCHAR(40) NOT NULL,
        report_reason VARCHAR(100) NOT NULL,
        additional_details TEXT NULL,
        report_status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME NULL,
        reviewed_by BIGINT(20) UNSIGNED NULL,
        PRIMARY KEY (id),
        KEY reporter_id (reporter_id),
        KEY reported_user_id (reported_user_id),
        KEY report_status (report_status)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('init', 'tossee_create_reports_table');