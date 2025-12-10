<?php
/**
 * TOSSEE PROFILE SHORTCODE
 * Naudojimas: [tossee_profile]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'tossee_profile', 'tossee_profile_shortcode' );

function tossee_profile_shortcode() {
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile – Tossee</title>

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
  </style>
</head>

<body>

<div class="wrap">
  <h2 id="profile-title">My Profile</h2>

  <div class="profile-photo">
    <img id="avatar"
         src="https://cdn-icons-png.flaticon.com/512/149/149071.png"
         alt="Profile photo">
  </div>

  <div class="content">
    <div class="field"><label>Username:</label> <span id="username">-</span></div>
    <div class="field"><label>Email:</label> <span id="email">-</span></div>

    <div class="field"><label>First Name:</label> <span id="first_name">-</span></div>
    <div class="field"><label>Last Name:</label> <span id="last_name">-</span></div>

    <div class="field"><label>Gender:</label> <span id="gender">-</span></div>
    <div class="field"><label>Date of Birth:</label> <span id="dob">-</span></div>

    <div class="field"><label>Country:</label> <span id="country">-</span></div>
    <div class="field"><label>City:</label> <span id="city">-</span></div>

    <div class="field"><label>Hobbies:</label> <span id="hobbies">-</span></div>
    <div class="field"><label>About Me:</label> <span id="about">-</span></div>
  </div>
</div>

<script>
async function loadProfile() {
  try {
    // GAUNAM PROFILĮ IŠ MŪSŲ API
    const response = await fetch("/wp-json/tossee/v1/profile", {
      credentials: "include"
    });

    if (!response.ok) {
      console.error("Profile HTTP error:", response.status);
      alert("Nepavyko užkrauti profilio. Prašome prisijungti.");
      return;
    }

    const profile = await response.json();
    console.log("PROFILE:", profile);

    // Avataras
    const avatar = document.getElementById("avatar");
    if (profile.photo) {
      avatar.src = profile.photo;
    }

    // USERNAME + EMAIL
    document.getElementById("username").textContent = profile.username || "-";
    document.getElementById("email").textContent    = profile.email || "-";

    // FIRST + LAST NAME
    document.getElementById("first_name").textContent = profile.first_name || "-";
    document.getElementById("last_name").textContent  = profile.last_name || "-";

    // GENDER
    document.getElementById("gender").textContent = profile.gender || "-";

    // DOB
    document.getElementById("dob").textContent = profile.dob || "-";

    // COUNTRY & CITY
    document.getElementById("country").textContent = profile.country || "-";
    document.getElementById("city").textContent    = profile.city || "-";

    // HOBBIES
    document.getElementById("hobbies").textContent = profile.hobbies || "-";

    // ABOUT
    document.getElementById("about").textContent = profile.about || "-";

    // Pavadinimas viršuje
    const fullName = `${profile.first_name || ""} ${profile.last_name || ""}`.trim();
    document.getElementById("profile-title").textContent =
      fullName ? "Profile of " + fullName : "My Profile";

  } catch (err) {
    console.error("Profile load error:", err);
    alert("Įvyko klaida kraunant profilį.");
  }
}

loadProfile();
</script>

</body>
</html>
    <?php
    return ob_get_clean();
}
