<?php
/**
 * Plugin Name: Tossee Core
 * Description: Custom Tossee user system (custom DB, registration, login, admin panel).
 * Author: Andrius / Tossee
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ================================
   1. DB LENTELĖ – wp_tossee_users
================================ */

if ( ! function_exists('tossee_core_create_users_table') ) {
    function tossee_core_create_users_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tossee_users';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            tossee_id VARCHAR(40) NOT NULL,
            username VARCHAR(60) NOT NULL,
            email VARCHAR(120) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(60) DEFAULT '' NOT NULL,
            last_name VARCHAR(60) DEFAULT '' NOT NULL,
            gender VARCHAR(10) DEFAULT '' NOT NULL,
            country VARCHAR(80) DEFAULT '' NOT NULL,
            city VARCHAR(80) DEFAULT '' NOT NULL,
            dob DATE NOT NULL,
            photo LONGTEXT NOT NULL,
            is_blocked TINYINT(1) NOT NULL DEFAULT 0,
            notes TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            UNIQUE KEY tossee_id (tossee_id),
            KEY username (username)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'tossee_core_create_users_table');


/* ================================
   2. HELPER FUNKCIJOS
================================ */

if ( ! function_exists('tossee_log') ) {
    function tossee_log($msg, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[TOSSEE][$level] " . $msg);
        }
    }
}

if ( ! function_exists('tossee_generate_id') ) {
    function tossee_generate_id() {
        return 'tossee_' . wp_generate_password(12, false, false);
    }
}

if ( ! function_exists('tossee_sanitize_username') ) {
    function tossee_sanitize_username($username) {
        $username = sanitize_text_field($username);
        $username = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $username);
        return strtolower($username);
    }
}

if ( ! function_exists('tossee_is_valid_age') ) {
    function tossee_is_valid_age($dob) {
        $ts = strtotime($dob);
        if (!$ts) return false;
        $age = (int) ((time() - $ts) / (365.25 * 24 * 60 * 60));
        return $age >= 18;
    }
}

if ( ! function_exists('tossee_validate_photo') ) {
    function tossee_validate_photo($photo_base64) {
        if (empty($photo_base64)) {
            return false;
        }
        if (strpos($photo_base64, 'data:image/') !== 0) {
            return false;
        }
        return true;
    }
}

if ( ! function_exists('tossee_is_username_available') ) {
    function tossee_is_username_available($username) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        $count = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE username = %s", $username)
        );
        return ($count == 0);
    }
}

if ( ! function_exists('tossee_is_email_available') ) {
    function tossee_is_email_available($email) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        $count = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email)
        );
        return ($count == 0);
    }
}

if ( ! function_exists('tossee_get_user_by_id') ) {
    function tossee_get_user_by_id($tossee_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE tossee_id = %s", $tossee_id)
        );
    }
}

if ( ! function_exists('tossee_get_user_by_email') ) {
    function tossee_get_user_by_email($email) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE email = %s", $email)
        );
    }
}

/**
 * NUSTATOME COOKIE SU UID (bendras visiems subdomenams)
 */
if ( ! function_exists('tossee_set_uid_cookie') ) {
    function tossee_set_uid_cookie($tossee_id) {
        // 30 dienų
        $domain = (strpos($_SERVER['HTTP_HOST'], 'tossee.com') !== false) ? '.tossee.com' : '';
        setcookie(
            "tossee_uid",
            $tossee_id,
            time() + (86400 * 30),
            "/",
            $domain,
            is_ssl(),
            true
        );
        // Taip pat nustatome session
        if (!session_id()) {
            session_start();
        }
        $_SESSION['tossee_uid'] = $tossee_id;
    }
}

/**
 * GAUNAME PRISIJUNGUSIO VARTOTOJO ID
 */
if ( ! function_exists('tossee_get_current_user_id') ) {
    function tossee_get_current_user_id() {
        // Pirma tikriname session
        if (!session_id()) {
            session_start();
        }
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
}

/**
 * GAUNAME PRISIJUNGUSIO VARTOTOJO DUOMENIS
 */
if ( ! function_exists('tossee_get_current_user') ) {
    function tossee_get_current_user() {
        $uid = tossee_get_current_user_id();
        if (!$uid) {
            return null;
        }
        return tossee_get_user_by_id($uid);
    }
}

/**
 * IŠJUNGIAME COOKIE IR SESSION
 */
if ( ! function_exists('tossee_logout') ) {
    function tossee_logout() {
        if (!session_id()) {
            session_start();
        }
        unset($_SESSION['tossee_uid']);
        $domain = (strpos($_SERVER['HTTP_HOST'], 'tossee.com') !== false) ? '.tossee.com' : '';
        setcookie("tossee_uid", "", time() - 3600, "/", $domain, is_ssl(), true);
    }
}


/* ================================
   3. REGISTRACIJOS HANDLERIS
================================ */

if ( ! function_exists('tossee_custom_register_handler') ) {
    add_action('admin_post_nopriv_tossee_custom_register', 'tossee_custom_register_handler');
    add_action('admin_post_tossee_custom_register',        'tossee_custom_register_handler');

    function tossee_custom_register_handler() {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';

        tossee_log("Registration handler called", 'info');

        if (
            empty($_POST['user_login']) ||
            empty($_POST['user_email']) ||
            empty($_POST['user_pass']) ||
            empty($_POST['dob']) ||
            empty($_POST['photo'])
        ) {
            tossee_log("Registration failed: missing fields", 'error');
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'missing_fields', $back));
            exit;
        }

        // Sanitize inputs
        $username   = tossee_sanitize_username(wp_unslash($_POST['user_login']));
        $email      = sanitize_email(wp_unslash($_POST['user_email']));
        $pass       = (string) $_POST['user_pass'];
        $dob        = sanitize_text_field($_POST['dob']);
        $photo      = wp_unslash($_POST['photo']);

        $first_name = !empty($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name  = !empty($_POST['last_name'])  ? sanitize_text_field($_POST['last_name'])  : '';
        $gender     = !empty($_POST['gender'])     ? sanitize_text_field($_POST['gender'])     : '';
        $country    = !empty($_POST['country'])    ? sanitize_text_field($_POST['country'])    : '';
        $city       = !empty($_POST['city'])       ? sanitize_text_field($_POST['city'])       : '';

        // Validate
        if (strlen($username) < 3) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'username_short', $back));
            exit;
        }

        if (!is_email($email)) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'invalid_email', $back));
            exit;
        }

        if (strlen($pass) < 6) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'password_short', $back));
            exit;
        }

        if (!tossee_is_valid_age($dob)) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'age_restriction', $back));
            exit;
        }

        if (!tossee_validate_photo($photo)) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'invalid_photo', $back));
            exit;
        }

        if (!tossee_is_username_available($username)) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'username_exists', $back));
            exit;
        }

        if (!tossee_is_email_available($email)) {
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'email_exists', $back));
            exit;
        }

        $tossee_id = tossee_generate_id();
        $hash      = password_hash($pass, PASSWORD_DEFAULT);

        $inserted = $wpdb->insert(
            $table,
            [
                'tossee_id'     => $tossee_id,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => $hash,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'gender'        => $gender,
                'country'       => $country,
                'city'          => $city,
                'dob'           => $dob,
                'photo'         => $photo,
                'created_at'    => current_time('mysql'),
            ],
            [ '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' ]
        );

        if (!$inserted) {
            tossee_log("Registration failed: DB error - " . $wpdb->last_error, 'error');
            $back = wp_get_referer() ?: home_url('/register');
            wp_safe_redirect(add_query_arg('error', 'save_failed', $back));
            exit;
        }

        tossee_set_uid_cookie($tossee_id);

        tossee_log("User registered successfully: {$username} ({$tossee_id})", 'info');

        // Redirect į chat arba į success puslapį
        $redirect_url = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/');
        wp_safe_redirect($redirect_url);
        exit;
    }
}


/* ================================
   4. LOGIN HANDLERIS
================================ */

if ( ! function_exists('tossee_custom_login_handler') ) {
    add_action('admin_post_nopriv_tossee_custom_login', 'tossee_custom_login_handler');
    add_action('admin_post_tossee_custom_login',        'tossee_custom_login_handler');

    function tossee_custom_login_handler() {
        tossee_log("Login handler called", 'info');

        if (empty($_POST['user_email']) || empty($_POST['user_pass'])) {
            tossee_log("Login failed: missing fields", 'error');
            $back = wp_get_referer() ?: home_url('/login');
            wp_safe_redirect(add_query_arg('error', 'missing_fields', $back));
            exit;
        }

        $email = sanitize_email(wp_unslash($_POST['user_email']));
        $pass  = (string) $_POST['user_pass'];

        // Gauname vartotoją
        $user = tossee_get_user_by_email($email);

        if (!$user) {
            tossee_log("Login failed: user not found - {$email}", 'error');
            $back = wp_get_referer() ?: home_url('/login');
            wp_safe_redirect(add_query_arg('error', 'invalid_credentials', $back));
            exit;
        }

        // Tikriname slaptažodį
        if (!password_verify($pass, $user->password_hash)) {
            tossee_log("Login failed: wrong password - {$email}", 'error');
            $back = wp_get_referer() ?: home_url('/login');
            wp_safe_redirect(add_query_arg('error', 'invalid_credentials', $back));
            exit;
        }

        // Tikriname ar neužblokuotas
        if ((int)$user->is_blocked === 1) {
            tossee_log("Login failed: user blocked - {$email}", 'error');
            $back = wp_get_referer() ?: home_url('/login');
            wp_safe_redirect(add_query_arg('error', 'user_blocked', $back));
            exit;
        }

        // Nustatome cookie ir session
        tossee_set_uid_cookie($user->tossee_id);

        tossee_log("User logged in successfully: {$user->username} ({$user->tossee_id})", 'info');

        // Redirect
        $redirect_url = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/');
        wp_safe_redirect($redirect_url);
        exit;
    }
}


/* ================================
   5. LOGOUT HANDLERIS
================================ */

if ( ! function_exists('tossee_custom_logout_handler') ) {
    add_action('admin_post_nopriv_tossee_custom_logout', 'tossee_custom_logout_handler');
    add_action('admin_post_tossee_custom_logout',        'tossee_custom_logout_handler');

    function tossee_custom_logout_handler() {
        tossee_logout();
        $redirect = home_url('/login');
        wp_safe_redirect($redirect);
        exit;
    }
}


/* ================================
   6. REGISTRACIJOS FORMA (SHORTCODE)
================================ */

if ( ! function_exists('tossee_register_form_shortcode') ) {
    function tossee_register_form_shortcode($atts) {
        $atts = shortcode_atts([
            'redirect_to' => home_url('/')
        ], $atts);

        ob_start();
        ?>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #140D42;
    color: #fff;
    text-align: center;
  }

  .tossee-wrap {
    width: 460px;
    max-width: 90%;
    margin: 0 auto;
    padding: 10px;
  }

  .tossee-logo {
    width: 460px;
    max-width: 90%;
    height: auto;
    margin: 30px auto 20px auto;
    display: block;
  }

  .tossee-wrap h2 {
    font-size: 34px;
    margin-bottom: 20px;
    font-weight: 800;
    text-transform: uppercase;
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    letter-spacing: 1px;
  }

  .tossee-wrap form input,
  .tossee-wrap form button {
    width: 100%;
    padding: 14px;
    margin: 8px 0;
    border: none;
    border-radius: 8px;
    font-size: 16px;
  }

  .tossee-wrap form input {
    background: #fff;
    color: #000;
  }

  .tossee-btn {
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: .15s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  }

  .tossee-btn:hover {
    opacity: 0.95;
    transform: translateY(-2px);
  }

  video, canvas, #photoPreview {
    width: 100%;
    border-radius: 8px;
    margin: 10px 0;
  }

  .tossee-error {
    background: #ff4444;
    color: #fff;
    padding: 12px;
    border-radius: 8px;
    margin: 10px 0;
  }

  .tossee-link {
    color: #00c6ff;
    text-decoration: none;
    margin-top: 15px;
    display: inline-block;
  }

  /* ==== TERMS MODAL ==== */
  .tossee-modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.85);
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  .tossee-modal-content {
    width: 460px;
    max-width: 90%;
    background: #fff;
    color: #000;
    padding: 25px;
    border-radius: 12px;
    text-align: left;
    max-height: 85vh;
    overflow-y: auto;
  }

  .tossee-modal-content h2 {
    font-size: 30px;
    text-align:center;
    font-weight: 900;
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 10px;
  }

  .tossee-modal-content h3 {
    margin-top: 25px;
    font-size: 22px;
    font-weight: bold;
    text-align: center;
  }

  .tossee-modal-content ul li {
    margin-bottom: 10px;
    font-size: 16px;
    line-height: 1.4;
  }

  .tossee-confirm-btn {
    background: linear-gradient(90deg,#9b59b6,#3498db);
    color:#fff;
    font-weight:bold;
    padding:16px;
    border:none;
    width:100%;
    border-radius:6px;
    margin-top:25px;
    cursor:pointer;
    font-size:18px;
  }

  .tossee-confirm-btn:disabled {
    opacity:0.5;
    cursor:not-allowed;
  }
</style>

<div class="tossee-wrap">
  <img src="https://tossee.com/wp-content/uploads/2025/09/logo.png"
       alt="Tossee Logo"
       class="tossee-logo">

  <h2>REGISTER</h2>

  <?php if (isset($_GET['error'])): ?>
    <div class="tossee-error">
      <?php
        $error = sanitize_text_field($_GET['error']);
        $errors = [
          'missing_fields' => 'Please fill in all required fields.',
          'username_short' => 'Username must be at least 3 characters.',
          'invalid_email' => 'Please enter a valid email address.',
          'password_short' => 'Password must be at least 6 characters.',
          'age_restriction' => 'You must be 18+ to register.',
          'invalid_photo' => 'Please take a valid photo.',
          'username_exists' => 'This username is already taken.',
          'email_exists' => 'This email is already registered.',
          'save_failed' => 'Registration failed. Please try again.'
        ];
        echo isset($errors[$error]) ? $errors[$error] : 'An error occurred.';
      ?>
    </div>
  <?php endif; ?>

  <form id="tossee-register-form"
        method="post"
        action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

    <input type="hidden" name="action" value="tossee_custom_register">
    <input type="hidden" name="redirect_to" value="<?php echo esc_url($atts['redirect_to']); ?>">

    <input type="text" name="user_login" placeholder="Username" required>
    <input type="email" name="user_email" placeholder="Email" required>
    <input type="password" name="user_pass" placeholder="Password" required>
    <input type="date" id="user_dob" name="dob" required>

    <button type="button" id="enableCamera" class="tossee-btn">ENABLE CAMERA</button>
    <video id="video" autoplay playsinline style="display:none;"></video>
    <button type="button" id="takePhoto" class="tossee-btn" style="display:none;">TAKE PHOTO</button>
    <canvas id="canvas" style="display:none;"></canvas>
    <img id="photoPreview" style="display:none;"/>
    <input type="hidden" name="photo" id="photoData">

    <button type="submit" class="tossee-btn">REGISTER</button>
  </form>

  <a href="<?php echo home_url('/login'); ?>" class="tossee-link">Already have an account? Login here</a>
</div>

<!-- ========= TERMS MODAL ========= -->
<div id="rulesModal" class="tossee-modal">
  <div class="tossee-modal-content">

    <h2>TERMS & CONDITIONS</h2>
    <p>Tossee is a platform for meeting new people and chatting. By continuing, you agree to follow the rules below and understand how we enforce them.</p>

    <h3>General Rules</h3>
    <ul>
      <li>18+ only. You must be at least 18 years old to register or use Tossee.</li>
      <li>You are responsible for your actions and for all content you share.</li>
      <li>Illegal, harmful, or offensive content is prohibited.</li>
      <li>No spam, scams, or impersonation.</li>
      <li>Respect privacy. Do not publish someone else's private information without consent.</li>
      <li>Moderation &amp; enforcement. We may warn, restrict, suspend, or permanently ban accounts.</li>
      <li>Liability. Users are solely responsible for their own behavior.</li>
    </ul>

    <h3>Minors</h3>
    <ul>
      <li>Users under 18 are strictly prohibited.</li>
      <li>Do not misrepresent your age; suspected minor accounts may be deleted.</li>
      <li>If an adult and a minor engage in illegal behavior, we may notify authorities.</li>
    </ul>

    <h3>Photo Verification</h3>
    <ul>
      <li>All users must take a clear face photo during registration.</li>
      <li>The photo is used only for age verification and prevention of underage use.</li>
      <li>Your photo will never be publicly displayed or shared with other users.</li>
      <li>Accounts without a valid photo may be suspended or deleted.</li>
    </ul>

    <h3>Safety Tips</h3>
    <ul>
      <li>Do not share personal info.</li>
      <li>Never send money.</li>
      <li>Report abusive behavior.</li>
    </ul>

    <label>
      <input type="checkbox" id="agreeCheck"> I agree to the Terms.
    </label>

    <button id="confirmRules" class="tossee-confirm-btn" disabled>CONFIRM</button>
  </div>
</div>

<script>
const enableCamera  = document.getElementById("enableCamera");
const takePhoto     = document.getElementById("takePhoto");
const video         = document.getElementById("video");
const canvas        = document.getElementById("canvas");
const photoPreview  = document.getElementById("photoPreview");
const photoData     = document.getElementById("photoData");
const regForm       = document.getElementById("tossee-register-form");
const modal         = document.getElementById("rulesModal");
const confirmRules  = document.getElementById("confirmRules");
const agreeCheck    = document.getElementById("agreeCheck");

video.style.display        = "none";
takePhoto.style.display    = "none";
photoPreview.style.display = "none";

let tosseeCanSubmit = false;

/* Kamera ON */
enableCamera.onclick = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = "block";
        takePhoto.style.display = "block";
        photoPreview.style.display = "none";
    } catch (err) {
        alert("Camera access denied.");
    }
};

/* Foto darymas */
takePhoto.onclick = () => {
    const ctx = canvas.getContext("2d");
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const dataURL = canvas.toDataURL("image/png");
    photoData.value = dataURL;
    photoPreview.src = dataURL;

    const stream = video.srcObject;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }

    video.style.display = "none";
    takePhoto.style.display = "none";
    photoPreview.style.display = "block";
};

/* Submit – tikrinam amžių ir foto + termos */
regForm.addEventListener("submit", function(e) {
    if (tosseeCanSubmit) {
        return;
    }

    const dobInput = document.getElementById("user_dob");
    if (!dobInput.value) {
        e.preventDefault();
        alert("Please select date of birth.");
        return;
    }

    const dob = new Date(dobInput.value);
    const now = new Date();
    let age = now.getFullYear() - dob.getFullYear();
    const m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;

    if (age < 18) {
        e.preventDefault();
        alert("You must be 18+ to register.");
        return;
    }

    if (!photoData.value) {
        e.preventDefault();
        alert("Please take a photo.");
        return;
    }

    e.preventDefault();
    modal.style.display = "flex";
});

/* Terms checkbox */
agreeCheck.onchange = (e) => {
    confirmRules.disabled = !e.target.checked;
};

/* Confirm Terms -> real submit */
confirmRules.onclick = () => {
    tosseeCanSubmit = true;
    modal.style.display = "none";
    regForm.submit();
};
</script>
        <?php
        return ob_get_clean();
    }
}
add_shortcode('tossee_register_form', 'tossee_register_form_shortcode');


/* ================================
   7. LOGIN FORMA (SHORTCODE)
================================ */

if ( ! function_exists('tossee_login_form_shortcode') ) {
    function tossee_login_form_shortcode($atts) {
        $atts = shortcode_atts([
            'redirect_to' => home_url('/')
        ], $atts);

        // Jei jau prisijungęs, redirect
        if (tossee_get_current_user_id()) {
            wp_safe_redirect($atts['redirect_to']);
            exit;
        }

        ob_start();
        ?>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #140D42;
    color: #fff;
    text-align: center;
  }

  .tossee-wrap {
    width: 460px;
    max-width: 90%;
    margin: 0 auto;
    padding: 10px;
  }

  .tossee-logo {
    width: 460px;
    max-width: 90%;
    height: auto;
    margin: 30px auto 20px auto;
    display: block;
  }

  .tossee-wrap h2 {
    font-size: 34px;
    margin-bottom: 20px;
    font-weight: 800;
    text-transform: uppercase;
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    letter-spacing: 1px;
  }

  .tossee-wrap form input,
  .tossee-wrap form button {
    width: 100%;
    padding: 14px;
    margin: 8px 0;
    border: none;
    border-radius: 8px;
    font-size: 16px;
  }

  .tossee-wrap form input {
    background: #fff;
    color: #000;
  }

  .tossee-btn {
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: .15s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  }

  .tossee-btn:hover {
    opacity: 0.95;
    transform: translateY(-2px);
  }

  .tossee-error {
    background: #ff4444;
    color: #fff;
    padding: 12px;
    border-radius: 8px;
    margin: 10px 0;
  }

  .tossee-link {
    color: #00c6ff;
    text-decoration: none;
    margin-top: 15px;
    display: inline-block;
  }
</style>

<div class="tossee-wrap">
  <img src="https://tossee.com/wp-content/uploads/2025/09/logo.png"
       alt="Tossee Logo"
       class="tossee-logo">

  <h2>LOGIN</h2>

  <?php if (isset($_GET['error'])): ?>
    <div class="tossee-error">
      <?php
        $error = sanitize_text_field($_GET['error']);
        $errors = [
          'missing_fields' => 'Please fill in all fields.',
          'invalid_credentials' => 'Invalid email or password.',
          'user_blocked' => 'Your account has been blocked. Please contact support.'
        ];
        echo isset($errors[$error]) ? $errors[$error] : 'An error occurred.';
      ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
    <input type="hidden" name="action" value="tossee_custom_login">
    <input type="hidden" name="redirect_to" value="<?php echo esc_url($atts['redirect_to']); ?>">

    <input type="email" name="user_email" placeholder="Email" required>
    <input type="password" name="user_pass" placeholder="Password" required>

    <button type="submit" class="tossee-btn">LOGIN</button>
  </form>

  <a href="<?php echo home_url('/register'); ?>" class="tossee-link">Don't have an account? Register here</a>
</div>
        <?php
        return ob_get_clean();
    }
}
add_shortcode('tossee_login_form', 'tossee_login_form_shortcode');


/* ================================
   8. ADMIN – TOSSEE USERS PANEL
================================ */

if ( ! function_exists('tossee_core_admin_menu') ) {
    add_action('admin_menu', 'tossee_core_admin_menu');

    function tossee_core_admin_menu() {
        add_menu_page(
            'Tossee Users',
            'Tossee Users',
            'manage_options',
            'tossee-users',
            'tossee_core_users_page',
            'dashicons-groups',
            26
        );
    }
}

if ( ! function_exists('tossee_core_users_page') ) {
    function tossee_core_users_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';

        // Actions: block/unblock/delete/reset_pass
        if (isset($_GET['action'], $_GET['id'], $_GET['_wpnonce'])) {
            $id = (int) $_GET['id'];
            if (!wp_verify_nonce($_GET['_wpnonce'], 'tossee_user_action_' . $id)) {
                echo '<div class="notice notice-error"><p>Invalid nonce.</p></div>';
            } else {
                $action = sanitize_text_field($_GET['action']);

                if ($action === 'block') {
                    $wpdb->update($table, ['is_blocked' => 1], ['id' => $id], ['%d'], ['%d']);
                    echo '<div class="notice notice-success"><p>User blocked.</p></div>';
                } elseif ($action === 'unblock') {
                    $wpdb->update($table, ['is_blocked' => 0], ['id' => $id], ['%d'], ['%d']);
                    echo '<div class="notice notice-success"><p>User unblocked.</p></div>';
                } elseif ($action === 'delete') {
                    $wpdb->delete($table, ['id' => $id], ['%d']);
                    echo '<div class="notice notice-success"><p>User deleted.</p></div>';
                } elseif ($action === 'reset_pass') {
                    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
                    if ($user) {
                        $new_pass = wp_generate_password(10, false);
                        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                        $wpdb->update($table, ['password_hash' => $hash], ['id' => $id], ['%s'], ['%d']);

                        $subject = 'Your new Tossee password';
                        $message = "Hi " . $user->username . ",\n\nYour new Tossee password: " . $new_pass . "\n\nPlease keep it safe.";
                        wp_mail($user->email, $subject, $message);

                        echo '<div class="notice notice-success"><p>Password reset and emailed to user.</p></div>';
                    }
                }
            }
        }

        // Detail view
        if (isset($_GET['view']) && $_GET['view'] === 'user' && isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            tossee_core_user_detail_view($id);
            return;
        }

        // List view
        $users = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 200");

        echo '<div class="wrap"><h1>Tossee Users</h1>';

        if (!$users) {
            echo '<p>No users found.</p></div>';
            return;
        }

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>
                <th>ID</th>
                <th>Tossee ID</th>
                <th>Photo</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Country / City</th>
                <th>Gender</th>
                <th>Blocked</th>
                <th>Created</th>
                <th>Actions</th>
              </tr></thead><tbody>';

        foreach ($users as $u) {
            $nonce = wp_create_nonce('tossee_user_action_' . $u->id);
            $photo_html = '';
            if (!empty($u->photo) && strpos($u->photo, 'data:image/') === 0) {
                $photo_html = '<img src="' . esc_attr($u->photo) . '" style="width:60px;height:60px;border-radius:6px;object-fit:cover;" />';
            }

            $actions = [];
            $actions[] = '<a href="' . esc_url(admin_url('admin.php?page=tossee-users&view=user&id=' . $u->id)) . '">View</a>';
            if ((int)$u->is_blocked === 0) {
                $actions[] = '<a href="' . esc_url(admin_url('admin.php?page=tossee-users&action=block&id=' . $u->id . '&_wpnonce=' . $nonce)) . '">Block</a>';
            } else {
                $actions[] = '<a href="' . esc_url(admin_url('admin.php?page=tossee-users&action=unblock&id=' . $u->id . '&_wpnonce=' . $nonce)) . '">Unblock</a>';
            }
            $actions[] = '<a href="' . esc_url(admin_url('admin.php?page=tossee-users&action=reset_pass&id=' . $u->id . '&_wpnonce=' . $nonce)) . '">Reset password</a>';
            $actions[] = '<a href="' . esc_url(admin_url('admin.php?page=tossee-users&action=delete&id=' . $u->id . '&_wpnonce=' . $nonce)) . '" onclick="return confirm(\'Delete this user?\');">Delete</a>';

            echo '<tr>';
            echo '<td>' . (int)$u->id . '</td>';
            echo '<td>' . esc_html($u->tossee_id) . '</td>';
            echo '<td>' . $photo_html . '</td>';
            echo '<td>@' . esc_html($u->username) . '</td>';
            echo '<td>' . esc_html(trim($u->first_name . ' ' . $u->last_name)) . '</td>';
            echo '<td>' . esc_html($u->email) . '</td>';
            echo '<td>' . esc_html($u->country . ' / ' . $u->city) . '</td>';
            echo '<td>' . esc_html($u->gender) . '</td>';
            echo '<td>' . ((int)$u->is_blocked ? 'Yes' : 'No') . '</td>';
            echo '<td>' . esc_html($u->created_at) . '</td>';
            echo '<td>' . implode(' | ', $actions) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }
}

if ( ! function_exists('tossee_core_user_detail_view') ) {
    function tossee_core_user_detail_view($id) {
        if (!current_user_can('manage_options')) return;
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';

        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        if (!$user) {
            echo '<div class="wrap"><h1>User not found</h1></div>';
            return;
        }

        // save notes
        if (isset($_POST['tossee_notes']) && check_admin_referer('tossee_save_notes_' . $id)) {
            $notes = wp_kses_post($_POST['tossee_notes']);
            $wpdb->update($table, ['notes' => $notes], ['id' => $id], ['%s'], ['%d']);
            $user->notes = $notes;
            echo '<div class="notice notice-success"><p>Notes updated.</p></div>';
        }

        $photo_html = '';
        if (!empty($user->photo) && strpos($user->photo, 'data:image/') === 0) {
            $photo_html = '<img src="' . esc_attr($user->photo) . '" style="width:200px;height:200px;border-radius:8px;object-fit:cover;" />';
        }

        $nonce = wp_create_nonce('tossee_user_action_' . $user->id);

        echo '<div class="wrap">';
        echo '<h1>Tossee User #' . (int)$user->id . '</h1>';

        echo '<p><a href="' . esc_url(admin_url('admin.php?page=tossee-users')) . '">&laquo; Back to list</a></p>';

        echo '<table class="form-table">';
        echo '<tr><th>Photo</th><td>' . $photo_html . '</td></tr>';
        echo '<tr><th>Tossee ID</th><td>' . esc_html($user->tossee_id) . '</td></tr>';
        echo '<tr><th>Username</th><td>@' . esc_html($user->username) . '</td></tr>';
        echo '<tr><th>Name</th><td>' . esc_html(trim($user->first_name . ' ' . $user->last_name)) . '</td></tr>';
        echo '<tr><th>Email</th><td>' . esc_html($user->email) . '</td></tr>';
        echo '<tr><th>Gender</th><td>' . esc_html($user->gender) . '</td></tr>';
        echo '<tr><th>Country</th><td>' . esc_html($user->country) . '</td></tr>';
        echo '<tr><th>City</th><td>' . esc_html($user->city) . '</td></tr>';
        echo '<tr><th>Date of birth</th><td>' . esc_html($user->dob) . '</td></tr>';
        echo '<tr><th>Blocked</th><td>' . ((int)$user->is_blocked ? 'Yes' : 'No') . '</td></tr>';
        echo '<tr><th>Created</th><td>' . esc_html($user->created_at) . '</td></tr>';
        echo '<tr><th>Updated</th><td>' . esc_html($user->updated_at) . '</td></tr>';
        echo '</table>';

        echo '<h2>Notes</h2>';
        echo '<form method="post">';
        wp_nonce_field('tossee_save_notes_' . $user->id);
        echo '<textarea name="tossee_notes" rows="6" style="width:100%;">' . esc_textarea($user->notes) . '</textarea>';
        submit_button('Save Notes');
        echo '</form>';

        echo '<h2>Actions</h2>';
        echo '<p>';
        if ((int)$user->is_blocked) {
            echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=tossee-users&action=unblock&id=' . $user->id . '&_wpnonce=' . $nonce)) . '">Unblock</a> ';
        } else {
            echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=tossee-users&action=block&id=' . $user->id . '&_wpnonce=' . $nonce)) . '">Block</a> ';
        }
        echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=tossee-users&action=reset_pass&id=' . $user->id . '&_wpnonce=' . $nonce)) . '">Reset Password</a> ';
        echo '<a class="button button-danger" onclick="return confirm(\'Delete this user?\');" href="' . esc_url(admin_url('admin.php?page=tossee-users&action=delete&id=' . $user->id . '&_wpnonce=' . $nonce)) . '">Delete</a>';
        echo '</p>';

        echo '</div>';
    }
}
