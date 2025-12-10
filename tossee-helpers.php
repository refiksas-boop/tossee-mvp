<?php
/* ================================
   TOSSEE – HELPER FUNCTIONS
================================ */

/**
 * Logging funkcija
 */
if ( ! function_exists( 'tossee_log' ) ) {
    function tossee_log( $msg, $level = 'info' ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[TOSSEE][' . strtoupper($level) . '] ' . $msg );
        }
    }
}

/**
 * Get user by email
 */
if ( ! function_exists( 'tossee_get_user_by_email' ) ) {
    function tossee_get_user_by_email( $email ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE email = %s", $email )
        );
    }
}

/**
 * Get user by tossee_id
 */
if ( ! function_exists( 'tossee_get_user_by_id' ) ) {
    function tossee_get_user_by_id( $tossee_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE tossee_id = %s", $tossee_id )
        );
    }
}

/**
 * Set UID cookie (shared across all subdomains)
 */
if ( ! function_exists( 'tossee_set_uid_cookie' ) ) {
    function tossee_set_uid_cookie( $tossee_id ) {
        // Bendras domenas tik jei esam ant *.tossee.com
        $host   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
        $domain = ( strpos( $host, 'tossee.com' ) !== false ) ? '.tossee.com' : $host;

        setcookie(
            'tossee_uid',
            $tossee_id,
            time() + ( 86400 * 30 ), // 30 days
            '/',
            $domain ?: '',
            is_ssl(),
            true // httponly
        );

        // Set session too
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }
        $_SESSION['tossee_id'] = $tossee_id;
        $_SESSION['tossee_uid'] = $tossee_id;
    }
}

/**
 * Get current logged in user ID
 */
if ( ! function_exists( 'tossee_get_current_user_id' ) ) {
    function tossee_get_current_user_id() {
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }

        if ( ! empty( $_SESSION['tossee_uid'] ) ) {
            return $_SESSION['tossee_uid'];
        }

        if ( ! empty( $_SESSION['tossee_id'] ) ) {
            return $_SESSION['tossee_id'];
        }

        if ( ! empty( $_COOKIE['tossee_uid'] ) ) {
            $_SESSION['tossee_uid'] = $_COOKIE['tossee_uid'];
            return $_COOKIE['tossee_uid'];
        }

        return null;
    }
}

/**
 * Get current logged in user
 */
if ( ! function_exists( 'tossee_get_current_user' ) ) {
    function tossee_get_current_user() {
        $uid = tossee_get_current_user_id();
        if ( ! $uid ) {
            return null;
        }
        return tossee_get_user_by_id( $uid );
    }
}

/**
 * Logout
 */
if ( ! function_exists( 'tossee_logout' ) ) {
    function tossee_logout() {
        if ( session_status() === PHP_SESSION_NONE ) {
            session_start();
        }

        unset( $_SESSION['tossee_uid'] );
        unset( $_SESSION['tossee_id'] );

        $host   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
        $domain = ( strpos( $host, 'tossee.com' ) !== false ) ? '.tossee.com' : $host;

        setcookie( 'tossee_uid', '', time() - 3600, '/', $domain ?: '', is_ssl(), true );
    }
}

/**
 * Check if email available
 */
if ( ! function_exists( 'tossee_is_email_available' ) ) {
    function tossee_is_email_available( $email ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        $count = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE email = %s", $email )
        );
        return ( $count == 0 );
    }
}

/**
 * Check if username available
 */
if ( ! function_exists( 'tossee_is_username_available' ) ) {
    function tossee_is_username_available( $username ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tossee_users';
        $count = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE username = %s", $username )
        );
        return ( $count == 0 );
    }
}

/**
 * Require authentication - redirect to login if not logged in
 */
if ( ! function_exists( 'tossee_require_auth' ) ) {
    function tossee_require_auth() {
        $uid = tossee_get_current_user_id();
        if ( ! $uid ) {
            wp_safe_redirect( home_url( '/login' ) );
            exit;
        }
    }
}

/**
 * Parse hobbies string to array
 */
if ( ! function_exists( 'tossee_parse_hobbies' ) ) {
    function tossee_parse_hobbies( $hobbies_string ) {
        if ( empty( $hobbies_string ) ) {
            return array();
        }
        return array_map( 'trim', explode( ',', $hobbies_string ) );
    }
}

/**
 * Get photo URL (convert base64 to displayable format)
 */
if ( ! function_exists( 'tossee_get_photo_url' ) ) {
    function tossee_get_photo_url( $photo ) {
        if ( empty( $photo ) ) {
            return 'https://via.placeholder.com/300x300.png?text=No+Photo';
        }

        // If already a data URL, return as-is
        if ( strpos( $photo, 'data:image/' ) === 0 ) {
            return $photo;
        }

        // If it's a regular URL, return as-is
        if ( strpos( $photo, 'http' ) === 0 ) {
            return $photo;
        }

        // Default placeholder
        return 'https://via.placeholder.com/300x300.png?text=No+Photo';
    }
}
