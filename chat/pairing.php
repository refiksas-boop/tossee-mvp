<?php
/**
 * Tossee Chat Pairing System
 * Handles matchmaking and WebRTC signaling for video chat
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create pairing tables on init
 */
add_action('init', 'tossee_create_pairing_tables');

function tossee_create_pairing_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Waiting room table
    $waiting_table = $wpdb->prefix . 'tossee_waiting';
    $sql1 = "CREATE TABLE IF NOT EXISTS $waiting_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        tossee_id VARCHAR(40) NOT NULL,
        entered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY tossee_id (tossee_id),
        KEY idx_entered_at (entered_at)
    ) $charset_collate;";

    // Active pairs table
    $pairs_table = $wpdb->prefix . 'tossee_pairs';
    $sql2 = "CREATE TABLE IF NOT EXISTS $pairs_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user1_id VARCHAR(40) NOT NULL,
        user2_id VARCHAR(40) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        initiator VARCHAR(40) NOT NULL,
        PRIMARY KEY (id),
        KEY idx_user1 (user1_id),
        KEY idx_user2 (user2_id),
        KEY idx_created_at (created_at)
    ) $charset_collate;";

    // Signals table for WebRTC
    $signals_table = $wpdb->prefix . 'tossee_signals';
    $sql3 = "CREATE TABLE IF NOT EXISTS $signals_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        from_user VARCHAR(40) NOT NULL,
        to_user VARCHAR(40) NOT NULL,
        signal LONGTEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        consumed TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id),
        KEY idx_to_user (to_user),
        KEY idx_consumed (consumed),
        KEY idx_created_at (created_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}

/**
 * Register pairing REST API endpoints
 */
add_action('rest_api_init', 'tossee_register_pairing_routes');

function tossee_register_pairing_routes() {
    // Find partner
    register_rest_route('tossee/v1', '/find-partner', array(
        'methods'  => 'POST',
        'callback' => 'tossee_api_find_partner',
        'permission_callback' => 'tossee_api_check_auth',
    ));

    // Send WebRTC signal
    register_rest_route('tossee/v1', '/signal', array(
        'methods'  => 'POST',
        'callback' => 'tossee_api_send_signal',
        'permission_callback' => 'tossee_api_check_auth',
    ));

    // Get WebRTC signal
    register_rest_route('tossee/v1', '/get-signal', array(
        'methods'  => 'GET',
        'callback' => 'tossee_api_get_signal',
        'permission_callback' => 'tossee_api_check_auth',
    ));

    // Disconnect
    register_rest_route('tossee/v1', '/disconnect', array(
        'methods'  => 'POST',
        'callback' => 'tossee_api_disconnect',
        'permission_callback' => 'tossee_api_check_auth',
    ));
}

/**
 * Find a partner for video chat
 */
function tossee_api_find_partner(WP_REST_Request $request) {
    global $wpdb;
    $waiting_table = $wpdb->prefix . 'tossee_waiting';
    $pairs_table = $wpdb->prefix . 'tossee_pairs';

    $current_user_id = tossee_get_current_user_id();

    if (!$current_user_id) {
        return new WP_Error('not_authenticated', 'Not authenticated', array('status' => 401));
    }

    // Check if user is already paired
    $existing_pair = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $pairs_table WHERE user1_id = %s OR user2_id = %s",
        $current_user_id,
        $current_user_id
    ));

    if ($existing_pair) {
        // Already paired, return partner info
        $partner_id = ($existing_pair->user1_id === $current_user_id)
            ? $existing_pair->user2_id
            : $existing_pair->user1_id;

        $partner = tossee_get_user_by_id($partner_id);
        $initiator = $existing_pair->initiator === $current_user_id;

        return rest_ensure_response(array(
            'found' => true,
            'partner' => array(
                'tossee_id' => $partner->tossee_id,
                'country' => $partner->country,
                'city' => $partner->city,
                'age' => tossee_calculate_age($partner->dob),
            ),
            'initiator' => $initiator,
        ));
    }

    // Look for someone else in waiting room
    $partner_row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $waiting_table WHERE tossee_id != %s ORDER BY entered_at ASC LIMIT 1",
        $current_user_id
    ));

    if ($partner_row) {
        // Found a partner!
        $partner_id = $partner_row->tossee_id;
        $partner = tossee_get_user_by_id($partner_id);

        // Create pair (current user is initiator)
        $wpdb->insert(
            $pairs_table,
            array(
                'user1_id' => $current_user_id,
                'user2_id' => $partner_id,
                'created_at' => current_time('mysql'),
                'initiator' => $current_user_id,
            ),
            array('%s', '%s', '%s', '%s')
        );

        // Remove both from waiting room
        $wpdb->delete($waiting_table, array('tossee_id' => $current_user_id), array('%s'));
        $wpdb->delete($waiting_table, array('tossee_id' => $partner_id), array('%s'));

        tossee_log("Paired users: {$current_user_id} <-> {$partner_id}", 'info');

        return rest_ensure_response(array(
            'found' => true,
            'partner' => array(
                'tossee_id' => $partner->tossee_id,
                'country' => $partner->country,
                'city' => $partner->city,
                'age' => tossee_calculate_age($partner->dob),
            ),
            'initiator' => true,
        ));
    } else {
        // No partner yet, add to waiting room
        $wpdb->replace(
            $waiting_table,
            array(
                'tossee_id' => $current_user_id,
                'entered_at' => current_time('mysql'),
            ),
            array('%s', '%s')
        );

        return rest_ensure_response(array(
            'found' => false,
            'message' => 'Waiting for partner...',
        ));
    }
}

/**
 * Send WebRTC signal to partner
 */
function tossee_api_send_signal(WP_REST_Request $request) {
    global $wpdb;
    $signals_table = $wpdb->prefix . 'tossee_signals';

    $current_user_id = tossee_get_current_user_id();
    $params = $request->get_json_params();

    if (empty($params['partner_id']) || empty($params['signal'])) {
        return new WP_Error('missing_params', 'Missing partner_id or signal', array('status' => 400));
    }

    $partner_id = sanitize_text_field($params['partner_id']);
    $signal = wp_json_encode($params['signal']);

    // Store signal
    $wpdb->insert(
        $signals_table,
        array(
            'from_user' => $current_user_id,
            'to_user' => $partner_id,
            'signal' => $signal,
            'created_at' => current_time('mysql'),
            'consumed' => 0,
        ),
        array('%s', '%s', '%s', '%s', '%d')
    );

    return rest_ensure_response(array('status' => 'ok'));
}

/**
 * Get WebRTC signal from partner
 */
function tossee_api_get_signal(WP_REST_Request $request) {
    global $wpdb;
    $signals_table = $wpdb->prefix . 'tossee_signals';

    $current_user_id = tossee_get_current_user_id();
    $partner_id = sanitize_text_field($request['partner_id']);

    // Get most recent unconsumed signal
    $signal_row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $signals_table
        WHERE from_user = %s AND to_user = %s AND consumed = 0
        ORDER BY created_at DESC LIMIT 1",
        $partner_id,
        $current_user_id
    ));

    if ($signal_row) {
        // Mark as consumed
        $wpdb->update(
            $signals_table,
            array('consumed' => 1),
            array('id' => $signal_row->id),
            array('%d'),
            array('%d')
        );

        return rest_ensure_response(array(
            'signal' => json_decode($signal_row->signal, true),
        ));
    }

    return rest_ensure_response(array('signal' => null));
}

/**
 * Disconnect from current partner
 */
function tossee_api_disconnect(WP_REST_Request $request) {
    global $wpdb;
    $pairs_table = $wpdb->prefix . 'tossee_pairs';
    $waiting_table = $wpdb->prefix . 'tossee_waiting';

    $current_user_id = tossee_get_current_user_id();

    // Remove from any active pair
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $pairs_table WHERE user1_id = %s OR user2_id = %s",
        $current_user_id,
        $current_user_id
    ));

    // Remove from waiting room
    $wpdb->delete($waiting_table, array('tossee_id' => $current_user_id), array('%s'));

    return rest_ensure_response(array('status' => 'ok'));
}

/**
 * Cleanup old waiting room entries (cron job)
 */
add_action('tossee_cleanup_waiting', 'tossee_cleanup_old_waiting');

function tossee_cleanup_old_waiting() {
    global $wpdb;
    $waiting_table = $wpdb->prefix . 'tossee_waiting';

    // Remove entries older than 5 minutes
    $wpdb->query(
        "DELETE FROM $waiting_table WHERE entered_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
    );
}

// Schedule cleanup if not already scheduled
if (!wp_next_scheduled('tossee_cleanup_waiting')) {
    wp_schedule_event(time(), 'hourly', 'tossee_cleanup_waiting');
}
