<?php
/**
 * Template Name: Pricing Plans
 * Template Post Type: page
 */

// Start session if not started
if (!session_id()) {
    session_start();
}

// Get UID from URL parameter or session
$uid = isset($_GET['uid']) ? sanitize_text_field($_GET['uid']) : '';
if (empty($uid) && isset($_SESSION['tossee_id'])) {
    $uid = $_SESSION['tossee_id'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tossee – Continue Chatting</title>
<style>
body{
  margin:0;
  font-family:Arial,sans-serif;
  background:#140D42;
  color:#fff;
  text-align:center;
  padding-top:180px;
  background-image:url("https://tossee.com/wp-content/uploads/2025/09/logo.png");
  background-repeat:no-repeat;
  background-position:center 40px;
  background-size:260px;
}
.wrap{
  max-width:1100px;
  margin:0 auto;
  padding:20px;
}

/* UID DISPLAY */
.uid-box{
  background:rgba(0,198,255,0.15);
  border:2px solid #00c6ff;
  border-radius:14px;
  padding:16px 24px;
  margin-bottom:30px;
  max-width:500px;
  margin-left:auto;
  margin-right:auto;
}
.uid-label{
  font-size:12px;
  opacity:0.8;
  margin-bottom:6px;
}
.uid-value{
  font-family:'Courier New',monospace;
  font-size:20px;
  font-weight:800;
  color:#00c6ff;
  letter-spacing:1px;
  word-break:break-all;
}
.uid-warning{
  background:rgba(255,107,107,0.15);
  border:2px solid #ff6b6b;
  color:#ff6b6b;
}

h2{
  font-size:26px;
  font-weight:800;
  margin-bottom:10px;
  color:#00c6ff;
}
.sub{
  font-size:15px;
  opacity:.9;
  margin-bottom:25px;
}

/* GRID */
.plans{
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
  gap:16px;
  max-width:1100px;
  margin:0 auto;
}

.plan{
  background:rgba(255,255,255,0.10);
  border-radius:14px;
  padding:20px;
  box-shadow:0 6px 18px rgba(0,0,0,.35);
  position:relative;
  text-align:left;
}

.plan.primary{
  border:2px solid #00c6ff;
  background:rgba(0,198,255,0.10);
}

.plan h3{
  margin:0;
  font-size:18px;
  color:#a6d9ff;
}

.price{
  font-size:26px;
  font-weight:800;
  margin:10px 0 12px;
}

.small{
  font-size:13px;
  opacity:.85;
  margin-bottom:12px;
}

.plan ul{
  list-style:none;
  padding:0;
  margin:0;
  font-size:13px;
  line-height:1.6;
}

.plan li{ margin:6px 0; }

.plan button{
  margin-top:16px;
  width:100%;
  padding:12px;
  border:none;
  border-radius:10px;
  font-size:15px;
  font-weight:800;
  color:#fff;
  background:linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
  cursor:pointer;
}
.plan button:hover{opacity:.92}

.badge{
  position:absolute;
  top:12px;
  right:12px;
  font-size:11px;
  font-weight:900;
  padding:6px 10px;
  border-radius:999px;
  background:#00c6ff;
  color:#06111a;
}

.badge.secondary{
  background:rgba(255,255,255,0.16);
  color:#fff;
  border:1px solid rgba(255,255,255,0.18);
}

.footer-note{
  margin-top:18px;
  font-size:13px;
  opacity:.75;
}

@media (max-width: 768px) {
  body{
    padding-top:140px;
    background-position:center 20px;
    background-size:180px;
  }
  .uid-value{
    font-size:16px;
  }
  .plans{
    grid-template-columns:1fr;
  }
}
</style>
</head>

<body>
<div class="wrap">

<!-- UID DISPLAY -->
<?php if (!empty($uid)) : ?>
<div class="uid-box">
  <div class="uid-label">Your User ID</div>
  <div class="uid-value" id="uid-display"><?php echo esc_html($uid); ?></div>
</div>
<?php else : ?>
<div class="uid-box uid-warning">
  <div class="uid-label">⚠ No User ID Found</div>
  <div class="uid-value" id="uid-display">Please log in first</div>
</div>
<?php endif; ?>

<h2>You've used your free time</h2>
<div class="sub">Stay longer and keep chatting</div>

<div class="plans">

  <!-- FREE -->
  <div class="plan">
    <div class="badge secondary">FREE</div>
    <h3>Free Access</h3>
    <div class="price">30 min</div>
    <div class="small">per day</div>
    <ul>
      <li>30 minutes daily</li>
      <li>Cooldown on NEXT</li>
      <li>No priority matching</li>
      <li>No reconnect</li>
    </ul>
    <button onclick="useFree()">Continue Free</button>
  </div>

  <!-- +30 -->
  <div class="plan">
    <div class="badge secondary">ADD-ON</div>
    <h3>+30 Minutes</h3>
    <div class="price">€0.99</div>
    <div class="small">One-time</div>
    <ul>
      <li>Instant +30 min</li>
      <li>One-time purchase</li>
      <li>No commitment</li>
      <li>Keep chatting now</li>
    </ul>
    <button onclick="buyPlan('addon_30min')">Add 30 min</button>
  </div>

  <!-- CORE 24H -->
  <div class="plan primary">
    <div class="badge">BEST</div>
    <h3>24h Unlimited</h3>
    <div class="price">€1.99</div>
    <div class="small">Unlimited for 24 hours</div>
    <ul>
      <li>Unlimited chat</li>
      <li>No cooldown</li>
      <li>Priority matching</li>
      <li>Reconnect</li>
    </ul>
    <button onclick="buyPlan('unlimited_24h')">Unlock 24h</button>
  </div>

  <!-- 7 DAYS -->
  <div class="plan">
    <div class="badge secondary">POWER</div>
    <h3>7 Days Unlimited</h3>
    <div class="price">€9.99</div>
    <div class="small">Unlimited for 7 days</div>
    <ul>
      <li>Unlimited chat</li>
      <li>No cooldown</li>
      <li>Priority matching</li>
      <li>Best for active users</li>
    </ul>
    <button onclick="buyPlan('unlimited_7d')">Unlock 7 Days</button>
  </div>

  <!-- MONTHLY -->
  <div class="plan">
    <div class="badge secondary">MONTHLY</div>
    <h3>Monthly Unlimited</h3>
    <div class="price">€19.99</div>
    <div class="small">Unlimited for 30 days</div>
    <ul>
      <li>Unlimited chat</li>
      <li>No cooldown</li>
      <li>Priority matching</li>
      <li>Best for regular users</li>
    </ul>
    <button onclick="buyPlan('unlimited_30d')">Unlock Month</button>
  </div>

</div>

<div class="footer-note">
  Most users choose <strong>24h Unlimited</strong> after finding a great match.
</div>

</div>

<script>
// Configuration
const API_URL = 'https://tossee.com/chat/api';
const CHAT_URL = 'https://chat.tossee.com';

// Get user ID from PHP, URL or localStorage
function getUserId(){
  // First try to get from PHP
  const phpUid = '<?php echo esc_js($uid); ?>';
  if(phpUid){
    localStorage.setItem('tossee_id', phpUid);
    return phpUid;
  }

  // Fallback to URL parameter
  const params = new URLSearchParams(window.location.search);
  const urlUid = params.get('uid');
  if(urlUid){
    localStorage.setItem('tossee_id', urlUid);
    // Update display
    const display = document.getElementById('uid-display');
    if(display) display.textContent = urlUid;
    return urlUid;
  }

  // Fallback to localStorage
  const storedUid = localStorage.getItem('tossee_id');
  if(storedUid){
    return storedUid;
  }

  return null;
}

// Buy a plan
async function buyPlan(planKey){
  const uid = getUserId();
  if(!uid){
    alert('Please log in first');
    window.location.href = '/login';
    return;
  }

  try {
    const response = await fetch(API_URL + '/create-checkout.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({uid: uid, plan: planKey})
    });

    const data = await response.json();

    if(data.error){
      alert('Error: ' + data.error);
      return;
    }

    if(data.url){
      window.location.href = data.url;
    } else {
      alert('Failed to create checkout session');
    }
  } catch(e){
    alert('Network error. Please try again.');
    console.error(e);
  }
}

// Continue with free plan
function useFree(){
  const uid = getUserId();
  if(!uid){
    alert('Please log in first');
    window.location.href = '/login';
    return;
  }

  window.location.href = CHAT_URL + '?uid=' + uid;
}

// Check for payment status on load
window.addEventListener('DOMContentLoaded', function(){
  const params = new URLSearchParams(window.location.search);
  const paymentStatus = params.get('payment');

  if(paymentStatus === 'success'){
    alert('Payment successful! Redirecting to chat...');
    const uid = getUserId();
    if(uid){
      setTimeout(() => {
        window.location.href = CHAT_URL + '?uid=' + uid;
      }, 1500);
    }
  } else if(paymentStatus === 'cancelled'){
    alert('Payment cancelled. Choose a plan to continue.');
  }
});
</script>

</body>
</html>
