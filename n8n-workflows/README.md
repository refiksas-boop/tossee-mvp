# N8N Video Chat Matching - Fixed Implementation

## Problema (The Problem)

Your original n8n workflows had these critical issues:

### 1. ❌ No Polling Mechanism
- User A arrives → queue empty → returns "waiting"
- **Problem:** User A never checks back for a match
- User B arrives → matches with User A → **but User A already left!**

### 2. ❌ Race Condition
- If 2 users POST simultaneously when queue empty
- Both add themselves to queue instead of matching

### 3. ❌ No Client Integration
- No frontend code to poll the matching endpoint
- No WebRTC connection logic

---

## Sprendimas (The Solution)

### Architecture Overview

```
┌─────────────┐      ┌──────────────────┐      ┌─────────────┐
│   Client A  │──────│  N8N Matching    │──────│  Client B   │
│   (Browser) │ POST │  Webhook         │ POST │  (Browser)  │
└─────────────┘      └──────────────────┘      └─────────────┘
       │                      │                        │
       │  1. action=join      │                        │
       │─────────────────────>│                        │
       │  ← status: waiting   │                        │
       │                      │                        │
       │  2. action=check     │                        │
       │─────────────────────>│                        │
       │  ← status: waiting   │  3. action=join        │
       │                      │<───────────────────────│
       │  4. action=check     │                        │
       │─────────────────────>│  ← status: matched!    │
       │  ← status: matched!  │────────────────────────>
       │     roomId: room_xyz │                        │
       │                      │                        │
       v                      v                        v
┌──────────────────────────────────────────────────────────┐
│              N8N Signaling Webhook                        │
│  - Exchange WebRTC offers/answers/ICE candidates         │
│  - Both clients poll every 1s to get peer's signals      │
└──────────────────────────────────────────────────────────┘
```

---

## N8N Workflow Setup

### Workflow 1: Matching (User Pairing)

**Webhook URL:** `https://your-n8n.app/webhook/matching`

**Node Configuration:**
1. **Webhook Node** - POST method, responds immediately
2. **Code in JavaScript Node** - Use code from `improved-matching-workflow.js`
3. **Respond to Webhook Node** - Returns JSON response

**Request Format:**
```json
{
  "user": "user_abc123",
  "action": "join"  // or "check"
}
```

**Response - Waiting:**
```json
{
  "ok": true,
  "status": "waiting",
  "position": 1,
  "message": "Added to queue. Poll with action=check every 2-3 seconds."
}
```

**Response - Matched:**
```json
{
  "ok": true,
  "status": "matched",
  "roomId": "room_a1b2c3d4",
  "users": ["user_abc123", "user_xyz789"],
  "peer": "user_xyz789"
}
```

---

### Workflow 2: Signaling (WebRTC Data Exchange)

**Webhook URL:** `https://your-n8n.app/webhook/signaling`

**Node Configuration:**
1. **Webhook1 Node** - POST method
2. **Code in JavaScript1 Node** - Use code from `improved-signaling-workflow.js`
3. **Respond to Webhook1 Node** - Returns JSON response

**Request Types:**

#### Store WebRTC Offer
```json
{
  "roomId": "room_a1b2c3d4",
  "type": "offer",
  "data": { "type": "offer", "sdp": "..." }
}
```

#### Store WebRTC Answer
```json
{
  "roomId": "room_a1b2c3d4",
  "type": "answer",
  "data": { "type": "answer", "sdp": "..." }
}
```

#### Store ICE Candidate
```json
{
  "roomId": "room_a1b2c3d4",
  "type": "candidate",
  "user": "user_abc123",
  "data": { "candidate": "...", "sdpMid": "0", "sdpMLineIndex": 0 }
}
```

#### Check for New Signaling Data
```json
{
  "roomId": "room_a1b2c3d4",
  "type": "check"
}
```

**Response:**
```json
{
  "ok": true,
  "roomData": {
    "offers": [...],
    "answers": [...],
    "candidates": [...]
  }
}
```

#### Disconnect
```json
{
  "roomId": "room_a1b2c3d4",
  "type": "disconnect",
  "user": "user_abc123"
}
```

---

## Client Implementation

See `example-client-implementation.html` for complete working example.

### Key Features

1. **Polling for Matches** - Every 2 seconds checks if paired
2. **WebRTC Setup** - Automatic offer/answer negotiation
3. **ICE Candidate Exchange** - Polls signaling server every 1 second
4. **Disconnect Handling** - Clean disconnection and "Next Stranger" button

### Configuration

Replace these placeholders in the HTML file:

```javascript
const N8N_MATCHING_WEBHOOK = "https://your-n8n.app/webhook/matching";
const N8N_SIGNALING_WEBHOOK = "https://your-n8n.app/webhook/signaling";
```

---

## How It Works (Step by Step)

### Phase 1: Finding a Match

1. **User A clicks "Start Chat"**
   - Gets camera/microphone access
   - Sends `POST { user: "A", action: "join" }`
   - Response: `{ status: "waiting", position: 1 }`

2. **User A polls every 2 seconds**
   - Sends `POST { user: "A", action: "check" }`
   - Response: `{ status: "waiting", position: 1 }`

3. **User B clicks "Start Chat"**
   - Sends `POST { user: "B", action: "join" }`
   - **N8N matches them immediately!**
   - Response: `{ status: "matched", roomId: "room_xyz", peer: "A" }`

4. **User A's next poll**
   - Sends `POST { user: "A", action: "check" }`
   - Response: `{ status: "matched", roomId: "room_xyz", peer: "B" }`

### Phase 2: WebRTC Connection

5. **Determine Initiator**
   - User with smaller ID alphabetically becomes initiator
   - Example: "A" < "B" → User A initiates

6. **User A (Initiator)**
   - Creates WebRTC offer
   - Sends `POST { roomId: "room_xyz", type: "offer", data: {...} }`

7. **User B (Receiver)**
   - Polls signaling every 1 second
   - Gets offer from n8n
   - Creates answer
   - Sends `POST { roomId: "room_xyz", type: "answer", data: {...} }`

8. **User A gets answer**
   - Polls signaling
   - Receives answer
   - Sets remote description

9. **Both exchange ICE candidates**
   - Both send candidates as they're generated
   - Both poll to receive peer's candidates
   - Connection established!

---

## Testing

### Test Scenario 1: Basic Matching
1. Open `example-client-implementation.html` in Browser Tab 1
2. Click "Start Chat" → Should show "Waiting for stranger... (Position: 1)"
3. Open same file in Browser Tab 2
4. Click "Start Chat" → Both should instantly show "Connected to stranger!"

### Test Scenario 2: Queue with 3 Users
1. Tab 1: Start Chat → Waiting (Position 1)
2. Tab 2: Start Chat → Matched with Tab 1
3. Tab 3: Start Chat → Waiting (Position 1)
4. Tab 4: Start Chat → Matched with Tab 3

### Test Scenario 3: Next Stranger
1. Tab 1 & 2: Matched
2. Tab 1: Click "Next Stranger"
3. Tab 3: Start Chat
4. Tab 1: Should match with Tab 3

---

## Security Considerations

⚠️ **IMPORTANT:** This is an MVP implementation. For production:

1. **Add Authentication**
   - Don't use random IDs, use real user sessions
   - Validate users on server-side

2. **Rate Limiting**
   - Prevent spam/abuse of webhooks
   - Add CAPTCHA before "Start Chat"

3. **Room Expiration**
   - Currently rooms stay forever
   - Add TTL cleanup (e.g., delete after 1 hour)

4. **TURN Server**
   - STUN servers work for ~80% of connections
   - For users behind strict NATs, add TURN server
   - Example: Twilio, Xirsys, or self-hosted coturn

5. **Content Moderation**
   - Add report/block features
   - Log room connections for moderation

---

## Troubleshooting

### "Waiting forever, no match found"
- ✅ Check n8n webhook URLs are correct
- ✅ Check browser console for API errors
- ✅ Verify n8n workflows are active
- ✅ Open in 2 separate browser tabs/windows

### "Connected but no video"
- ✅ Check camera/microphone permissions
- ✅ Check browser console for WebRTC errors
- ✅ Try using Chrome/Firefox (Safari has issues)
- ✅ Check if behind corporate firewall (may block WebRTC)

### "Connection failed"
- ✅ Both users might be behind symmetric NATs
- ✅ Need to add TURN server (see Security section)
- ✅ Check if ICE candidates are being exchanged

---

## Integration with Your WordPress Site

To integrate with `chat.tossee.com`:

1. **Get User ID from Session**
   ```javascript
   // Replace this line:
   const userId = "user_" + Math.random().toString(36).substring(2, 10);

   // With:
   const userId = window.tosseeUserId; // Set by PHP during page load
   ```

2. **Add PHP Session to Page**
   ```php
   <?php
   session_start();
   if (!isset($_SESSION['tossee_id'])) {
       header('Location: /login');
       exit;
   }
   ?>
   <script>
   window.tosseeUserId = <?php echo json_encode($_SESSION['tossee_id']); ?>;
   </script>
   ```

3. **Deploy HTML to `chat.tossee.com`**
   - Upload `example-client-implementation.html`
   - Update webhook URLs in JavaScript
   - Test with real users

---

## Files in This Directory

- `improved-matching-workflow.js` - N8N node code for user pairing
- `improved-signaling-workflow.js` - N8N node code for WebRTC signaling
- `README.md` - This documentation
- `example-client-implementation.html` - Complete working frontend

---

## Additional Resources

- [N8N Webhook Documentation](https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.webhook/)
- [WebRTC API Guide](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)
- [Simple Peer Library](https://github.com/feross/simple-peer) - Alternative to manual WebRTC
- [Socket.io + Node.js Alternative](https://socket.io/get-started/chat) - More robust than n8n polling

---

## Support

If issues persist:
1. Check n8n workflow execution logs
2. Check browser DevTools console
3. Verify both users are online simultaneously
4. Test with different browsers/devices
