/**
 * Astra functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Constants
 */
define( 'ASTRA_THEME_VERSION', '4.11.10' );
define( 'ASTRA_THEME_SETTINGS', 'astra-settings' );
define( 'ASTRA_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'ASTRA_THEME_URI', trailingslashit( esc_url( get_template_directory_uri() ) ) );
define( 'ASTRA_THEME_ORG_VERSION', file_exists( ASTRA_THEME_DIR . 'inc/w-org-version.php' ) );

/**
 * Minimum Version requirement of the Astra Pro addon.
 * This constant will be used to display the notice asking user to update the Astra addon to the version defined below.
 */
define( 'ASTRA_EXT_MIN_VER', '4.11.6' );

/**
 * Load in-house compatibility.
 */
if ( ASTRA_THEME_ORG_VERSION ) {
	require_once ASTRA_THEME_DIR . 'inc/w-org-version.php';
}

/**
 * Setup helper functions of Astra.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-theme-options.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-theme-strings.php';
require_once ASTRA_THEME_DIR . 'inc/core/common-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-icons.php';

define( 'ASTRA_WEBSITE_BASE_URL', 'https://wpastra.com' );

/**
 * ToDo: Deprecate constants in future versions as they are no longer used in the codebase.
 */
define( 'ASTRA_PRO_UPGRADE_URL', ASTRA_THEME_ORG_VERSION ? astra_get_pro_url( '/pricing/', 'free-theme', 'dashboard', 'upgrade' ) : 'https://woocommerce.com/products/astra-pro/' );
define( 'ASTRA_PRO_CUSTOMIZER_UPGRADE_URL', ASTRA_THEME_ORG_VERSION ? astra_get_pro_url( '/pricing/', 'free-theme', 'customizer', 'upgrade' ) : 'https://woocommerce.com/products/astra-pro/' );

/**
 * Update theme
 */
require_once ASTRA_THEME_DIR . 'inc/theme-update/astra-update-functions.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-background-updater.php';

/**
 * Fonts Files
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-font-families.php';
if ( is_admin() ) {
	require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts-data.php';
}

require_once ASTRA_THEME_DIR . 'inc/lib/webfont/class-astra-webfont-loader.php';
require_once ASTRA_THEME_DIR . 'inc/lib/docs/class-astra-docs-loader.php';
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts.php';

require_once ASTRA_THEME_DIR . 'inc/dynamic-css/custom-menu-old-header.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/container-layouts.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/astra-icons.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-walker-page.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-enqueue-scripts.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-gutenberg-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-wp-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/block-editor-compatibility.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/inline-on-mobile.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/content-background.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/dark-mode.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-dynamic-css.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-global-palette.php';

// Enable NPS Survey only if the starter templates version is < 4.3.7 or > 4.4.4 to prevent fatal error.
if ( ! defined( 'ASTRA_SITES_VER' ) || version_compare( ASTRA_SITES_VER, '4.3.7', '<' ) || version_compare( ASTRA_SITES_VER, '4.4.4', '>' ) ) {
	// NPS Survey Integration
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-notice.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-survey.php';
}

/**
 * Custom template tags for this theme.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-attr.php';
require_once ASTRA_THEME_DIR . 'inc/template-tags.php';

require_once ASTRA_THEME_DIR . 'inc/widgets.php';
require_once ASTRA_THEME_DIR . 'inc/core/theme-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/admin-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/sidebar-manager.php';

/**
 * Markup Functions
 */
require_once ASTRA_THEME_DIR . 'inc/markup-extras.php';
require_once ASTRA_THEME_DIR . 'inc/extras.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog-config.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog.php';
require_once ASTRA_THEME_DIR . 'inc/blog/single-blog.php';

/**
 * Markup Files
 */
require_once ASTRA_THEME_DIR . 'inc/template-parts.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-loop.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-mobile-header.php';

/**
 * Functions and definitions.
 */
require_once ASTRA_THEME_DIR . 'inc/class-astra-after-setup-theme.php';

// Required files.
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-helper.php';

require_once ASTRA_THEME_DIR . 'inc/schema/class-astra-schema.php';

/* Setup API */
require_once ASTRA_THEME_DIR . 'admin/includes/class-astra-api-init.php';

if ( is_admin() ) {
	/**
	 * Admin Menu Settings
	 */
	require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-settings.php';
	require_once ASTRA_THEME_DIR . 'admin/class-astra-admin-loader.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/astra-notices/class-astra-notices.php';
}

/**
 * Metabox additions.
 */
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-boxes.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-box-operations.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-elementor-editor-settings.php';

/**
 * Customizer additions.
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-customizer.php';

/**
 * Astra Modules.
 */
require_once ASTRA_THEME_DIR . 'inc/modules/posts-structures/class-astra-post-structures.php';
require_once ASTRA_THEME_DIR . 'inc/modules/related-posts/class-astra-related-posts.php';

/**
 * Compatibility
 */
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gutenberg.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-jetpack.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/woocommerce/class-astra-woocommerce.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/edd/class-astra-edd.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/lifterlms/class-astra-lifterlms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/learndash/class-astra-learndash.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bb-ultimate-addon.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-contact-form-7.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-visual-composer.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-site-origin.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gravity-forms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bne-flyout.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-ubermeu.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-divi-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-amp.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-yoast-seo.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/surecart/class-astra-surecart.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-starter-content.php';
require_once ASTRA_THEME_DIR . 'inc/addons/transparent-header/class-astra-ext-transparent-header.php';
require_once ASTRA_THEME_DIR . 'inc/addons/breadcrumbs/class-astra-breadcrumbs.php';
require_once ASTRA_THEME_DIR . 'inc/addons/scroll-to-top/class-astra-scroll-to-top.php';
require_once ASTRA_THEME_DIR . 'inc/addons/heading-colors/class-astra-heading-colors.php';
require_once ASTRA_THEME_DIR . 'inc/builder/class-astra-builder-loader.php';

// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor-pro.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-web-stories.php';
}

// Beaver Themer compatibility requires PHP 5.3 for anonymous functions.
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-themer.php';
}

require_once ASTRA_THEME_DIR . 'inc/core/markup/class-astra-markup.php';

/**
 * Load deprecated functions
 */
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-filters.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-functions.php';
// Po registracijos visada redirect į chat
add_filter( 'registration_redirect', function( $redirect_to ) {
    return 'https://chat.tossee.com';
});
add_action('wp_logout', function() {
    // pilnai ištrinam sesiją
    if (function_exists('wp_destroy_current_session')) {
        wp_destroy_current_session();
    }
    wp_clear_auth_cookie();

    // siunčiam į tavo custom login.html
    wp_redirect('https://tossee.com/login');
    exit;
});
add_action('template_redirect', function () {
    if (isset($_GET['tossee_logout'])) {
        wp_logout();
        $redirect = !empty($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : home_url('/');
        wp_safe_redirect($redirect);
        exit;
    }
});
// Paslepia WP admin barą visiems vartotojams frontend'e
add_filter('show_admin_bar', '__return_false');

// REST API endpoint registracijos nuotraukai gauti
add_action('rest_api_init', function () {
    register_rest_route('tossee/v1', '/photo', array(
        'methods' => 'GET',
        'callback' => 'tossee_get_registration_photo',
        'permission_callback' => '__return_true'
    ));

    // Debug endpoint - sesijos patikrinimui
    register_rest_route('tossee/v1', '/debug-session', array(
        'methods' => 'GET',
        'callback' => 'tossee_debug_session',
        'permission_callback' => '__return_true'
    ));
});

function tossee_get_registration_photo($request) {
    // Gauname current user per plugin'o funkciją
    $user = tossee_get_current_user();

    if (!$user) {
        return array(
            'error' => true,
            'message' => 'Not logged in'
        );
    }

    if (empty($user->photo)) {
        return array(
            'error' => true,
            'message' => 'Photo not found'
        );
    }

    return array(
        'success' => true,
        'photo' => $user->photo
    );
}

function tossee_debug_session($request) {
    if (!session_id()) {
        session_start();
    }

    return array(
        'session_id' => session_id(),
        'tossee_id' => isset($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null,
        'session_data' => $_SESSION
    );
}

// My Account shortcode
add_shortcode('tossee_my_account', 'tossee_my_account_shortcode');

function tossee_my_account_shortcode() {
    ob_start();
    ?>
    <style>
        .tossee-my-account {
            max-width: 420px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }
        .tossee-welcome {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 40px;
            background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .tossee-menu-btn {
            display: block;
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .tossee-menu-btn:hover {
            transform: translateY(-2px);
            opacity: 0.95;
        }
        .tossee-photo-modal {
            display: none;
            position: fixed;
            z-index: 9999;
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
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            position: relative;
        }
        .tossee-modal-content h2 {
            color: #140D42;
            margin-bottom: 20px;
        }
        .tossee-modal-content img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .tossee-close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #140D42;
            cursor: pointer;
            background: none;
            border: none;
        }
        .tossee-error-msg {
            color: #ff4444;
            padding: 15px;
            background: rgba(255,68,68,0.1);
            border-radius: 8px;
            margin-top: 15px;
        }
    </style>

    <div class="tossee-my-account">
        <div class="tossee-welcome" id="tosseeWelcome">Welcome 👋</div>

        <div class="tossee-menu">
            <a href="/profile.html" class="tossee-menu-btn">View Profile</a>
            <a href="/edit-profile.html" class="tossee-menu-btn">Edit Profile</a>
            <button id="tosseeShowPhoto" class="tossee-menu-btn">Registration Photo</button>
            <button id="tosseeChangePass" class="tossee-menu-btn">Change Password</button>
        </div>
    </div>

    <div id="tosseePhotoModal" class="tossee-photo-modal">
        <div class="tossee-modal-content">
            <button class="tossee-close-modal" id="tosseeCloseModal">&times;</button>
            <h2>Registration Photo</h2>
            <div id="tosseePhotoContainer">
                <img id="tosseePhotoImage" src="" alt="Loading..." style="display:none;">
            </div>
            <div id="tosseePhotoError" class="tossee-error-msg" style="display:none;"></div>
        </div>
    </div>

    <script>
    (function() {
        // Load user data
        fetch('/wp-json/tossee/v1/profile', { credentials: 'include' })
            .then(r => r.json())
            .then(user => {
                if (user && user.username) {
                    document.getElementById('tosseeWelcome').innerHTML = 'Welcome, ' + user.username + ' 👋';
                }
            })
            .catch(err => console.error('Failed to load user:', err));

        // Show photo modal
        document.getElementById('tosseeShowPhoto').addEventListener('click', async function() {
            const modal = document.getElementById('tosseePhotoModal');
            const img = document.getElementById('tosseePhotoImage');
            const error = document.getElementById('tosseePhotoError');

            modal.style.display = 'flex';
            img.style.display = 'none';
            error.style.display = 'none';

            try {
                const response = await fetch('/wp-json/tossee/v1/profile', { credentials: 'include' });

                if (!response.ok) {
                    throw new Error('Photo not found');
                }

                const data = await response.json();

                if (!data || !data.photo) {
                    throw new Error('Photo not available');
                }

                img.src = data.photo;
                img.style.display = 'block';
            } catch (err) {
                error.textContent = err.message || 'Photo unavailable';
                error.style.display = 'block';
            }
        });

        // Close modal
        document.getElementById('tosseeCloseModal').addEventListener('click', function() {
            document.getElementById('tosseePhotoModal').style.display = 'none';
        });

        document.getElementById('tosseePhotoModal').addEventListener('click', function(e) {
            if (e.target.id === 'tosseePhotoModal') {
                this.style.display = 'none';
            }
        });

        // Change password
        document.getElementById('tosseeChangePass').addEventListener('click', function() {
            alert('Password change feature coming soon!');
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}

