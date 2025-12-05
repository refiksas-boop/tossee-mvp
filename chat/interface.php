<?php
/**
 * Template Name: Tossee Chat Interface
 * WebRTC video chat interface
 */

// Require authentication
tossee_require_auth();

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
  <title>Tossee - Random Video Chat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background: #140D42;
      color: #fff;
      overflow: hidden;
    }

    #app {
      width: 100vw;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Header */
    #header {
      background: rgba(0, 0, 0, 0.5);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #00c6ff;
    }

    #header .logo {
      font-size: 24px;
      font-weight: bold;
      background: linear-gradient(45deg, #a64dff, #00c6ff, #0072ff);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    #header .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    #header .btn {
      padding: 8px 16px;
      background: linear-gradient(45deg, #a64dff, #00c6ff, #0072ff);
      border: none;
      border-radius: 6px;
      color: #fff;
      cursor: pointer;
      text-decoration: none;
      font-weight: bold;
    }

    /* Video Container */
    #video-container {
      flex: 1;
      position: relative;
      background: #000;
    }

    #remoteVideo {
      width: 100%;
      height: 100%;
      object-fit: cover;
      background: #1a1a1a;
    }

    #localVideo {
      position: absolute;
      bottom: 20px;
      right: 20px;
      width: 200px;
      height: 150px;
      object-fit: cover;
      border: 3px solid #00c6ff;
      border-radius: 8px;
      background: #000;
    }

    /* Controls */
    #controls {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 15px;
      z-index: 10;
    }

    #controls button {
      padding: 15px 30px;
      border: none;
      border-radius: 50px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    #controls button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
    }

    .btn-next {
      background: linear-gradient(45deg, #a64dff, #00c6ff, #0072ff);
      color: #fff;
    }

    .btn-stop {
      background: #dc3545;
      color: #fff;
    }

    .btn-start {
      background: #28a745;
      color: #fff;
    }

    /* Status Overlay */
    #status-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(20, 13, 66, 0.95);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      z-index: 5;
    }

    #status-overlay h2 {
      font-size: 32px;
      margin-bottom: 20px;
    }

    #status-overlay p {
      font-size: 18px;
      color: #aaa;
    }

    .spinner {
      border: 4px solid rgba(255, 255, 255, 0.3);
      border-top: 4px solid #00c6ff;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      animation: spin 1s linear infinite;
      margin: 20px 0;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .hidden {
      display: none !important;
    }

    /* Stranger Info */
    #stranger-info {
      position: absolute;
      top: 20px;
      left: 20px;
      background: rgba(0, 0, 0, 0.7);
      padding: 15px;
      border-radius: 8px;
      z-index: 10;
    }

    #stranger-info h3 {
      margin-bottom: 8px;
      color: #00c6ff;
    }

    #stranger-info p {
      margin: 4px 0;
      font-size: 14px;
    }
  </style>
</head>

<body>

<div id="app">
  <!-- Header -->
  <div id="header">
    <div class="logo">TOSSEE</div>
    <div class="user-info">
      <span>@<?php echo esc_html($user->username); ?></span>
      <a href="<?php echo home_url('/my-account'); ?>" class="btn">My Profile</a>
      <a href="<?php echo tossee_logout_url(); ?>" class="btn">Logout</a>
    </div>
  </div>

  <!-- Video Container -->
  <div id="video-container">
    <video id="remoteVideo" autoplay playsinline></video>
    <video id="localVideo" autoplay playsinline muted></video>

    <!-- Stranger Info -->
    <div id="stranger-info" class="hidden">
      <h3>Stranger</h3>
      <p id="stranger-location"></p>
      <p id="stranger-age"></p>
    </div>

    <!-- Status Overlay -->
    <div id="status-overlay">
      <h2 id="status-text">Welcome to Tossee</h2>
      <div class="spinner hidden" id="spinner"></div>
      <p id="status-desc">Click "Start Chatting" to find a random stranger</p>
    </div>

    <!-- Controls -->
    <div id="controls">
      <button id="btnStart" class="btn-start">Start Chatting</button>
      <button id="btnNext" class="btn-next hidden">Next</button>
      <button id="btnStop" class="btn-stop hidden">Stop</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/simple-peer@9.11.1/simplepeer.min.js"></script>

<script>
// Application State
const state = {
  localStream: null,
  peer: null,
  partnerId: null,
  isSearching: false,
  isConnected: false
};

// DOM Elements
const localVideo = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const statusOverlay = document.getElementById('status-overlay');
const statusText = document.getElementById('status-text');
const statusDesc = document.getElementById('status-desc');
const spinner = document.getElementById('spinner');
const strangerInfo = document.getElementById('stranger-info');
const strangerLocation = document.getElementById('stranger-location');
const strangerAge = document.getElementById('stranger-age');

const btnStart = document.getElementById('btnStart');
const btnNext = document.getElementById('btnNext');
const btnStop = document.getElementById('btnStop');

// Initialize
async function init() {
  try {
    // Get user media
    state.localStream = await navigator.mediaDevices.getUserMedia({
      video: true,
      audio: true
    });

    localVideo.srcObject = state.localStream;

    console.log('Camera and microphone initialized');
  } catch (err) {
    console.error('Error accessing media devices:', err);
    alert('Please allow camera and microphone access to use Tossee.');
  }
}

// Update UI
function updateUI(status, description = '') {
  statusText.textContent = status;
  statusDesc.textContent = description;

  switch(status) {
    case 'Searching for a stranger...':
      statusOverlay.classList.remove('hidden');
      spinner.classList.remove('hidden');
      btnStart.classList.add('hidden');
      btnNext.classList.add('hidden');
      btnStop.classList.remove('hidden');
      strangerInfo.classList.add('hidden');
      break;

    case 'Connecting...':
      statusOverlay.classList.remove('hidden');
      spinner.classList.remove('hidden');
      break;

    case 'Connected':
      statusOverlay.classList.add('hidden');
      spinner.classList.add('hidden');
      btnNext.classList.remove('hidden');
      btnStop.classList.remove('hidden');
      strangerInfo.classList.remove('hidden');
      break;

    case 'Disconnected':
      statusOverlay.classList.remove('hidden');
      spinner.classList.add('hidden');
      btnStart.classList.remove('hidden');
      btnNext.classList.add('hidden');
      btnStop.classList.add('hidden');
      strangerInfo.classList.add('hidden');
      break;
  }
}

// Start searching for partner
async function startSearching() {
  if (!state.localStream) {
    await init();
  }

  state.isSearching = true;
  updateUI('Searching for a stranger...', 'Please wait while we find you a chat partner');

  // Start polling for partner
  pollForPartner();
}

// Poll for partner (MVP approach using AJAX)
async function pollForPartner() {
  if (!state.isSearching) return;

  try {
    const response = await fetch('<?php echo rest_url('tossee/v1/find-partner'); ?>', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
      }
    });

    const data = await response.json();

    if (data.found) {
      // Partner found!
      state.partnerId = data.partner.tossee_id;
      connectToPartner(data.partner, data.initiator);
    } else {
      // Keep polling
      setTimeout(pollForPartner, 2000);
    }
  } catch (err) {
    console.error('Error polling for partner:', err);
    setTimeout(pollForPartner, 3000);
  }
}

// Connect to partner using WebRTC
function connectToPartner(partner, isInitiator) {
  updateUI('Connecting...', `Connecting to ${partner.country || 'stranger'}...`);

  // Display partner info
  strangerLocation.textContent = partner.country && partner.city
    ? `📍 ${partner.city}, ${partner.country}`
    : '📍 Unknown location';
  strangerAge.textContent = partner.age ? `👤 ${partner.age} years old` : '';

  // Create SimplePeer connection
  state.peer = new SimplePeer({
    initiator: isInitiator,
    stream: state.localStream,
    trickle: false
  });

  state.peer.on('signal', async (data) => {
    // Send signal to partner via API
    await fetch('<?php echo rest_url('tossee/v1/signal'); ?>', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
      },
      body: JSON.stringify({
        partner_id: state.partnerId,
        signal: data
      })
    });

    // Poll for partner's signal
    pollForSignal();
  });

  state.peer.on('stream', (remoteStream) => {
    remoteVideo.srcObject = remoteStream;
    state.isConnected = true;
    updateUI('Connected');
  });

  state.peer.on('error', (err) => {
    console.error('Peer error:', err);
    disconnect('Connection error. Please try again.');
  });

  state.peer.on('close', () => {
    disconnect('Stranger disconnected');
  });
}

// Poll for partner's WebRTC signal
async function pollForSignal() {
  if (!state.peer || state.isConnected) return;

  try {
    const response = await fetch(`<?php echo rest_url('tossee/v1/get-signal'); ?>?partner_id=${state.partnerId}`, {
      credentials: 'include',
      headers: {
        'X-WP-Nonce': wpApiSettings.nonce
      }
    });

    const data = await response.json();

    if (data.signal) {
      state.peer.signal(data.signal);
    } else if (!state.isConnected) {
      setTimeout(pollForSignal, 1000);
    }
  } catch (err) {
    console.error('Error polling for signal:', err);
    setTimeout(pollForSignal, 2000);
  }
}

// Disconnect from current partner
function disconnect(message = 'Disconnected') {
  state.isSearching = false;
  state.isConnected = false;

  if (state.peer) {
    state.peer.destroy();
    state.peer = null;
  }

  state.partnerId = null;
  remoteVideo.srcObject = null;

  updateUI('Disconnected', message);
}

// Next partner
function nextPartner() {
  disconnect();
  startSearching();
}

// Stop chatting
function stopChatting() {
  disconnect('You stopped chatting');
}

// Event listeners
btnStart.addEventListener('click', startSearching);
btnNext.addEventListener('click', nextPartner);
btnStop.addEventListener('click', stopChatting);

// Initialize on load
window.addEventListener('load', init);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
  if (state.peer) {
    state.peer.destroy();
  }
  if (state.localStream) {
    state.localStream.getTracks().forEach(track => track.stop());
  }
});
</script>

</body>
</html>
