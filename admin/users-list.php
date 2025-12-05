<?php
/**
 * Tossee Admin - Users List
 * WordPress admin page to manage Tossee users
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register admin menu
 */
add_action('admin_menu', 'tossee_admin_menu');

function tossee_admin_menu() {
    add_menu_page(
        'Tossee Users',
        'Tossee Users',
        'manage_options',
        'tossee-users',
        'tossee_admin_users_page',
        'dashicons-groups',
        30
    );

    add_submenu_page(
        'tossee-users',
        'View User',
        null,
        'manage_options',
        'tossee-user-detail',
        'tossee_admin_user_detail_page'
    );
}

/**
 * Admin users list page
 */
function tossee_admin_users_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    // Pagination
    $per_page = 50;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Search and filters
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $country_filter = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
    $gender_filter = isset($_GET['gender']) ? sanitize_text_field($_GET['gender']) : '';

    // Build query
    $where = array('1=1');
    $params = array();

    if (!empty($search)) {
        $where[] = "(username LIKE %s OR email LIKE %s OR first_name LIKE %s OR last_name LIKE %s)";
        $search_param = '%' . $wpdb->esc_like($search) . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($country_filter)) {
        $where[] = "country = %s";
        $params[] = $country_filter;
    }

    if (!empty($gender_filter)) {
        $where[] = "gender = %s";
        $params[] = $gender_filter;
    }

    $where_clause = implode(' AND ', $where);

    // Get total count
    $total_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
    if (!empty($params)) {
        $total_query = $wpdb->prepare($total_query, $params);
    }
    $total_users = $wpdb->get_var($total_query);

    // Get users
    $query = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $params[] = $per_page;
    $params[] = $offset;
    $users = $wpdb->get_results($wpdb->prepare($query, $params));

    // Get distinct countries for filter
    $countries = $wpdb->get_col("SELECT DISTINCT country FROM $table WHERE country IS NOT NULL AND country != '' ORDER BY country");

    // Calculate pages
    $total_pages = ceil($total_users / $per_page);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Tossee Users</h1>
        <p>Total Users: <?php echo number_format($total_users); ?></p>

        <!-- Search and Filters -->
        <form method="get" action="">
            <input type="hidden" name="page" value="tossee-users">

            <p class="search-box">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search users...">

                <select name="gender">
                    <option value="">All Genders</option>
                    <option value="male" <?php selected($gender_filter, 'male'); ?>>Male</option>
                    <option value="female" <?php selected($gender_filter, 'female'); ?>>Female</option>
                    <option value="other" <?php selected($gender_filter, 'other'); ?>>Other</option>
                </select>

                <select name="country">
                    <option value="">All Countries</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo esc_attr($country); ?>" <?php selected($country_filter, $country); ?>>
                            <?php echo esc_html($country); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="button">Filter</button>
                <a href="<?php echo admin_url('admin.php?page=tossee-users'); ?>" class="button">Reset</a>
            </p>
        </form>

        <!-- Users Table -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 60px;">Photo</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 20px;">
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $age = tossee_calculate_age($user->dob);
                        $photo_url = tossee_get_photo_url($user->photo);
                        $full_name = tossee_get_display_name($user);
                        $detail_url = admin_url('admin.php?page=tossee-user-detail&id=' . urlencode($user->tossee_id));
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo esc_url($photo_url); ?>"
                                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"
                                     alt="<?php echo esc_attr($user->username); ?>">
                            </td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url($detail_url); ?>">
                                        <?php echo esc_html($user->username); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($full_name); ?></td>
                            <td><?php echo esc_html($user->email); ?></td>
                            <td><?php echo esc_html($age); ?></td>
                            <td><?php echo esc_html($user->gender ?: '-'); ?></td>
                            <td><?php echo esc_html($user->country ?: '-'); ?></td>
                            <td><?php echo esc_html($user->city ?: '-'); ?></td>
                            <td><?php echo esc_html(tossee_format_date($user->created_at, 'M j, Y')); ?></td>
                            <td>
                                <a href="<?php echo esc_url($detail_url); ?>" class="button button-small">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo number_format($total_users); ?> users</span>
                    <?php
                    $base_url = admin_url('admin.php?page=tossee-users');
                    if (!empty($search)) {
                        $base_url = add_query_arg('s', urlencode($search), $base_url);
                    }
                    if (!empty($country_filter)) {
                        $base_url = add_query_arg('country', urlencode($country_filter), $base_url);
                    }
                    if (!empty($gender_filter)) {
                        $base_url = add_query_arg('gender', urlencode($gender_filter), $base_url);
                    }

                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%', $base_url),
                        'format' => '',
                        'current' => $current_page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .search-box {
            margin: 20px 0;
        }
        .search-box input[type="search"] {
            width: 300px;
            margin-right: 10px;
        }
        .search-box select {
            margin-right: 10px;
        }
    </style>
    <?php
}
