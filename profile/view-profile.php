<?php
/**
 * Template Name: Tossee Profile View
 * Displays user profile information
 */

// Require authentication
tossee_require_auth();

// Get current user
$user = tossee_get_current_user();

if (!$user) {
    wp_safe_redirect(home_url('/login'));
    exit;
}

// Parse hobbies
$hobbies = tossee_parse_hobbies($user->hobbies);
$hobbies_display = !empty($hobbies) ? implode(', ', $hobbies) : '-';

// Get display name
$display_name = tossee_get_display_name($user);

// Get photo URL
$photo_url = tossee_get_photo_url($user->photo);

// Enqueue nonce for REST API
wp_localize_script('jquery', 'wpApiSettings', array(
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest')
));

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile – Tossee</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #140D42 url("https://tossee.com/wp-content/uploads/2025/09/logo.png") no-repeat center top 40px;
      background-size: 280px;
      color: #fff;
      text-align: center;
    }
    .wrap {
      max-width: 700px;
      margin: 260px auto 40px;
      padding: 0 20px;
    }
    h2 {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 30px;
      color: #00c6ff;
    }
    .profile-photo {
      width: 140px;
      height: 140px;
      border: 3px solid #00c6ff;
      border-radius: 50%;
      margin: 0 auto 25px auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .profile-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .content {
      background: rgba(0,0,0,0.3);
      padding: 20px;
      border-radius: 12px;
    }
    .field {
      margin: 12px 0;
      font-size: 17px;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      padding-bottom: 6px;
    }
    .field label {
      font-weight: bold;
      margin-right: 6px;
      color: #a6d9ff;
    }
    .buttons {
      margin-top: 20px;
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
      cursor: pointer;
      transition: .15s;
      display: inline-block;
    }
    .btn-primary {
      background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    }
    .btn-secondary {
      background: linear-gradient(45deg,#555,#777);
    }
    .btn:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }
  </style>
</head>

<body>

<div class="wrap">
  <h2><?php echo esc_html($display_name); ?></h2>

  <div class="profile-photo">
    <img src="<?php echo esc_url($photo_url); ?>" alt="Profile photo">
  </div>

  <div class="content">
    <div class="field"><label>Username:</label> <span><?php echo esc_html($user->username); ?></span></div>
    <div class="field"><label>Email:</label> <span><?php echo esc_html($user->email); ?></span></div>

    <div class="field"><label>First Name:</label> <span><?php echo esc_html($user->first_name ?: '-'); ?></span></div>
    <div class="field"><label>Last Name:</label> <span><?php echo esc_html($user->last_name ?: '-'); ?></span></div>

    <div class="field"><label>Gender:</label> <span><?php echo esc_html($user->gender ?: '-'); ?></span></div>
    <div class="field"><label>Date of Birth:</label> <span><?php echo esc_html($user->dob ?: '-'); ?></span></div>
    <div class="field"><label>Age:</label> <span><?php echo esc_html(tossee_calculate_age($user->dob)); ?> years</span></div>

    <div class="field"><label>Country:</label> <span><?php echo esc_html($user->country ?: '-'); ?></span></div>
    <div class="field"><label>City:</label> <span><?php echo esc_html($user->city ?: '-'); ?></span></div>

    <div class="field"><label>Hobbies:</label> <span><?php echo esc_html($hobbies_display); ?></span></div>
    <div class="field"><label>About Me:</label> <span><?php echo esc_html($user->about ?: '-'); ?></span></div>
  </div>

  <div class="buttons">
    <a href="<?php echo home_url('/edit-profile'); ?>" class="btn btn-primary">Edit Profile</a>
    <a href="<?php echo home_url('/chat'); ?>" class="btn btn-primary">Start Chatting</a>
    <a href="<?php echo tossee_logout_url(); ?>" class="btn btn-secondary">Logout</a>
  </div>
</div>

</body>
</html>
