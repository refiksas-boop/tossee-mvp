<?php
/**
 * Tossee MVP Custom Functions
 * Loaded by Astra theme functions.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Note: REST API endpoints are handled by tossee-core plugin
// /wp-json/tossee/v1/profile - returns user data including photo

// Registration Photo endpoint - specifically for My Account page
add_action('rest_api_init', function () {
    register_rest_route('tossee/v1', '/registration-photo', array(
        'methods' => 'GET',
        'callback' => 'tossee_get_registration_photo',
        'permission_callback' => '__return_true'
    ));
});

function tossee_get_registration_photo() {
    // Auth check
    if (function_exists('tossee_get_current_user_id')) {
        $tossee_id = tossee_get_current_user_id();
    } else {
        // Fallback
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tossee_id = !empty($_SESSION['tossee_uid']) ? $_SESSION['tossee_uid'] :
                     (!empty($_SESSION['tossee_id']) ? $_SESSION['tossee_id'] : null);
    }

    if (!$tossee_id) {
        return new WP_Error('not_logged_in', 'Not logged in', array('status' => 401));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $photo = $wpdb->get_var($wpdb->prepare(
        "SELECT photo FROM $table WHERE tossee_id = %s",
        $tossee_id
    ));

    if (!$photo) {
        return new WP_Error('no_photo', 'Photo not found', array('status' => 404));
    }

    return rest_ensure_response(array('photo' => $photo));
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
            <a href="/profile" class="tossee-menu-btn">View Profile</a>
            <a href="/edit-profile" class="tossee-menu-btn">Edit Profile</a>
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
                const response = await fetch('/wp-json/tossee/v1/registration-photo', { credentials: 'include' });

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
