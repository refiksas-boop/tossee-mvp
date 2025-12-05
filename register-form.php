/* ================================
   TOSSEE – REGISTRATION SHORTCODE
   Custom users (NO WP users)
================================ */

function tossee_register_form_shortcode() {
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

  /* Forma + logotipas ~460px */
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

  video, canvas, #photoPreview {
    width: 100%;
    border-radius: 8px;
    margin: 10px 0;
  }

  /* ==== Modal – tokio pat pločio kaip forma ==== */
  .tossee-modal {
    display: none;
    position: fixed;
    z-index: 999;
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
    width: 460px;
    max-width: 90%;
    background: #fff;
    color: #000;
    padding: 25px;
    border-radius: 12px;
    text-align: left;
    max-height: 85vh;
    overflow-y: auto;
  }

  .tossee-modal-content h2 {
    font-size: 30px;
    text-align:center;
    font-weight: 900;
    background: linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 10px;
  }

  .tossee-modal-content h3 {
    margin-top: 25px;
    font-size: 22px;
    font-weight: bold;
    text-align: center;
  }

  .tossee-modal-content ul li {
    margin-bottom: 10px;
    font-size: 16px;
    line-height: 1.4;
  }

  .tossee-confirm-btn {
    background: linear-gradient(90deg,#9b59b6,#3498db);
    color:#fff;
    font-weight:bold;
    padding:16px;
    border:none;
    width:100%;
    border-radius:6px;
    margin-top:25px;
    cursor:pointer;
    font-size:18px;
  }

  .tossee-confirm-btn:disabled {
    opacity:0.5;
    cursor:not-allowed;
  }
</style>

<div class="tossee-wrap">
  <img src="https://tossee.com/wp-content/uploads/2025/09/logo.png"
       alt="Tossee Logo"
       class="tossee-logo">

  <h2>REGISTER</h2>

  <form id="tossee-register-form"
      method="post"
      action="https://tossee.com/wp-admin/admin-post.php"


    <input type="hidden" name="action" value="tossee_custom_register">
    <input type="hidden" name="redirect_to" value="https://tossee.com/chat">

    <input type="text" name="user_login" placeholder="Username" required>
    <input type="email" name="user_email" placeholder="Email" required>
    <input type="password" name="user_pass" placeholder="Password" required>
    <input type="date" id="user_dob" name="dob" required>

    <button type="button" id="enableCamera" class="tossee-btn">ENABLE CAMERA</button>
    <video id="video" autoplay playsinline style="display:none;"></video>
    <button type="button" id="takePhoto" class="tossee-btn" style="display:none;">TAKE PHOTO</button>
    <canvas id="canvas" style="display:none;"></canvas>
    <img id="photoPreview" style="display:none;"/>
    <input type="hidden" name="photo" id="photoData">

    <button type="submit" class="tossee-btn">REGISTER</button>
  </form>
</div>

<!-- ========= TERMS MODAL ========= -->
<div id="rulesModal" class="tossee-modal">
  <div class="tossee-modal-content">

    <h2>TERMS & CONDITIONS</h2>
    <p>Tossee is a platform for meeting new people and chatting. By continuing, you agree to follow the rules below and understand how we enforce them.</p>

    <h3>General Rules</h3>
    <ul>
      <li>18+ only. You must be at least 18 years old to register or use Tossee.</li>
      <li>You are responsible for your actions and for all content you share.</li>
      <li>Illegal, harmful, or offensive content is prohibited.</li>
      <li>No spam, scams, or impersonation.</li>
      <li>Respect privacy. Do not publish someone else’s private information without consent.</li>
      <li>Moderation & enforcement. We may warn, restrict, suspend, or permanently ban accounts.</li>
      <li>Liability. Users are solely responsible for their own behavior.</li>
    </ul>

    <h3>Minors</h3>
    <ul>
      <li>Users under 18 are strictly prohibited.</li>
      <li>Do not misrepresent your age; suspected minor accounts may be deleted.</li>
      <li>If an adult and a minor engage in illegal behavior, we may notify authorities.</li>
    </ul>

    <h3>Photo Verification</h3>
    <ul>
      <li>All users must take a clear face photo during registration.</li>
      <li>The photo is used only for age verification and prevention of underage use.</li>
      <li>Your photo will never be publicly displayed or shared with other users.</li>
      <li>Accounts without a valid photo may be suspended or deleted.</li>
    </ul>

    <h3>Safety Tips</h3>
    <ul>
      <li>Do not share personal info.</li>
      <li>Never send money.</li>
      <li>Report abusive behavior.</li>
    </ul>

    <label>
      <input type="checkbox" id="agreeCheck"> I agree to the Terms.
    </label>

    <button id="confirmRules" class="tossee-confirm-btn" disabled>CONFIRM</button>
  </div>
</div>

<script>
const enableCamera  = document.getElementById("enableCamera");
const takePhoto     = document.getElementById("takePhoto");
const video         = document.getElementById("video");
const canvas        = document.getElementById("canvas");
const photoPreview  = document.getElementById("photoPreview");
const photoData     = document.getElementById("photoData");
const regForm       = document.getElementById("tossee-register-form");
const modal         = document.getElementById("rulesModal");
const confirmRules  = document.getElementById("confirmRules");

/* RESET */
video.style.display        = "none";
takePhoto.style.display    = "none";
photoPreview.style.display = "none";

/* Kamera ON */
enableCamera.onclick = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = "block";
        takePhoto.style.display = "block";
        photoPreview.style.display = "none";
    } catch (err) {
        alert("Camera access denied.");
    }
};

/* Foto darymas */
takePhoto.onclick = () => {
    const ctx = canvas.getContext("2d");
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const dataURL = canvas.toDataURL("image/png");
    photoData.value = dataURL;
    photoPreview.src = dataURL;

    const stream = video.srcObject;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }

    video.style.display = "none";
    takePhoto.style.display = "none";
    photoPreview.style.display = "block";
};

/* Submit – tikrinam amžių ir foto */
regForm.addEventListener("submit", function(e) {

    const dob = new Date(document.getElementById("user_dob").value);
    const now = new Date();
    let age = now.getFullYear() - dob.getFullYear();
    const m = now.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < dob.getDate())) age--;

    if (age < 18) {
        e.preventDefault();
        alert("You must be 18+ to register.");
        return;
    }

    if (!photoData.value) {
        e.preventDefault();
        alert("Please take a photo.");
        return;
    }

    // Jei viskas OK — stabdom submit ir atidarom modal
    e.preventDefault();
    modal.style.display = "flex";
});

/* Terms checkbox */
document.getElementById("agreeCheck").onchange = (e) => {
    confirmRules.disabled = !e.target.checked;
};

/* Confirm Terms -> real submit */
confirmRules.onclick = () => {
    modal.style.display = "none";
    regForm.submit();
};
								
/* TESTAS — PATIKRINTI AR SUBMIT VEIKIA */
regForm.addEventListener("submit", () => {
    alert("FORMA SUBMITINASI");
});
								
/* ========= ERROR POPUP ========= */
function showError(msg) {
    const modalErr = document.createElement("div");
    modalErr.style = `
        position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.8); display:flex;
        justify-content:center; align-items:center; z-index:9999;
    `;
    modalErr.innerHTML = `
        <div style="
            background:#fff; color:#000; padding:25px;
            border-radius:12px; max-width:340px; width:90%;
            text-align:center; font-size:18px;">
            <h2 style="margin-bottom:10px; color:#0072ff;">Error</h2>
            <p>${msg}</p>
            <button id="errBtn" style="
                margin-top:15px; padding:10px 20px; border:none;
                border-radius:8px; background:linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
                color:white; cursor:pointer; font-weight:bold;">
                OK
            </button>
        </div>
    `;
    document.body.appendChild(modalErr);
    document.getElementById("errBtn").onclick = () => modalErr.remove();
}

// check GET param from PHP redirect (?error=email_exists)
const params = new URLSearchParams(window.location.search);
if (params.get("error") === "email_exists") {
    showError("This email is already registered.");
}
</script>
<?php
    return ob_get_clean();
}
add_shortcode('tossee_register_form', 'tossee_register_form_shortcode');
						
