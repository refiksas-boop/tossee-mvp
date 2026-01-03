<?php
/* ================================
   TOSSEE – ACCOUNT PAGE SHORTCODE
   Usage: [tossee_account]
================================ */

function tossee_account_shortcode() {
    ob_start();
    ?>
    <style>
    .tossee-account-wrap {
      margin: 60px auto;
      max-width: 480px;
      text-align: center;
    }
    .tossee-account-wrap h2 {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 40px;
      background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .tossee-account-menu a {
      display: block;
      margin: 15px 0;
      font-size: 18px;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
      padding: 14px;
      border-radius: 8px;
      background: rgba(255,255,255,0.1);
      transition: background 0.3s, transform 0.2s;
      cursor: pointer;
    }
    .tossee-account-menu a:hover {
      background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
      transform: translateY(-2px);
    }

    /* === PHOTO MODAL === */
    #tosseePhotoModal {
      display: none;
      position: fixed;
      z-index: 99999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
    }
    #tosseePhotoModal img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(255,255,255,0.3);
    }
    #tosseeClosePhotoModal {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 36px;
      color: #fff;
      cursor: pointer;
      font-weight: bold;
    }

    /* Password modal */
    #tosseePasswordModal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.7);
      justify-content: center;
      align-items: center;
    }
    #tosseePasswordModal .modal-content {
      background: #fff;
      color: #000;
      padding: 30px;
      border-radius: 10px;
      width: 340px;
      text-align: left;
      position: relative;
    }
    #tosseePasswordModal h3 {
      margin-top: 0;
      margin-bottom: 20px;
      text-align: center;
      color: #140D42;
    }
    #tosseePasswordModal label {
      display: block;
      margin-top: 15px;
      margin-bottom: 5px;
      font-weight: bold;
      color: #140D42;
    }
    #tosseePasswordModal input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
    }
    #tosseePasswordModal button {
      width: 100%;
      margin-top: 20px;
      padding: 12px;
      background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
      color: #fff;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: opacity 0.3s;
    }
    #tosseePasswordModal button:hover {
      opacity: 0.9;
    }
    #tosseePasswordModal .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 28px;
      font-weight: bold;
      color: #999;
      cursor: pointer;
    }
    #tosseePasswordModal .close:hover {
      color: #000;
    }
  </style>

  <div class="tossee-account-wrap">
    <h2>Welcome, <span id="tosseeUsername">...</span> 👋</h2>

    <div class="tossee-account-menu">
      <a href="/profile">View Profile</a>
      <a id="tosseeOpenPasswordModal">Change Password</a>
      <a id="tosseeRegistrationPhoto">Registration Photo</a>
    </div>
  </div>

  <!-- PASSWORD MODAL -->
  <div id="tosseePasswordModal">
    <div class="modal-content">
      <span class="close" onclick="tosseeCloseModal()">&times;</span>
      <h3>Change Password</h3>

      <label>Current Password</label>
      <input type="password" id="tosseeCurrentPassword">

      <label>New Password</label>
      <input type="password" id="tosseeNewPassword">

      <label>Confirm New Password</label>
      <input type="password" id="tosseeConfirmPassword">

      <button onclick="tosseeSavePassword()">Save</button>
    </div>
  </div>

  <!-- PHOTO MODAL -->
  <div id="tosseePhotoModal">
    <span id="tosseeClosePhotoModal">&times;</span>
    <img id="tosseePhotoModalImg" src="" alt="Registration Photo">
  </div>

  <script>
    let TOSSEE_CURRENT_USER_ID = null;

    // Load profile and display username
    async function tosseeLoadProfile() {
      try {
        const response = await fetch("/wp-json/tossee/v1/profile", {
          credentials: "include"
        });

        const user = await response.json();

        if (user && user.username) {
          TOSSEE_CURRENT_USER_ID = user.id;
          document.getElementById("tosseeUsername").textContent = user.username || "User";
        }
      } catch (err) {
        console.error('Failed to load profile:', err);
      }
    }

    tosseeLoadProfile();

    // Open password modal
    const tosseeModal = document.getElementById("tosseePasswordModal");
    document.getElementById("tosseeOpenPasswordModal").onclick = () => {
      tosseeModal.style.display = "flex";
    };

    function tosseeCloseModal() {
      tosseeModal.style.display = "none";
      document.getElementById("tosseeCurrentPassword").value = "";
      document.getElementById("tosseeNewPassword").value = "";
      document.getElementById("tosseeConfirmPassword").value = "";
    }

    // Save password with API call
    async function tosseeSavePassword() {
      const oldP = document.getElementById("tosseeCurrentPassword").value;
      const newP = document.getElementById("tosseeNewPassword").value;
      const confirmP = document.getElementById("tosseeConfirmPassword").value;

      if (!oldP || !newP || !confirmP) {
        alert("❌ All fields are required!");
        return;
      }

      if (newP !== confirmP) {
        alert("❌ New passwords do not match!");
        return;
      }

      if (newP.length < 6) {
        alert("❌ Password too short (min 6 characters).");
        return;
      }

      try {
        const response = await fetch("/wp-json/tossee/v1/change-password", {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            current_password: oldP,
            new_password: newP
          })
        });

        const result = await response.json();

        if (!response.ok) {
          if (result.code === 'wrong_password') {
            alert("❌ Current password is incorrect!");
          } else if (result.code === 'password_too_short') {
            alert("❌ Password too short (min 6 characters).");
          } else {
            alert("❌ Failed to change password: " + (result.message || 'Unknown error'));
          }
          return;
        }

        alert("✅ Password changed successfully!");
        tosseeCloseModal();
      } catch (err) {
        console.error('Error changing password:', err);
        alert("❌ Network error. Please try again.");
      }
    }

    // Photo modal logic
    const tosseePhotoModal = document.getElementById("tosseePhotoModal");
    const tosseePhotoModalImg = document.getElementById("tosseePhotoModalImg");

    document.getElementById("tosseeRegistrationPhoto").onclick = async () => {
      if (!TOSSEE_CURRENT_USER_ID) {
        alert("Please wait, loading user data...");
        return;
      }

      try {
        const response = await fetch("/wp-json/tossee/v1/photo", {
          credentials: "include"
        });

        if (!response.ok) {
          alert("Photo unavailable.");
          return;
        }

        const data = await response.json();

        if (data.photo) {
          tosseePhotoModalImg.src = data.photo;
          tosseePhotoModal.style.display = "flex";
        } else {
          alert("Photo not found.");
        }
      } catch (err) {
        console.error('Error loading photo:', err);
        alert("Failed to load photo.");
      }
    };

    document.getElementById("tosseeClosePhotoModal").onclick = () => {
      tosseePhotoModal.style.display = "none";
    };

    window.onclick = (e) => {
      if (e.target === tosseePhotoModal) {
        tosseePhotoModal.style.display = "none";
      }
      if (e.target === tosseeModal) {
        tosseeCloseModal();
      }
    };
  </script>
    <?php
    return ob_get_clean();
}

add_shortcode('tossee_account', 'tossee_account_shortcode');

// TEST shortcode
function tossee_test_shortcode() {
    return '<h1 style="color: red;">🎯 SHORTCODE WORKS!</h1>';
}
add_shortcode('tossee_test', 'tossee_test_shortcode');
