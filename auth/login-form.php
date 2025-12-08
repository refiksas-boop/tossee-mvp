/* ================================
   TOSSEE – LOGIN FORM SHORTCODE
   Custom users (NO WP users)
================================ */

function tossee_login_form_shortcode() {
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

  .error-message {
    background: rgba(255,0,0,0.1);
    border: 1px solid #ff4444;
    color: #ff4444;
    padding: 12px;
    border-radius: 8px;
    margin: 10px 0;
    font-size: 14px;
  }

  .success-message {
    background: rgba(0,255,0,0.1);
    border: 1px solid #44ff44;
    color: #44ff44;
    padding: 12px;
    border-radius: 8px;
    margin: 10px 0;
    font-size: 14px;
  }

  .link {
    margin-top: 20px;
    font-size: 14px;
  }

  .link a {
    color: #00c6ff;
    text-decoration: none;
    font-weight: bold;
  }

  .link a:hover {
    text-decoration: underline;
  }
</style>

<div class="tossee-wrap">
  <img src="https://tossee.com/wp-content/uploads/2025/09/logo.png"
       alt="Tossee Logo"
       class="tossee-logo">

  <h2>LOGIN</h2>

  <?php
  // Display error messages
  if (isset($_GET['error'])) {
      $error = sanitize_text_field($_GET['error']);
      $messages = array(
          'missing_fields' => 'Please fill in all fields.',
          'invalid_credentials' => 'Invalid username/email or password.',
          'user_not_found' => 'User not found.',
          'empty_username' => 'Please enter your username or email.',
          'empty_password' => 'Please enter your password.',
      );
      $message = isset($messages[$error]) ? $messages[$error] : 'An error occurred. Please try again.';
      echo '<div class="error-message">' . esc_html($message) . '</div>';
  }

  // Display success message (e.g., after registration)
  if (isset($_GET['success'])) {
      $success = sanitize_text_field($_GET['success']);
      if ($success === 'registered') {
          echo '<div class="success-message">Registration successful! Please log in.</div>';
      }
  }
  ?>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="tossee_custom_login">

    <input type="text" name="user_login" placeholder="Username or Email" required>
    <input type="password" name="user_pass" placeholder="Password" required>

    <button type="submit" class="tossee-btn">LOGIN</button>
  </form>

  <div class="link">
    Don't have an account? <a href="<?php echo home_url('/register'); ?>">Register here</a>
  </div>
</div>

<?php
    return ob_get_clean();
}
add_shortcode('tossee_login_form', 'tossee_login_form_shortcode');
