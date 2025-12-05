<?php
/**
 * Template Name: Tossee Edit Profile
 * Edit user profile information
 */

// Require authentication
tossee_require_auth();

// Get current user
$user = tossee_get_current_user();

if (!$user) {
    wp_safe_redirect(home_url('/login'));
    exit;
}

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
  <title>Tossee – Complete Your Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet"/>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #140D42;
      color: #000;
      padding-top: 260px;
      background-image: url("https://tossee.com/wp-content/uploads/2025/09/logo.png");
      background-repeat: no-repeat;
      background-position: center 40px;
      background-size: 320px;
    }
    .wrap {
      max-width: 480px;
      margin: 0 auto 60px;
      padding: 20px;
      background: rgba(255,255,255,0.96);
      border-radius: 14px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    }
    h2 {
      text-align: center;
      margin-top: 0;
      margin-bottom: 20px;
      font-size: 24px;
      font-weight: 800;
      color: #140D42;
    }
    form label {
      display: block;
      margin: 10px 0 6px;
      font-weight: bold;
    }
    form input, form select, form textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }
    form input[readonly] { background: #eee; color: #555; }

    .checkbox-group label {
      display: inline;
      margin-right: 10px;
      font-weight: normal;
    }

    .btn {
      margin-top: 15px;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: bold;
      color: #fff;
      background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
      cursor: pointer;
    }
    .btn:hover { opacity: 0.9; }

    .profile-photo {
      margin: 15px auto;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      border: 3px solid #00c6ff;
      overflow: hidden;
      position: relative;
      background: #eee;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .profile-photo img {
      max-width: 100%;
      display: block;
      cursor: grab;
    }

    .cropper-crop-box,
    .cropper-view-box { display: none !important; }
    .cropper-modal { opacity: 0 !important; }

    .profile-photo::before {
      content: "";
      position: absolute;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      border: 2px dashed rgba(20, 13, 66, 0.6);
      top: 0;
      left: 0;
      pointer-events: none;
    }

    .back-link {
      text-align: center;
      margin-top: 15px;
    }

    .back-link a {
      color: #0072ff;
      text-decoration: none;
      font-weight: bold;
    }

    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <div class="wrap">
    <h2>Complete Your Profile</h2>

    <form id="profileForm">

      <label>First Name *</label>
      <input type="text" id="first_name" required value="<?php echo esc_attr($user->first_name); ?>">

      <label>Last Name *</label>
      <input type="text" id="last_name" required value="<?php echo esc_attr($user->last_name); ?>">

      <label>Gender *</label>
      <select id="gender" required>
        <option value="">-- Select --</option>
        <option value="male" <?php selected($user->gender, 'male'); ?>>Male</option>
        <option value="female" <?php selected($user->gender, 'female'); ?>>Female</option>
        <option value="other" <?php selected($user->gender, 'other'); ?>>Other</option>
      </select>

      <label>Date of Birth</label>
      <input type="date" id="dob" value="<?php echo esc_attr($user->dob); ?>" readonly>

      <label>Country *</label>
      <select id="country" required>
        <option value="">-- Select Country --</option>
      </select>

      <label>City *</label>
      <select id="city" required>
        <option value="">-- Select City --</option>
      </select>

      <label>Hobbies</label>
      <div class="checkbox-group">
        <?php
        $user_hobbies = tossee_parse_hobbies($user->hobbies);
        $all_hobbies = array('sports', 'music', 'travel', 'reading', 'gaming');
        foreach ($all_hobbies as $hobby) {
            $checked = in_array($hobby, $user_hobbies) ? 'checked' : '';
            echo '<label><input type="checkbox" name="hobbies" value="' . esc_attr($hobby) . '" ' . $checked . '> ' . ucfirst($hobby) . '</label>';
        }
        ?>
      </div>

      <label>About Me</label>
      <textarea id="about" rows="4"><?php echo esc_textarea($user->about); ?></textarea>

      <label>Profile Photo</label>
      <div class="profile-photo">
        <img id="previewImage" src="<?php echo esc_url(tossee_get_photo_url($user->photo)); ?>">
      </div>
      <input type="file" id="profile_picture" accept="image/*">

      <button type="submit" class="btn">Save Profile</button>

    </form>

    <div class="back-link">
      <a href="<?php echo home_url('/my-account'); ?>">← Back to Profile</a>
    </div>
  </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
/* LOAD COUNTRIES */
fetch("<?php echo get_template_directory_uri(); ?>/tossee-mvp/assets/data/countries.json")
  .then(r => r.json())
  .then(data => {
    const c = document.getElementById("country");
    const city = document.getElementById("city");

    data.forEach(row => {
      const opt = document.createElement("option");
      opt.value = row.country;
      opt.textContent = row.country;
      c.appendChild(opt);
    });

    // Set current value
    c.value = "<?php echo esc_js($user->country); ?>";

    c.addEventListener("change", () => {
      city.innerHTML = "<option value=''>-- Select City --</option>";
      const selected = data.find(x => x.country === c.value);
      if (selected && selected.cities) {
        selected.cities.forEach(ct => {
          const opt = document.createElement("option");
          opt.value = ct;
          opt.textContent = ct;
          city.appendChild(opt);
        });
      }
    });

    // Trigger change to load cities
    if (c.value) {
      c.dispatchEvent(new Event('change'));
      setTimeout(() => {
        city.value = "<?php echo esc_js($user->city); ?>";
      }, 100);
    }
  })
  .catch(err => console.error("Failed to load countries:", err));

/* CROP IMAGE */
let cropper;
const fileInput = document.getElementById("profile_picture");
const previewImage = document.getElementById("previewImage");

fileInput.addEventListener("change", e => {
  const file = e.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = event => {
    previewImage.src = event.target.result;

    if (cropper) cropper.destroy();

    cropper = new Cropper(previewImage, {
      aspectRatio: 1,
      dragMode: "move",
      viewMode: 1,
      background: false,
      autoCropArea: 1,
      movable: true,
      zoomable: true
    });
  };
  reader.readAsDataURL(file);
});

/* SAVE PROFILE (POST) */
profileForm.onsubmit = async e => {
  e.preventDefault();

  let finalPhoto = null;

  if (cropper) {
    const sq = cropper.getCroppedCanvas({ width: 300, height: 300 });
    const c = document.createElement("canvas");
    const ctx = c.getContext("2d");
    c.width = 300;
    c.height = 300;

    ctx.beginPath();
    ctx.arc(150,150,150,0,Math.PI*2);
    ctx.clip();
    ctx.drawImage(sq,0,0,300,300);

    finalPhoto = c.toDataURL("image/png");
  }

  const hobbies = [...document.querySelectorAll("input[name='hobbies']:checked")]
        .map(cb => cb.value);

  const res = await fetch("<?php echo rest_url('tossee/v1/save-profile'); ?>", {
    method: "POST",
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": wpApiSettings.nonce
    },
    body: JSON.stringify({
      first_name: first_name.value,
      last_name: last_name.value,
      gender: gender.value,
      dob: dob.value,
      country: country.value,
      city: city.value,
      about: about.value,
      hobbies,
      photo: finalPhoto
    })
  });

  const json = await res.json();

  if (json.status === "ok") {
    window.location.href = "<?php echo home_url('/my-account'); ?>";
  } else {
    alert("Failed to save profile.");
    console.log(json);
  }
};
</script>

</body>
</html>
