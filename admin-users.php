<?php
/* ================================
   TOSSEE – ADMIN USERS PANEL
   Reads directly from wp_tossee_users
================================ */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* --- Register admin menu --- */
add_action( 'admin_menu', 'tossee_admin_menu' );
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
        'tossee-user-view',
        'tossee_admin_user_view_page'
    );
}

/* --- Handle ban / delete actions --- */
add_action( 'admin_init', 'tossee_handle_admin_actions' );
function tossee_handle_admin_actions() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( empty( $_GET['tossee_action'] ) || empty( $_GET['tossee_uid'] ) ) return;

    global $wpdb;
    $table     = $wpdb->prefix . 'tossee_users';
    $tossee_id = sanitize_text_field( $_GET['tossee_uid'] );

    if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'tossee_admin_action_' . $tossee_id ) ) {
        wp_die( 'Security check failed.' );
    }

    switch ( $_GET['tossee_action'] ) {
        case 'ban':
            $wpdb->update( $table, [ 'is_banned' => 1 ], [ 'tossee_id' => $tossee_id ], [ '%d' ], [ '%s' ] );
            break;
        case 'unban':
            $wpdb->update( $table, [ 'is_banned' => 0 ], [ 'tossee_id' => $tossee_id ], [ '%d' ], [ '%s' ] );
            break;
        case 'delete':
            $wpdb->delete( $table, [ 'tossee_id' => $tossee_id ], [ '%s' ] );
            wp_safe_redirect( admin_url( 'admin.php?page=tossee-users&deleted=1' ) );
            exit;
    }

    wp_safe_redirect( wp_get_referer() ?: admin_url( 'admin.php?page=tossee-users' ) );
    exit;
}

/* --- Users list page --- */
function tossee_admin_users_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
    $paged  = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $per_page = 20;
    $offset   = ( $paged - 1 ) * $per_page;

    if ( $search ) {
        $users = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE username LIKE %s OR email LIKE %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%',
            $per_page,
            $offset
        ) );
        $total = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE username LIKE %s OR email LIKE %s",
            '%' . $wpdb->esc_like( $search ) . '%',
            '%' . $wpdb->esc_like( $search ) . '%'
        ) );
    } else {
        $users = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ) );
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    }

    $total_pages = ceil( $total / $per_page );
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Tossee Users</h1>
        <span class="title-count"><?php echo intval( $total ); ?> total</span>

        <?php if ( ! empty( $_GET['deleted'] ) ) : ?>
            <div class="notice notice-success"><p>User deleted.</p></div>
        <?php endif; ?>

        <form method="get" action="">
            <input type="hidden" name="page" value="tossee-users">
            <p class="search-box">
                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search username or email">
                <input type="submit" class="button" value="Search">
            </p>
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:60px">Photo</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Age</th>
                    <th>Country / City</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( $users ) : foreach ( $users as $u ) :
                $dob = $u->dob ? new DateTime( $u->dob ) : null;
                $age = $dob ? (int) $dob->diff( new DateTime() )->y : '—';
                $nonce = wp_create_nonce( 'tossee_admin_action_' . $u->tossee_id );
                $view_url = admin_url( 'admin.php?page=tossee-user-view&tossee_id=' . urlencode( $u->tossee_id ) );
                $ban_label = $u->is_banned ? 'Unban' : 'Ban';
                $ban_action = $u->is_banned ? 'unban' : 'ban';
                $ban_url = admin_url( 'admin.php?tossee_action=' . $ban_action . '&tossee_uid=' . urlencode( $u->tossee_id ) . '&_wpnonce=' . $nonce );
                $del_url  = admin_url( 'admin.php?tossee_action=delete&tossee_uid=' . urlencode( $u->tossee_id ) . '&_wpnonce=' . $nonce );
            ?>
                <tr>
                    <td>
                        <?php if ( $u->photo ) : ?>
                            <a href="<?php echo esc_url( $view_url ); ?>">
                                <img src="<?php echo esc_attr( $u->photo ); ?>"
                                     style="width:48px;height:48px;object-fit:cover;border-radius:50%;"
                                     alt="<?php echo esc_attr( $u->username ); ?>">
                            </a>
                        <?php else : ?>
                            <span style="color:#999">—</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo esc_url( $view_url ); ?>"><?php echo esc_html( $u->username ); ?></a></td>
                    <td><?php echo esc_html( $u->email ); ?></td>
                    <td><?php echo esc_html( $age ); ?></td>
                    <td><?php echo esc_html( trim( ( $u->country ?? '' ) . ' ' . ( $u->city ?? '' ) ) ?: '—' ); ?></td>
                    <td><?php echo esc_html( $u->created_at ); ?></td>
                    <td><?php echo $u->is_banned ? '<span style="color:red">Banned</span>' : '<span style="color:green">Active</span>'; ?></td>
                    <td>
                        <a href="<?php echo esc_url( $ban_url ); ?>" class="button button-small"><?php echo esc_html( $ban_label ); ?></a>
                        <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete"
                           onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links( [
                        'base'    => add_query_arg( 'paged', '%#%' ),
                        'format'  => '',
                        'current' => $paged,
                        'total'   => $total_pages,
                    ] );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/* --- Single user view page --- */
function tossee_admin_user_view_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'tossee_users';

    $tossee_id = sanitize_text_field( $_GET['tossee_id'] ?? '' );
    if ( ! $tossee_id ) {
        echo '<div class="wrap"><p>No user specified.</p></div>';
        return;
    }

    $u = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE tossee_id = %s", $tossee_id ) );
    if ( ! $u ) {
        echo '<div class="wrap"><p>User not found.</p></div>';
        return;
    }

    $dob = $u->dob ? new DateTime( $u->dob ) : null;
    $age = $dob ? (int) $dob->diff( new DateTime() )->y : '—';
    $nonce = wp_create_nonce( 'tossee_admin_action_' . $u->tossee_id );
    $ban_label  = $u->is_banned ? 'Unban' : 'Ban';
    $ban_action = $u->is_banned ? 'unban' : 'ban';
    $ban_url = admin_url( 'admin.php?tossee_action=' . $ban_action . '&tossee_uid=' . urlencode( $u->tossee_id ) . '&_wpnonce=' . $nonce . '&_wp_http_referer=' . urlencode( $_SERVER['REQUEST_URI'] ) );
    $del_url  = admin_url( 'admin.php?tossee_action=delete&tossee_uid=' . urlencode( $u->tossee_id ) . '&_wpnonce=' . $nonce );
    ?>
    <div class="wrap">
        <h1>User: <?php echo esc_html( $u->username ); ?></h1>
        <a href="<?php echo admin_url( 'admin.php?page=tossee-users' ); ?>" class="button">&larr; Back to list</a>
        &nbsp;
        <a href="<?php echo esc_url( $ban_url ); ?>" class="button"><?php echo esc_html( $ban_label ); ?></a>
        <a href="<?php echo esc_url( $del_url ); ?>" class="button button-link-delete"
           onclick="return confirm('Delete this user permanently?')">Delete</a>

        <table class="form-table" style="margin-top:20px">
            <tr><th>Tossee ID</th><td><?php echo esc_html( $u->tossee_id ); ?></td></tr>
            <tr><th>Username</th><td><?php echo esc_html( $u->username ); ?></td></tr>
            <tr><th>Email</th><td><?php echo esc_html( $u->email ); ?></td></tr>
            <tr><th>Date of Birth</th><td><?php echo esc_html( $u->dob ?? '—' ); ?> (age: <?php echo esc_html( $age ); ?>)</td></tr>
            <tr><th>Gender</th><td><?php echo esc_html( $u->gender ?? '—' ); ?></td></tr>
            <tr><th>Name</th><td><?php echo esc_html( trim( ( $u->first_name ?? '' ) . ' ' . ( $u->last_name ?? '' ) ) ?: '—' ); ?></td></tr>
            <tr><th>Country</th><td><?php echo esc_html( $u->country ?? '—' ); ?></td></tr>
            <tr><th>City</th><td><?php echo esc_html( $u->city ?? '—' ); ?></td></tr>
            <tr><th>Hobbies</th><td><?php echo esc_html( $u->hobbies ?? '—' ); ?></td></tr>
            <tr><th>About</th><td><?php echo esc_html( $u->about ?? '—' ); ?></td></tr>
            <tr><th>Registered</th><td><?php echo esc_html( $u->created_at ); ?></td></tr>
            <tr><th>Status</th><td><?php echo $u->is_banned ? '<strong style="color:red">BANNED</strong>' : '<strong style="color:green">Active</strong>'; ?></td></tr>
            <tr>
                <th>Selfie Photo</th>
                <td>
                    <?php if ( $u->photo ) : ?>
                        <img src="<?php echo esc_attr( $u->photo ); ?>"
                             style="max-width:300px;border-radius:8px;border:1px solid #ddd;"
                             alt="Selfie">
                    <?php else : ?>
                        <em>No photo</em>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
