<?php
/* ================================
   TOSSEE – PROFILE UPDATE HANDLER
================================ */

// AUTO-MIGRATION: Prideda trūkstamus stulpelius prie lentelės
function tossee_ensure_profile_columns() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $columns_to_add = [
        'first_name' => "VARCHAR(60) DEFAULT '' NOT NULL",
        'last_name'  => "VARCHAR(60) DEFAULT '' NOT NULL",
        'gender'     => "VARCHAR(20) DEFAULT '' NOT NULL",
        'country'    => "VARCHAR(100) DEFAULT '' NOT NULL",
        'state'      => "VARCHAR(100) DEFAULT '' NOT NULL",
        'city'       => "VARCHAR(100) DEFAULT '' NOT NULL",
        'about'      => "TEXT NULL",
    ];

    foreach ( $columns_to_add as $column => $definition ) {
        $column_exists = $wpdb->get_results(
            $wpdb->prepare( "SHOW COLUMNS FROM `$table` LIKE %s", $column )
        );

        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE `$table` ADD COLUMN `$column` $definition" );
        }
    }
}

add_action('admin_post_nopriv_tossee_update_profile', 'tossee_update_profile_handler');
add_action('admin_post_tossee_update_profile',        'tossee_update_profile_handler');

function tossee_update_profile_handler() {

    // Visada JSON
    header('Content-Type: application/json; charset=utf-8');

    // 1. AUTH – naudojam plugin'o funkciją arba fallback
    if (function_exists('tossee_get_current_user_id')) {
        $tossee_id = tossee_get_current_user_id();
    } else {
        // Fallback - session tikrinimas
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tossee_id = !empty($_SESSION['tossee_uid']) ? $_SESSION['tossee_uid'] : (!empty($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null);
    }

    if ( ! $tossee_id ) {
        echo json_encode([
            'error' => 'no_auth',
            'message' => 'User not authenticated'
        ]);
        exit;
    }

    // 2. Nuskaityti JSON body
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if ( ! is_array($data) ) {
        echo json_encode([
            'error' => 'invalid_payload',
            'raw'   => $raw
        ]);
        exit;
    }

    // 3. LEISTINI LAUKAI (AIŠKIAI)
    $allowed_fields = [
        'first_name',
        'last_name',
        'gender',
        'country',
        'state',
        'city',
        'about'
    ];

    $update = [];

    foreach ( $allowed_fields as $field ) {
        if ( array_key_exists($field, $data) ) {
            $update[$field] = ($field === 'about')
                ? sanitize_textarea_field($data[$field])
                : sanitize_text_field($data[$field]);
        }
    }

    // Profilio foto – tik jei tikrai base64 image
    if (
        ! empty($data['photo']) &&
        is_string($data['photo']) &&
        strpos($data['photo'], 'data:image/') === 0
    ) {
        $update['profile_photo'] = $data['photo'];
    }

    // Jei nėra ką saugoti
    if ( empty($update) ) {
        echo json_encode([
            'status' => 'nothing_to_update',
            'tossee_id' => $tossee_id
        ]);
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // 4. UPDATE
    $result = $wpdb->update(
        $table,
        $update,
        ['tossee_id' => $tossee_id]
    );

    // 5. REZULTATO KONTROLĖ
    if ( $result === false ) {
        echo json_encode([
            'error' => 'db_error',
            'mysql_error' => $wpdb->last_error,
            'tossee_id' => $tossee_id
        ]);
        exit;
    }

    if ( $result === 0 ) {
        echo json_encode([
            'status' => 'no_changes',
            'tossee_id' => $tossee_id,
            'update_attempted' => $update
        ]);
        exit;
    }

    // 6. OK
    echo json_encode([
        'status' => 'ok',
        'updated_fields' => array_keys($update),
        'tossee_id' => $tossee_id
    ]);
    exit;
}

/* ================================
   TOSSEE – PROFILE GET HANDLER
================================ */

add_action('admin_post_nopriv_tossee_get_profile', 'tossee_get_profile_handler');
add_action('admin_post_tossee_get_profile',        'tossee_get_profile_handler');

function tossee_get_profile_handler() {

    header('Content-Type: application/json; charset=utf-8');

    // AUTO-MIGRATION: Prideda trūkstamus stulpelius jei jų nėra
    tossee_ensure_profile_columns();

    // 1. Auth – naudojam plugin'o funkciją arba fallback
    if (function_exists('tossee_get_current_user_id')) {
        $tossee_id = tossee_get_current_user_id();
    } else {
        // Fallback - session tikrinimas
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tossee_id = !empty($_SESSION['tossee_uid']) ? $_SESSION['tossee_uid'] : (!empty($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null);
    }

    if ( ! $tossee_id ) {
        echo json_encode(['error' => 'no_auth']);
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // 2. Fetch from custom DB - NAUDOJAME 'photo' (REGISTRATION PHOTO)
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT
                username,
                email,
                dob,
                first_name,
                last_name,
                gender,
                country,
                state,
                city,
                about,
                photo
             FROM $table
             WHERE tossee_id = %s
             LIMIT 1",
            $tossee_id
        ),
        ARRAY_A
    );

    if ( ! $user ) {
        echo json_encode(['error' => 'user_not_found']);
        exit;
    }

    // 3. Užtikriname, kad visi laukai egzistuoja
    $fields = [
        'username',
        'email',
        'dob',
        'first_name',
        'last_name',
        'gender',
        'country',
        'state',
        'city',
        'about',
        'photo'
    ];

    foreach ( $fields as $field ) {
        if ( ! isset($user[$field]) ) {
            $user[$field] = '';
        }
    }

    // 4. Return JSON
    echo json_encode($user);
    exit;
}

/* ================================
   REST API ENDPOINTS (optional)
================================ */

add_action('rest_api_init', function () {

    // ONLY Registration Photo endpoint - don't conflict with plugin
    register_rest_route('tossee/v1', '/registration-photo', [
        'methods'  => 'GET',
        'callback' => 'tossee_api_get_registration_photo',
        'permission_callback' => '__return_true'
    ]);

});

function tossee_api_get_registration_photo() {
    // Auth – naudojam plugin'o funkciją arba fallback
    if (function_exists('tossee_get_current_user_id')) {
        $tossee_id = tossee_get_current_user_id();
    } else {
        // Fallback - session tikrinimas
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tossee_id = !empty($_SESSION['tossee_uid']) ? $_SESSION['tossee_uid'] : (!empty($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null);
    }

    if ( ! $tossee_id ) {
        return new WP_Error('no_auth', 'Not authenticated', ['status' => 401]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // Fetch ONLY photo from registration
    $photo = $wpdb->get_var($wpdb->prepare(
        "SELECT photo FROM $table WHERE tossee_id = %s",
        $tossee_id
    ));

    if ( ! $photo ) {
        return new WP_Error('no_photo', 'Photo not found', ['status' => 404]);
    }

    return rest_ensure_response(['photo' => $photo]);
}
