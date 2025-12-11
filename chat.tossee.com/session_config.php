<?php
/**
 * Shared Session Configuration
 *
 * SVARBU: Šis failas turi būti IDENTIŠKAS
 * tiek ant tossee.com tiek ant chat.tossee.com
 *
 * IMPORTANT: This file must be IDENTICAL
 * on both tossee.com and chat.tossee.com
 */

// Set session cookie domain to work across subdomains
// The leading dot makes it work for both tossee.com and chat.tossee.com
ini_set('session.cookie_domain', '.tossee.com');

// Cookie path (root)
ini_set('session.cookie_path', '/');

// Security: HttpOnly flag (prevents JavaScript access)
ini_set('session.cookie_httponly', 1);

// Security: Secure flag (HTTPS only)
// Set to 1 if using HTTPS, 0 if testing on HTTP
ini_set('session.cookie_secure', 1); // Change to 0 for local testing without SSL

// Session name (same across both domains)
session_name('TOSSEE_SESSION');

// Session lifetime (24 hours)
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper function: Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['tossee_id']) && !empty($_SESSION['tossee_id']);
}

/**
 * Helper function: Get current user ID
 */
function getUserId() {
    return $_SESSION['tossee_id'] ?? null;
}

/**
 * Helper function: Require login (redirect if not logged in)
 */
function requireLogin($redirect_to = 'https://tossee.com/login.html') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect_to);
        exit;
    }
}

/**
 * Helper function: Set user session
 */
function setUserSession($tossee_id) {
    $_SESSION['tossee_id'] = $tossee_id;
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Helper function: Clear user session (logout)
 */
function clearUserSession() {
    $_SESSION = array();

    // Delete the session cookie
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
