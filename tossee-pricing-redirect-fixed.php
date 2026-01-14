<?php
defined('ABSPATH') || exit;

/**
 * SHORTCODE: [pricing_redirect]
 * FIXED: Debug + localStorage sync
 */
add_shortcode('pricing_redirect', 'tossee_pricing_redirect_shortcode');

function tossee_pricing_redirect_shortcode() {
    $uid = isset($_GET['uid']) ? sanitize_text_field(wp_unslash($_GET['uid'])) : '';

    ob_start();
    ?>
    <style>
      .tossee-pricing{
        min-height:100vh;
        font-family:Arial,sans-serif;
        background:#140D42;
        color:#fff;
        text-align:center;
        padding:240px 0 40px;
        background-image:url("https://tossee.com/wp-content/uploads/2025/09/logo.png");
        background-repeat:no-repeat;
        background-position:center 40px;
        background-size:260px;
      }
      .tossee-pricing .wrap{max-width:1100px;margin:0 auto;padding:20px;}
      .tossee-pricing h2{font-size:26px;font-weight:800;margin:0 0 10px;color:#00c6ff;}
      .tossee-pricing .sub{font-size:15px;opacity:.9;margin-bottom:25px;}
      .tossee-pricing .plans{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
        gap:16px;
        max-width:1100px;
        margin:0 auto;
      }
      .tossee-pricing .plan{
        background:rgba(255,255,255,0.10);
        border-radius:14px;
        padding:20px;
        box-shadow:0 6px 18px rgba(0,0,0,.35);
        position:relative;
        text-align:left;
      }
      .tossee-pricing .plan h3{margin:0;font-size:18px;color:#a6d9ff;}
      .tossee-pricing .price{font-size:26px;font-weight:800;margin:10px 0 12px;}
      .tossee-pricing .small{font-size:13px;opacity:.85;margin-bottom:12px;}
      .tossee-pricing .plan ul{list-style:none;padding:0;margin:0;font-size:13px;line-height:1.6;}
      .tossee-pricing .plan li{margin:6px 0;}
      .tossee-pricing .plan button{
        margin-top:16px;width:100%;padding:12px;border:none;border-radius:10px;
        font-size:15px;font-weight:800;color:#fff;
        background:linear-gradient(45deg,#a64dff,#00c6ff,#0072ff);
        cursor:pointer;
      }
      .tossee-pricing .plan button:hover{opacity:.92}
      .tossee-pricing .badge{
        position:absolute;top:12px;right:12px;
        font-size:11px;font-weight:900;padding:6px 10px;border-radius:999px;
        background:rgba(255,255,255,0.16);
        color:#fff;border:1px solid rgba(255,255,255,0.18);
      }
      .tossee-pricing .footer-note{margin-top:18px;font-size:13px;opacity:.75;}
      /* DEBUG BOX */
      .debug-box{
        position:fixed;top:10px;right:10px;
        background:rgba(0,0,0,0.9);padding:15px;border-radius:8px;
        font-size:11px;text-align:left;max-width:300px;
        z-index:999999;border:2px solid #0072ff;
      }
      .debug-box h4{margin:0 0 8px;color:#00c6ff;font-size:13px;}
      .debug-box .line{margin:4px 0;word-break:break-all;}
      .debug-box .label{color:#a6d9ff;font-weight:bold;}
      @media (max-width:768px){
        .tossee-pricing{padding-top:140px;background-position:center 20px;background-size:180px;}
        .tossee-pricing .plans{grid-template-columns:1fr;}
        .debug-box{font-size:10px;padding:10px;max-width:200px;}
      }
    </style>

    <!-- DEBUG BOX -->
    <div class="debug-box" id="debugBox">
      <h4>🔍 Debug Info</h4>
      <div class="line"><span class="label">PHP UID:</span> <span id="phpUid">checking...</span></div>
      <div class="line"><span class="label">URL UID:</span> <span id="urlUid">checking...</span></div>
      <div class="line"><span class="label">localStorage:</span> <span id="localUid">checking...</span></div>
      <div class="line"><span class="label">Final UID:</span> <span id="finalUid">checking...</span></div>
    </div>

    <section class="tossee-pricing">
      <div class="wrap">
        <h2>You've used your free time</h2>
        <div class="sub">Stay longer and keep chatting</div>

        <div class="plans">

          <!-- FREE (INFO ONLY, NO BUTTON) -->
          <div class="plan">
            <div class="badge">FREE</div>
            <h3>Free Access</h3>
            <div class="price">30 min</div>
            <div class="small">per day</div>
            <ul>
              <li>New users get 30 minutes daily</li>
              <li>Cooldown on NEXT</li>
              <li>No priority matching</li>
              <li>No reconnect</li>
            </ul>
            <div class="small" style="margin-top:16px;opacity:.9;">
              Your free daily time is already included.
            </div>
          </div>

          <!-- +30 -->
          <div class="plan">
            <div class="badge">ADD-ON</div>
            <h3>+30 Minutes</h3>
            <div class="price">€0.99</div>
            <div class="small">One-time</div>
            <ul>
              <li>Instant +30 min</li>
              <li>One-time purchase</li>
              <li>No commitment</li>
              <li>Keep chatting now</li>
            </ul>
            <button type="button" onclick="tosseeBuyPlan('addon_30min')">Add 30 min</button>
          </div>

          <!-- 24H -->
          <div class="plan">
            <div class="badge">24H</div>
            <h3>24h Unlimited</h3>
            <div class="price">€1.99</div>
            <div class="small">Unlimited for 24 hours</div>
            <ul>
              <li>Unlimited chat</li>
              <li>No cooldown</li>
              <li>Priority matching</li>
              <li>Reconnect</li>
            </ul>
            <button type="button" onclick="tosseeBuyPlan('unlimited_24h')">Unlock 24h</button>
          </div>

          <!-- 7 DAYS -->
          <div class="plan">
            <div class="badge">7 DAYS</div>
            <h3>7 Days Unlimited</h3>
            <div class="price">€9.99</div>
            <div class="small">Unlimited for 7 days</div>
            <ul>
              <li>Unlimited chat</li>
              <li>No cooldown</li>
              <li>Priority matching</li>
              <li>Best for active users</li>
            </ul>
            <button type="button" onclick="tosseeBuyPlan('unlimited_7d')">Unlock 7 Days</button>
          </div>

          <!-- MONTHLY -->
          <div class="plan">
            <div class="badge">MONTHLY</div>
            <h3>Monthly Unlimited</h3>
            <div class="price">€19.99</div>
            <div class="small">Unlimited for 30 days</div>
            <ul>
              <li>Unlimited chat</li>
              <li>No cooldown</li>
              <li>Priority matching</li>
              <li>Best for regular users</li>
            </ul>
            <button type="button" onclick="tosseeBuyPlan('unlimited_30d')">Unlock Month</button>
          </div>

        </div>

        <div class="footer-note">
          Most users choose <strong>24h Unlimited</strong> after finding a great match.
        </div>
      </div>
    </section>

    <script>
    (function(){
      const API_URL  = 'https://tossee.com/chat/api';
      const CHAT_URL = 'https://chat.tossee.com';
      const PHP_UID  = <?php echo wp_json_encode($uid); ?>;

      // ===== DEBUG START =====
      console.log('=== PRICING DEBUG START ===');
      console.log('PHP_UID from server:', PHP_UID);
      console.log('localStorage tossee_id:', localStorage.getItem('tossee_id'));

      const params = new URLSearchParams(window.location.search);
      const urlUid = params.get('uid');
      console.log('URL uid parameter:', urlUid);

      // Update debug box
      document.getElementById('phpUid').textContent = PHP_UID || '❌ missing';
      document.getElementById('urlUid').textContent = urlUid || '❌ missing';
      document.getElementById('localUid').textContent = localStorage.getItem('tossee_id') || '❌ missing';
      // ===== DEBUG END =====

      // Išsaugom PHP_UID jei egzistuoja
      if (PHP_UID) {
        localStorage.setItem('tossee_id', PHP_UID);
        console.log('✅ Saved PHP_UID to localStorage');
      }

      // Išsaugom URL uid jei egzistuoja
      if (urlUid) {
        localStorage.setItem('tossee_id', urlUid);
        console.log('✅ Saved URL uid to localStorage');
      }

      function getUserId(){
        // 1. Pirmiausia bandome iš URL
        const params = new URLSearchParams(window.location.search);
        const urlUid = params.get('uid');
        if (urlUid) {
          console.log('✅ getUserId: using URL uid:', urlUid);
          localStorage.setItem('tossee_id', urlUid);
          return urlUid;
        }

        // 2. Jei nėra URL, bandome iš localStorage
        const localUid = localStorage.getItem('tossee_id');
        if (localUid) {
          console.log('✅ getUserId: using localStorage uid:', localUid);
          return localUid;
        }

        // 3. Jei nėra niekur, grąžinam null
        console.error('❌ getUserId: NO UID FOUND');
        return null;
      }

      // Gauname final UID
      const finalUid = getUserId();
      document.getElementById('finalUid').textContent = finalUid || '❌ MISSING!';
      console.log('Final UID for checkout:', finalUid);

      // Jei nėra UID, parodom warning
      if (!finalUid) {
        console.error('❌ NO UID DETECTED! User needs to login again.');
        alert('⚠️ Session expired. Please start from chat page.');
        // Nukreipiam į chat puslapį
        setTimeout(() => {
          window.location.href = CHAT_URL;
        }, 2000);
      }

      window.tosseeBuyPlan = async function(planKey){
        const uid = getUserId();
        console.log('tosseeBuyPlan called with uid:', uid, 'plan:', planKey);

        if(!uid){
          console.error('❌ Missing uid in tosseeBuyPlan');
          alert('Missing user id. Please log in again.');
          window.location.href = CHAT_URL;
          return;
        }

        try{
          console.log('📤 Sending checkout request:', { uid, plan: planKey });
          const res = await fetch(API_URL + '/create-checkout.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ uid: uid, plan: planKey })
          });
          const data = await res.json();
          console.log('📥 Checkout response:', data);

          if (data && data.error) {
            console.error('❌ Checkout error:', data.error);
            return alert('Error: ' + data.error);
          }
          if (data && data.url) {
            console.log('✅ Redirecting to checkout:', data.url);
            return window.location.href = data.url;
          }

          console.error('❌ No checkout URL in response');
          alert('Failed to create checkout session');
        }catch(e){
          console.error('❌ Network error:', e);
          alert('Network error. Please try again.');
        }
      };

      // Payment status check
      const paymentStatus = params.get('payment');
      if (paymentStatus === 'success'){
        console.log('✅ Payment success detected');
        alert('Payment successful! Redirecting to chat...');
        const uid = getUserId();
        if (uid) {
          setTimeout(() => {
            window.location.href = CHAT_URL + '?uid=' + encodeURIComponent(uid);
          }, 1200);
        }
      } else if (paymentStatus === 'cancelled'){
        console.log('⚠️ Payment cancelled');
        alert('Payment cancelled. Choose a plan to continue.');
      }

      console.log('=== PRICING DEBUG END ===');
    })();
    </script>
    <?php
    return ob_get_clean();
}

/**
 * FORCE: visada rodyk pricing šiame puslapyje net jei Elementor Canvas tuščias
 */
add_filter('the_content', function ($content) {
    if (!is_page()) return $content;
    global $post;
    if ($post && $post->post_name === 'pricing-plans') {
        return do_shortcode('[pricing_redirect]');
    }
    return $content;
}, 9999);
