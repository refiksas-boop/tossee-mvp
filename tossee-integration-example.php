<?php
/**
 * TOSSEE INTEGRATION EXAMPLE
 *
 * Šis failas parodo kaip integruoti Tossee autentifikaciją
 * į bet kokią PHP sistemą (pvz. chat.tossee.com)
 */

// ============================================
// 1. COOKIE SKAITYMAS
// ============================================

/**
 * Gauti Tossee User ID iš cookie
 */
function get_tossee_uid_from_cookie() {
    return $_COOKIE['tossee_uid'] ?? null;
}

/**
 * Gauti Tossee User ID iš session arba cookie
 */
function get_tossee_uid() {
    // Pradėti session jei dar nepradėta
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Pirma tikriname session
    if (!empty($_SESSION['tossee_uid'])) {
        return $_SESSION['tossee_uid'];
    }

    // Jei nėra session, tikriname cookie
    if (!empty($_COOKIE['tossee_uid'])) {
        $_SESSION['tossee_uid'] = $_COOKIE['tossee_uid'];
        return $_COOKIE['tossee_uid'];
    }

    return null;
}


// ============================================
// 2. DATABASE CONNECTION (WordPress DB)
// ============================================

/**
 * Connect to WordPress database
 */
function get_tossee_db_connection() {
    // KEISTI PAGAL JŪSŲ DATABASE SETTINGS!
    $host = 'localhost';
    $dbname = 'wordpress_db';
    $username = 'db_user';
    $password = 'db_password';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Tossee DB Connection failed: " . $e->getMessage());
        return null;
    }
}


// ============================================
// 3. USER DATA FUNCTIONS
// ============================================

/**
 * Gauti vartotojo duomenis pagal Tossee ID
 */
function get_tossee_user($tossee_id) {
    $pdo = get_tossee_db_connection();
    if (!$pdo) return null;

    $stmt = $pdo->prepare("
        SELECT * FROM wp_tossee_users
        WHERE tossee_id = :tossee_id AND is_blocked = 0
        LIMIT 1
    ");

    $stmt->execute(['tossee_id' => $tossee_id]);
    return $stmt->fetch(PDO::FETCH_OBJ);
}

/**
 * Gauti prisijungusį vartotoją
 */
function get_current_tossee_user() {
    $uid = get_tossee_uid();
    if (!$uid) return null;
    return get_tossee_user($uid);
}

/**
 * Patikrinti ar vartotojas prisijungęs
 */
function is_tossee_logged_in() {
    return get_tossee_uid() !== null;
}

/**
 * Require login - redirect jei neprisijungęs
 */
function require_tossee_login($redirect_to = 'https://tossee.com/login') {
    if (!is_tossee_logged_in()) {
        header("Location: $redirect_to");
        exit;
    }
}


// ============================================
// 4. USAGE EXAMPLES
// ============================================

// Example 1: Paprastas authentication check
// -----------------------------------------
/*
require_tossee_login();

$user = get_current_tossee_user();
echo "Hello, " . htmlspecialchars($user->username);
*/


// Example 2: Protected page
// -----------------------------------------
/*
<?php
require_once 'tossee-integration-example.php';

if (!is_tossee_logged_in()) {
    header("Location: https://tossee.com/login");
    exit;
}

$user = get_current_tossee_user();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat - Tossee</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user->username); ?>!</h1>
    <p>Your Tossee ID: <?php echo htmlspecialchars($user->tossee_id); ?></p>

    <?php if (!empty($user->photo)): ?>
        <img src="<?php echo htmlspecialchars($user->photo); ?>"
             alt="Profile Photo"
             style="width:100px;height:100px;border-radius:50%;">
    <?php endif; ?>
</body>
</html>
*/


// Example 3: AJAX endpoint su authentication
// -----------------------------------------
/*
<?php
require_once 'tossee-integration-example.php';

header('Content-Type: application/json');

$uid = get_tossee_uid();
if (!$uid) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user = get_current_tossee_user();
if (!$user) {
    http_response_code(403);
    echo json_encode(['error' => 'User blocked or not found']);
    exit;
}

// Return user data
echo json_encode([
    'tossee_id' => $user->tossee_id,
    'username' => $user->username,
    'email' => $user->email,
    'first_name' => $user->first_name,
    'last_name' => $user->last_name,
    'photo' => $user->photo
]);
*/


// Example 4: JavaScript integration
// -----------------------------------------
/*
<script>
// Get cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Check if user is logged in
const tosseeUid = getCookie('tossee_uid');

if (tosseeUid) {
    console.log('User logged in:', tosseeUid);

    // Fetch user data via AJAX
    fetch('/api/tossee-user.php')
        .then(res => res.json())
        .then(data => {
            console.log('User data:', data);
            document.getElementById('username').textContent = data.username;
        })
        .catch(err => console.error('Error:', err));
} else {
    // Redirect to login
    window.location.href = 'https://tossee.com/login';
}
</script>
*/


// ============================================
// 5. LOGOUT FUNCTION
// ============================================

/**
 * Logout current user
 */
function tossee_logout_user() {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear session
    unset($_SESSION['tossee_uid']);

    // Clear cookie
    $domain = (strpos($_SERVER['HTTP_HOST'], 'tossee.com') !== false) ? '.tossee.com' : '';
    setcookie('tossee_uid', '', time() - 3600, '/', $domain, true, true);

    // Redirect to login
    header('Location: https://tossee.com/login');
    exit;
}


// ============================================
// 6. MIDDLEWARE EXAMPLE (for frameworks)
// ============================================

/**
 * Middleware function to check authentication
 * Use this with routing frameworks
 */
function tossee_auth_middleware() {
    $uid = get_tossee_uid();

    if (!$uid) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $user = get_tossee_user($uid);

    if (!$user) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'User not found or blocked']);
        exit;
    }

    // Store user in global for easy access
    $GLOBALS['tossee_current_user'] = $user;

    return $user;
}


// ============================================
// 7. HELPER FUNCTIONS
// ============================================

/**
 * Get user profile photo URL (or return default)
 */
function get_tossee_user_photo($user, $default = '/assets/default-avatar.png') {
    if (!empty($user->photo) && strpos($user->photo, 'data:image/') === 0) {
        return $user->photo;
    }
    return $default;
}

/**
 * Get user full name
 */
function get_tossee_user_fullname($user) {
    $name = trim($user->first_name . ' ' . $user->last_name);
    return $name ?: $user->username;
}

/**
 * Calculate user age from DOB
 */
function get_tossee_user_age($user) {
    $dob = new DateTime($user->dob);
    $now = new DateTime();
    return $now->diff($dob)->y;
}


// ============================================
// 8. SECURITY HELPERS
// ============================================

/**
 * Check if user has permission (example)
 */
function tossee_user_can($capability) {
    $user = get_current_tossee_user();
    if (!$user) return false;

    // Add your own permission logic here
    // For example, check if user is admin, etc.

    return true;
}

/**
 * Escape output for HTML
 */
function tossee_esc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


// ============================================
// COMPLETE EXAMPLE: Chat Page
// ============================================
/*
<?php
require_once 'tossee-integration-example.php';

// Require login
require_tossee_login('https://tossee.com/login?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));

// Get current user
$user = get_current_tossee_user();
$age = get_tossee_user_age($user);
$fullname = get_tossee_user_fullname($user);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chat - Tossee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #140D42;
            color: white;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .logout-btn {
            background: #ff4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="user-info">
            <img src="<?php echo tossee_esc(get_tossee_user_photo($user)); ?>"
                 alt="Profile"
                 class="user-photo">
            <div>
                <div><strong><?php echo tossee_esc($fullname); ?></strong></div>
                <div>@<?php echo tossee_esc($user->username); ?> • <?php echo $age; ?> years old</div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <h1>Welcome to Tossee Chat!</h1>
    <p>Your Tossee ID: <code><?php echo tossee_esc($user->tossee_id); ?></code></p>

    <!-- Your chat interface here -->
</body>
</html>
*/
?>
