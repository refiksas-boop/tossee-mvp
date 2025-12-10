<?php
/* ================================
   TOSSEE – PROFILE PAGE SHORTCODE
================================ */

function tossee_profile_page_shortcode() {
    // Check if user is logged in
    $uid = tossee_get_current_user_id();
    if ( ! $uid ) {
        wp_safe_redirect( home_url( '/login' ) );
        exit;
    }

    $user = tossee_get_current_user();
    if ( ! $user ) {
        return '<p>User not found.</p>';
    }

    ob_start();
    ?>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #140D42;
    color: #fff;
  }

  .profile-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: rgba(255,255,255,0.95);
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    color: #000;
  }

  .profile-header {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
  }

  .profile-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #00c6ff;
  }

  .profile-info h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }

  .profile-info p {
    margin: 5px 0;
    color: #555;
  }

  .profile-details {
    margin: 20px 0;
  }

  .profile-details h3 {
    margin: 20px 0 10px 0;
    font-size: 20px;
    color: #140D42;
    border-bottom: 2px solid #00c6ff;
    padding-bottom: 5px;
  }

  .profile-row {
    display: flex;
    margin: 10px 0;
    padding: 10px 0;
  }

  .profile-label {
    font-weight: bold;
    width: 150px;
    color: #555;
  }

  .profile-value {
    flex: 1;
    color: #000;
  }

  .btn-group {
    display: flex;
    gap: 10px;
    margin-top: 20px;
  }

  .btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
  }

  .btn-primary {
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    color: #fff;
  }

  .btn-secondary {
    background: #f44336;
    color: #fff;
  }

  .btn:hover {
    opacity: 0.9;
  }
</style>

<div class="profile-container">
  <div class="profile-header">
    <img src="<?php echo esc_url( tossee_get_photo_url( $user->photo ) ); ?>"
         alt="Profile Photo"
         class="profile-photo">
    <div class="profile-info">
      <h2><?php echo esc_html( trim( $user->first_name . ' ' . $user->last_name ) ?: $user->username ); ?></h2>
      <p><strong>@<?php echo esc_html( $user->username ); ?></strong></p>
      <p><?php echo esc_html( $user->email ); ?></p>
    </div>
  </div>

  <div class="profile-details">
    <h3>Personal Information</h3>

    <div class="profile-row">
      <span class="profile-label">Gender:</span>
      <span class="profile-value"><?php echo esc_html( ucfirst( $user->gender ?: 'Not specified' ) ); ?></span>
    </div>

    <div class="profile-row">
      <span class="profile-label">Date of Birth:</span>
      <span class="profile-value"><?php echo esc_html( $user->dob ?: 'Not specified' ); ?></span>
    </div>

    <div class="profile-row">
      <span class="profile-label">Location:</span>
      <span class="profile-value">
        <?php
        $location = array_filter([ $user->city, $user->country ]);
        echo esc_html( implode( ', ', $location ) ?: 'Not specified' );
        ?>
      </span>
    </div>

    <?php if ( ! empty( $user->hobbies ) ) : ?>
    <div class="profile-row">
      <span class="profile-label">Hobbies:</span>
      <span class="profile-value">
        <?php
        $hobbies = tossee_parse_hobbies( $user->hobbies );
        echo esc_html( implode( ', ', array_map( 'ucfirst', $hobbies ) ) );
        ?>
      </span>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $user->about ) ) : ?>
    <h3>About Me</h3>
    <p><?php echo nl2br( esc_html( $user->about ) ); ?></p>
    <?php endif; ?>
  </div>

  <div class="btn-group">
    <a href="<?php echo home_url( '/edit-profile' ); ?>" class="btn btn-primary">Edit Profile</a>
    <a href="<?php echo home_url( '/?tossee_logout=1' ); ?>" class="btn btn-secondary">Logout</a>
  </div>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'tossee_profile', 'tossee_profile_page_shortcode' );
