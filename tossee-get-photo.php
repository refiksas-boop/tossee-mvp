<?php
/**
 * Tossee Registration Photo Endpoint
 * Gauna registracijos nuotrauką iš wp_tossee_users lentelės
 */

// Ieškome wp-load.php
$wp_load_locations = array(
    dirname(__FILE__) . '/wp-load.php',
    dirname(__FILE__) . '/../wp-load.php',
    dirname(__FILE__) . '/../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php',
);

$wp_loaded = false;
foreach ($wp_load_locations as $location) {
    if (file_exists($location)) {
        require_once($location);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    http_response_code(500);
    die('WordPress not found');
}

// Startuojame sesiją
if (!session_id()) {
    session_start();
}

// Patikriname ar vartotojas prisijungęs
if (empty($_SESSION['tossee_id'])) {
    header("Content-Type: application/json");
    http_response_code(401);
    echo json_encode(array(
        'error' => true,
        'message' => 'Not logged in'
    ));
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'tossee_users';
$tossee_id = $_SESSION['tossee_id'];

// Gauname nuotrauką
$photo = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT photo FROM $table WHERE tossee_id = %s LIMIT 1",
        $tossee_id
    )
);

if (!$photo) {
    header("Content-Type: application/json");
    http_response_code(404);
    echo json_encode(array(
        'error' => true,
        'message' => 'Photo not found'
    ));
    exit;
}

// Jei nuotrauka yra base64 su data URI
if (strpos($photo, 'data:image') === 0) {
    // Ištraukiame MIME type
    preg_match('/data:(image\/[^;]+);base64,(.*)/', $photo, $matches);

    if ($matches) {
        $mime_type = $matches[1];
        $base64_data = $matches[2];

        // Rodyti kaip paveikslėlį
        header("Content-Type: " . $mime_type);
        echo base64_decode($base64_data);
        exit;
    }
}

// Jei yra paprastas base64 be data URI
header("Content-Type: image/png");
echo base64_decode($photo);
exit;
