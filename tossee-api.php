<?php
/**
 * Tossee REST API Endpoints
 * Handles all custom API endpoints for Tossee application
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register REST API endpoints
 */
add_action('rest_api_init', function () {
    // Report submission endpoint
    register_rest_route('tossee/v1', '/report', array(
        'methods' => 'POST',
        'callback' => 'tossee_handle_report_submission',
        'permission_callback' => '__return_true', // Allow anyone to submit reports
    ));

    // Get reports for admin (with authentication)
    register_rest_route('tossee/v1', '/reports', array(
        'methods' => 'GET',
        'callback' => 'tossee_get_reports',
        'permission_callback' => 'tossee_check_admin_permission',
    ));

    // Update report status (mark as reviewed)
    register_rest_route('tossee/v1', '/report/(?P<id>\d+)', array(
        'methods' => 'PATCH',
        'callback' => 'tossee_update_report_status',
        'permission_callback' => 'tossee_check_admin_permission',
    ));

    // Get user profile endpoint
    register_rest_route('tossee/v1', '/profile', array(
        'methods' => 'GET',
        'callback' => 'tossee_get_user_profile',
        'permission_callback' => '__return_true',
    ));
});

/**
 * Handle report submission
 */
function tossee_handle_report_submission($request) {
    global $wpdb;

    // Get request data
    $data = json_decode($request->get_body(), true);

    // Validate required fields
    if (empty($data['reporter_id']) || empty($data['reported_user_id']) || empty($data['report_reason'])) {
        return new WP_Error(
            'missing_fields',
            'Missing required fields',
            array('status' => 400)
        );
    }

    // Sanitize input
    $reporter_id = sanitize_text_field($data['reporter_id']);
    $reported_user_id = sanitize_text_field($data['reported_user_id']);
    $report_reason = sanitize_text_field($data['report_reason']);
    $additional_details = !empty($data['additional_details'])
        ? sanitize_textarea_field($data['additional_details'])
        : null;

    // Map reason codes to readable labels
    $reason_map = array(
        'harassment' => 'Harassment or offensive behavior',
        'nudity' => 'Nudity or sexual content',
        'spam' => 'Spam, scam, or advertising'
    );

    $report_reason_text = isset($reason_map[$report_reason])
        ? $reason_map[$report_reason]
        : $report_reason;

    // Insert report into database
    $table_name = $wpdb->prefix . 'tossee_reports';
    $result = $wpdb->insert(
        $table_name,
        array(
            'reporter_id' => $reporter_id,
            'reported_user_id' => $reported_user_id,
            'report_reason' => $report_reason_text,
            'additional_details' => $additional_details,
            'report_status' => 'pending',
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result === false) {
        return new WP_Error(
            'database_error',
            'Failed to save report',
            array('status' => 500)
        );
    }

    // Get the inserted report ID
    $report_id = $wpdb->insert_id;

    // Send notification to admin (optional - can be implemented later)
    tossee_send_report_notification($report_id, $reporter_id, $reported_user_id, $report_reason_text);

    return array(
        'success' => true,
        'message' => 'Report submitted successfully',
        'report_id' => $report_id
    );
}

/**
 * Get all reports (for admin panel)
 */
function tossee_get_reports($request) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'tossee_reports';
    $users_table = $wpdb->prefix . 'tossee_users';

    // Get filter parameters
    $status = $request->get_param('status');
    $page = $request->get_param('page') ?: 1;
    $per_page = $request->get_param('per_page') ?: 20;
    $offset = ($page - 1) * $per_page;

    // Build query
    $where = '';
    if ($status && in_array($status, array('pending', 'reviewed', 'resolved'))) {
        $where = $wpdb->prepare(" WHERE r.report_status = %s", $status);
    }

    // Get total count
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name r" . $where);

    // Get reports with user information
    $query = "
        SELECT
            r.*,
            reporter.username as reporter_username,
            reporter.email as reporter_email,
            reported.username as reported_username,
            reported.email as reported_email
        FROM $table_name r
        LEFT JOIN $users_table reporter ON r.reporter_id = reporter.tossee_id
        LEFT JOIN $users_table reported ON r.reported_user_id = reported.tossee_id
        $where
        ORDER BY r.created_at DESC
        LIMIT %d OFFSET %d
    ";

    $reports = $wpdb->get_results($wpdb->prepare($query, $per_page, $offset));

    return array(
        'success' => true,
        'reports' => $reports,
        'total' => (int)$total,
        'page' => (int)$page,
        'per_page' => (int)$per_page,
        'total_pages' => ceil($total / $per_page)
    );
}

/**
 * Update report status
 */
function tossee_update_report_status($request) {
    global $wpdb;

    $report_id = $request->get_param('id');
    $data = json_decode($request->get_body(), true);

    if (empty($data['status'])) {
        return new WP_Error(
            'missing_status',
            'Status field is required',
            array('status' => 400)
        );
    }

    $status = sanitize_text_field($data['status']);

    if (!in_array($status, array('pending', 'reviewed', 'resolved'))) {
        return new WP_Error(
            'invalid_status',
            'Invalid status value',
            array('status' => 400)
        );
    }

    $table_name = $wpdb->prefix . 'tossee_reports';
    $current_user_id = get_current_user_id();

    $result = $wpdb->update(
        $table_name,
        array(
            'report_status' => $status,
            'reviewed_at' => current_time('mysql'),
            'reviewed_by' => $current_user_id
        ),
        array('id' => $report_id),
        array('%s', '%s', '%d'),
        array('%d')
    );

    if ($result === false) {
        return new WP_Error(
            'database_error',
            'Failed to update report',
            array('status' => 500)
        );
    }

    return array(
        'success' => true,
        'message' => 'Report status updated successfully'
    );
}

/**
 * Get user profile
 */
function tossee_get_user_profile($request) {
    global $wpdb;

    // Start session if not already started
    if (!session_id()) {
        session_start();
    }

    // Get user ID from session or URL parameter
    $tossee_id = isset($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null;

    if (!$tossee_id) {
        // Try to get from URL parameter
        $tossee_id = $request->get_param('uid');
    }

    if (!$tossee_id) {
        return new WP_Error(
            'not_authenticated',
            'User not authenticated',
            array('status' => 401)
        );
    }

    $table_name = $wpdb->prefix . 'tossee_users';
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT tossee_id, username, email, dob, photo, created_at FROM $table_name WHERE tossee_id = %s",
        $tossee_id
    ));

    if (!$user) {
        return new WP_Error(
            'user_not_found',
            'User not found',
            array('status' => 404)
        );
    }

    return array(
        'success' => true,
        'user' => $user
    );
}

/**
 * Check if user has admin permission
 */
function tossee_check_admin_permission() {
    return current_user_can('manage_options');
}

/**
 * Send notification to admin about new report
 */
function tossee_send_report_notification($report_id, $reporter_id, $reported_user_id, $reason) {
    // This is a placeholder for notification functionality
    // You can implement email notifications, push notifications, or in-app notifications here

    // Example: Send email to admin
    $admin_email = get_option('admin_email');
    $subject = 'New Report Submitted on Tossee';
    $message = sprintf(
        "A new report has been submitted.\n\nReport ID: %d\nReporter: %s\nReported User: %s\nReason: %s\n\nPlease review this report in the admin panel.",
        $report_id,
        $reporter_id,
        $reported_user_id,
        $reason
    );

    // Uncomment to enable email notifications
    // wp_mail($admin_email, $subject, $message);

    // Store notification in database (for in-app notification center)
    tossee_create_notification($report_id, $reporter_id, $reported_user_id, $reason);
}

/**
 * Create in-app notification for new report
 */
function tossee_create_notification($report_id, $reporter_id, $reported_user_id, $reason) {
    global $wpdb;

    $notifications_table = $wpdb->prefix . 'tossee_notifications';

    // Check if notifications table exists, if not create it
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$notifications_table'") == $notifications_table;

    if (!$table_exists) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $notifications_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            notification_type VARCHAR(50) NOT NULL,
            reference_id BIGINT(20) UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY notification_type (notification_type),
            KEY is_read (is_read)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    // Insert notification
    $message = sprintf(
        'New report submitted: %s reported user %s for "%s"',
        $reporter_id,
        $reported_user_id,
        $reason
    );

    $wpdb->insert(
        $notifications_table,
        array(
            'notification_type' => 'new_report',
            'reference_id' => $report_id,
            'message' => $message,
            'is_read' => 0,
            'created_at' => current_time('mysql')
        ),
        array('%s', '%d', '%s', '%d', '%s')
    );
}

/**
 * Get unread notifications count
 */
function tossee_get_unread_notifications_count() {
    global $wpdb;
    $notifications_table = $wpdb->prefix . 'tossee_notifications';

    $count = $wpdb->get_var("SELECT COUNT(*) FROM $notifications_table WHERE is_read = 0");

    return (int)$count;
}

/**
 * Get notifications for admin
 */
add_action('rest_api_init', function () {
    register_rest_route('tossee/v1', '/notifications', array(
        'methods' => 'GET',
        'callback' => 'tossee_get_notifications',
        'permission_callback' => 'tossee_check_admin_permission',
    ));

    register_rest_route('tossee/v1', '/notifications/mark-read/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'tossee_mark_notification_read',
        'permission_callback' => 'tossee_check_admin_permission',
    ));
});

function tossee_get_notifications($request) {
    global $wpdb;
    $notifications_table = $wpdb->prefix . 'tossee_notifications';

    $limit = $request->get_param('limit') ?: 50;
    $offset = $request->get_param('offset') ?: 0;

    $notifications = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $notifications_table ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $limit,
        $offset
    ));

    $unread_count = tossee_get_unread_notifications_count();

    return array(
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    );
}

function tossee_mark_notification_read($request) {
    global $wpdb;
    $notifications_table = $wpdb->prefix . 'tossee_notifications';

    $notification_id = $request->get_param('id');

    $wpdb->update(
        $notifications_table,
        array('is_read' => 1),
        array('id' => $notification_id),
        array('%d'),
        array('%d')
    );

    return array(
        'success' => true,
        'message' => 'Notification marked as read'
    );
}
