<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tossee - Random Video Chat</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        video { width: 100%; max-width: 400px; border: 2px solid #333; background: #000; }
        .video-container { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
        button { padding: 10px 20px; font-size: 16px; margin: 5px; cursor: pointer; }
        #status { padding: 15px; margin: 15px 0; border-radius: 5px; font-weight: bold; }
        .waiting { background: #fff3cd; color: #856404; }
        .matched { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        #debug { margin-top: 20px; padding: 10px; background: #f5f5f5; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>Tossee - Random Video Chat</h1>

    <div id="status" class="waiting">Not connected</div>

    <div class="video-container">
        <div>
            <h3>Your Video</h3>
            <video id="localVideo" autoplay muted playsinline></video>
        </div>
        <div>
            <h3>Stranger's Video</h3>
            <video id="remoteVideo" autoplay playsinline></video>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button id="startBtn">Start Chat</button>
        <button id="nextBtn" disabled>Next Stranger</button>
        <button id="stopBtn" disabled>Stop</button>
    </div>

    <div id="debug"></div>

    <!-- PHP INJECTS USER ID -->
    <?php
    // Include shared session configuration
    require_once 'session_config.php';

    // Require user to be logged in
    requireLogin('https://tossee.com/login.html');

    // Get user ID
    $tossee_id = getUserId();
    ?>

    <script>
        // ============================================
        // CONFIGURATION
        // ============================================
        const N8N_MATCHING_WEBHOOK = "YOUR_N8N_MATCHING_WEBHOOK_URL"; // TODO: Replace with your URL
        const N8N_SIGNALING_WEBHOOK = "YOUR_N8N_SIGNALING_WEBHOOK_URL"; // TODO: Replace with your URL

        // Get user ID from PHP session
        const userId = <?php echo json_encode($tossee_id); ?>;

        // ============================================
        // STATE
        // ============================================
        let localStream = null;
        let peerConnection = null;
        let currentRoomId = null;
        let pollingInterval = null;
        let signalingInterval = null;
        let isInitiator = false;

        // ============================================
        // DOM ELEMENTS
        // ============================================
        const localVideo = document.getElementById("localVideo");
        const remoteVideo = document.getElementById("remoteVideo");
        const startBtn = document.getElementById("startBtn");
        const nextBtn = document.getElementById("nextBtn");
        const stopBtn = document.getElementById("stopBtn");
        const statusDiv = document.getElementById("status");
        const debugDiv = document.getElementById("debug");

        // ============================================
        // WEBRTC CONFIGURATION
        // ============================================
        const ICE_SERVERS = {
            iceServers: [
                { urls: "stun:stun.l.google.com:19302" },
                { urls: "stun:stun1.l.google.com:19302" }
            ]
        };

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        function updateStatus(message, type = "waiting") {
            statusDiv.textContent = message;
            statusDiv.className = type;
            log(message);
        }

        function log(message) {
            const timestamp = new Date().toLocaleTimeString();
            debugDiv.innerHTML += `[${timestamp}] ${message}<br>`;
            debugDiv.scrollTop = debugDiv.scrollHeight;
            console.log(message);
        }

        async function fetchAPI(url, body) {
            try {
                log(`🔄 Sending request to: ${url}`);
                log(`📤 Request body: ${JSON.stringify(body)}`);

                const response = await fetch(url, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(body)
                });

                log(`📥 Response status: ${response.status} ${response.statusText}`);

                const text = await response.text();
                log(`📥 Response length: ${text.length} bytes`);

                if (!text || text.trim() === '') {
                    log("❌ ERROR: Empty response from webhook!");
                    log("💡 TIP: Check your n8n workflow:");
                    log("   1. Is the workflow ACTIVE?");
                    log("   2. Is 'Respond to Webhook' node connected?");
                    log("   3. Does the workflow have any errors?");
                    return { ok: false, error: "Empty response from webhook" };
                }

                try {
                    const json = JSON.parse(text);
                    log(`✅ Valid JSON received: ${JSON.stringify(json).substring(0, 100)}...`);
                    return json;
                } catch (parseError) {
                    log(`❌ ERROR: Invalid JSON in response`);
                    log(`📄 Response preview: ${text.substring(0, 200)}`);
                    return { ok: false, error: `Invalid JSON: ${parseError.message}` };
                }

            } catch (error) {
                log("❌ Network Error: " + error.message);
                log("💡 TIP: Check your webhook URL is correct");
                return { ok: false, error: error.message };
            }
        }

        // ============================================
        // MATCHING LOGIC
        // ============================================
        async function joinQueue() {
            updateStatus("Joining queue...", "waiting");
            const result = await fetchAPI(N8N_MATCHING_WEBHOOK, {
                user: userId,
                action: "join"
            });

            if (!result.ok) {
                updateStatus("Error: " + result.message, "error");
                return;
            }

            if (result.status === "matched") {
                handleMatch(result);
            } else if (result.status === "waiting") {
                updateStatus(`Waiting for stranger... (Position: ${result.position})`, "waiting");
                startPolling();
            }
        }

        function startPolling() {
            pollingInterval = setInterval(async () => {
                const result = await fetchAPI(N8N_MATCHING_WEBHOOK, {
                    user: userId,
                    action: "check"
                });

                if (result.status === "matched") {
                    stopPolling();
                    handleMatch(result);
                } else if (result.status === "waiting") {
                    updateStatus(`Waiting for stranger... (Position: ${result.position})`, "waiting");
                }
            }, 2000);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        async function handleMatch(matchData) {
            log("Match found! Room: " + matchData.roomId);
            currentRoomId = matchData.roomId;

            isInitiator = userId < matchData.peer;

            updateStatus("Connected to stranger! Setting up video...", "matched");

            await setupWebRTC();
            startSignaling();

            nextBtn.disabled = false;
            stopBtn.disabled = false;
            startBtn.disabled = true;
        }

        // ============================================
        // WEBRTC SETUP
        // ============================================
        async function setupWebRTC() {
            try {
                peerConnection = new RTCPeerConnection(ICE_SERVERS);

                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

                peerConnection.ontrack = (event) => {
                    log("Received remote track");
                    remoteVideo.srcObject = event.streams[0];
                    updateStatus("Connected! Enjoy your chat!", "matched");
                };

                peerConnection.onicecandidate = async (event) => {
                    if (event.candidate) {
                        log("Sending ICE candidate");
                        await fetchAPI(N8N_SIGNALING_WEBHOOK, {
                            roomId: currentRoomId,
                            type: "candidate",
                            user: userId,
                            data: event.candidate
                        });
                    }
                };

                peerConnection.onconnectionstatechange = () => {
                    log("Connection state: " + peerConnection.connectionState);
                    if (peerConnection.connectionState === "failed") {
                        updateStatus("Connection failed. Click 'Next Stranger' to try again.", "error");
                    }
                };

                if (isInitiator) {
                    log("Creating offer (initiator)");
                    const offer = await peerConnection.createOffer();
                    await peerConnection.setLocalDescription(offer);

                    await fetchAPI(N8N_SIGNALING_WEBHOOK, {
                        roomId: currentRoomId,
                        type: "offer",
                        data: offer
                    });
                }

            } catch (error) {
                log("WebRTC setup error: " + error.message);
                updateStatus("Error setting up video: " + error.message, "error");
            }
        }

        // ============================================
        // SIGNALING LOOP
        // ============================================
        function startSignaling() {
            signalingInterval = setInterval(async () => {
                const result = await fetchAPI(N8N_SIGNALING_WEBHOOK, {
                    roomId: currentRoomId,
                    type: "check"
                });

                if (!result.ok || !result.roomData) return;

                const { offers, answers, candidates } = result.roomData;

                if (!isInitiator && offers.length > 0 && peerConnection.signalingState !== "stable") {
                    const offer = offers[offers.length - 1];
                    log("Received offer, creating answer");
                    await peerConnection.setRemoteDescription(offer);
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);

                    await fetchAPI(N8N_SIGNALING_WEBHOOK, {
                        roomId: currentRoomId,
                        type: "answer",
                        data: answer
                    });
                }

                if (isInitiator && answers.length > 0 && peerConnection.signalingState !== "stable") {
                    const answer = answers[answers.length - 1];
                    log("Received answer");
                    await peerConnection.setRemoteDescription(answer);
                }

                for (const item of candidates) {
                    if (item.from !== userId) {
                        try {
                            await peerConnection.addIceCandidate(item.candidate);
                        } catch (e) {
                            // Ignore errors
                        }
                    }
                }

            }, 1000);
        }

        function stopSignaling() {
            if (signalingInterval) {
                clearInterval(signalingInterval);
                signalingInterval = null;
            }
        }

        // ============================================
        // DISCONNECT / CLEANUP
        // ============================================
        async function disconnect() {
            if (currentRoomId) {
                await fetchAPI(N8N_SIGNALING_WEBHOOK, {
                    roomId: currentRoomId,
                    type: "disconnect",
                    user: userId
                });
            }

            stopSignaling();
            stopPolling();

            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }

            remoteVideo.srcObject = null;

            currentRoomId = null;
            isInitiator = false;
        }

        // ============================================
        // BUTTON HANDLERS
        // ============================================
        startBtn.onclick = async () => {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                localVideo.srcObject = localStream;

                await joinQueue();

            } catch (error) {
                updateStatus("Camera/microphone access denied: " + error.message, "error");
            }
        };

        nextBtn.onclick = async () => {
            await disconnect();
            updateStatus("Finding next stranger...", "waiting");
            await joinQueue();
        };

        stopBtn.onclick = async () => {
            await disconnect();

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
                localVideo.srcObject = null;
            }

            updateStatus("Stopped", "waiting");
            startBtn.disabled = false;
            nextBtn.disabled = true;
            stopBtn.disabled = true;
        };

        window.addEventListener("beforeunload", () => {
            disconnect();
        });

        // Log user ID on page load
        log("Logged in as: " + userId);
    </script>
</body>
</html>
