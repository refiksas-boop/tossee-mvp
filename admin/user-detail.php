<?php
/**
 * Tossee Admin - User Detail View
 * Display full user profile in WordPress admin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * User detail page
 */
function tossee_admin_user_detail_page() {
    if (!isset($_GET['id'])) {
        echo '<div class="wrap"><h1>User Not Found</h1><p>No user ID specified.</p></div>';
        return;
    }

    $tossee_id = sanitize_text_field($_GET['id']);
    $user = tossee_get_user_by_id($tossee_id);

    if (!$user) {
        echo '<div class="wrap"><h1>User Not Found</h1><p>User with ID "' . esc_html($tossee_id) . '" does not exist.</p></div>';
        return;
    }

    // Handle delete action
    if (isset($_POST['delete_user']) && check_admin_referer('tossee_delete_user_' . $tossee_id)) {
        tossee_admin_delete_user($tossee_id);
        wp_safe_redirect(admin_url('admin.php?page=tossee-users&deleted=1'));
        exit;
    }

    $age = tossee_calculate_age($user->dob);
    $photo_url = tossee_get_photo_url($user->photo);
    $full_name = tossee_get_display_name($user);
    $hobbies = tossee_parse_hobbies($user->hobbies);
    $hobbies_display = !empty($hobbies) ? implode(', ', $hobbies) : 'None';

    ?>
    <div class="wrap">
        <h1><?php echo esc_html($full_name); ?> (@<?php echo esc_html($user->username); ?>)</h1>

        <a href="<?php echo admin_url('admin.php?page=tossee-users'); ?>" class="button">
            ← Back to Users List
        </a>

        <div style="margin-top: 20px; display: grid; grid-template-columns: 300px 1fr; gap: 30px;">

            <!-- Left Column: Photo -->
            <div>
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px; text-align: center;">
                    <h2>Profile Photo</h2>
                    <img src="<?php echo esc_url($photo_url); ?>"
                         style="width: 250px; height: 250px; border-radius: 50%; object-fit: cover; border: 3px solid #0073aa;">
                    <p style="margin-top: 15px; font-size: 12px; color: #666;">
                        Verification photo taken during registration
                    </p>
                </div>

                <!-- Actions -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px; margin-top: 20px;">
                    <h3>Admin Actions</h3>

                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        <?php wp_nonce_field('tossee_delete_user_' . $tossee_id); ?>
                        <button type="submit" name="delete_user" class="button button-danger" style="width: 100%; background: #dc3545; color: #fff; border-color: #dc3545;">
                            Delete User
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: User Details -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
                <h2>User Information</h2>

                <table class="form-table" style="margin-top: 0;">
                    <tr>
                        <th scope="row">Tossee ID</th>
                        <td><code><?php echo esc_html($user->tossee_id); ?></code></td>
                    </tr>
                    <tr>
                        <th scope="row">Username</th>
                        <td><strong><?php echo esc_html($user->username); ?></strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Email</th>
                        <td><a href="mailto:<?php echo esc_attr($user->email); ?>"><?php echo esc_html($user->email); ?></a></td>
                    </tr>
                    <tr>
                        <th scope="row">First Name</th>
                        <td><?php echo esc_html($user->first_name ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Last Name</th>
                        <td><?php echo esc_html($user->last_name ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Gender</th>
                        <td><?php echo esc_html($user->gender ? ucfirst($user->gender) : '-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Date of Birth</th>
                        <td><?php echo esc_html(tossee_format_date($user->dob, 'F j, Y')); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Age</th>
                        <td><strong><?php echo esc_html($age); ?> years old</strong></td>
                    </tr>
                    <tr>
                        <th scope="row">Country</th>
                        <td><?php echo esc_html($user->country ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">City</th>
                        <td><?php echo esc_html($user->city ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Hobbies</th>
                        <td><?php echo esc_html($hobbies_display); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">About</th>
                        <td><?php echo esc_html($user->about ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Registered</th>
                        <td><?php echo esc_html(tossee_format_date($user->created_at, 'F j, Y \a\t g:i A')); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Last Updated</th>
                        <td><?php echo esc_html($user->updated_at ? tossee_format_date($user->updated_at, 'F j, Y \a\t g:i A') : 'Never'); ?></td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
    <?php
}

/**
 * Delete user
 */
function tossee_admin_delete_user($tossee_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $deleted = $wpdb->delete(
        $table,
        array('tossee_id' => $tossee_id),
        array('%s')
    );

    if ($deleted) {
        tossee_log("Admin deleted user: {$tossee_id}", 'info');
    } else {
        tossee_log("Failed to delete user: {$tossee_id} - " . $wpdb->last_error, 'error');
    }

    return $deleted;
}
