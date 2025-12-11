# N8N Video Chat - Step-by-Step Setup Guide

This guide will walk you through creating both n8n workflows from scratch.

---

## 📋 Prerequisites

- N8N instance running (cloud or self-hosted)
- Access to n8n workflow editor
- Basic understanding of n8n nodes

---

## 🔧 Workflow 1: Matching (User Pairing)

This workflow handles matching users together.

### Step 1: Create New Workflow

1. Click **"Add Workflow"** in n8n
2. Name it: **"Tossee Video Chat - Matching"**
3. Click **"Save"**

### Step 2: Add Webhook Node

1. Click the **+** button to add a node
2. Search for **"Webhook"**
3. Click on **"Webhook"** node

#### Configure Webhook Node:

| Setting | Value |
|---------|-------|
| **HTTP Method** | POST |
| **Path** | `matching` |
| **Response Mode** | "When Last Node Finishes" |
| **Response Code** | 200 |

**Screenshot reference:**
```
┌─────────────────────────────┐
│ Webhook                      │
├─────────────────────────────┤
│ HTTP Method:     POST        │
│ Path:            matching    │
│ Response Mode:   Last Node   │
│ Response Code:   200         │
└─────────────────────────────┘
```

4. Click **"Execute Node"** to test (should show "Waiting for webhook call")
5. **Copy the Production URL** - you'll need this later!
   - It looks like: `https://your-n8n.app/webhook/matching`

### Step 3: Add Code Node

1. Click the **+** button next to Webhook node
2. Search for **"Code"**
3. Select **"Code"** node (NOT "Function" or "Function Item")

#### Configure Code Node:

1. **Mode:** "Run Once for All Items"
2. **Language:** JavaScript
3. Click in the code editor
4. **Delete all existing code**
5. Open the file `improved-matching-workflow.js`
6. **Copy ALL the code** from that file
7. **Paste it** into the n8n code editor

**Important:** Make sure you copied the ENTIRE file, including:
- The comments at the top
- All the initialization code
- All the logic
- The final `return` statement

#### Verify the code:

- First line should be: `const data = this.getWorkflowStaticData('global');`
- Code should have sections for:
  - INIT STORAGE
  - CLEANUP OLD WAITING USERS
  - ACTION: CHECK FOR MATCH
  - ACTION: JOIN QUEUE
- Last part should have multiple `return` statements

### Step 4: Add Respond to Webhook Node

1. Click the **+** button next to Code node
2. Search for **"Respond to Webhook"**
3. Select **"Respond to Webhook"** node

#### Configure Respond to Webhook:

1. **Response Code:** 200
2. **Response Body:** Leave **EMPTY** ❗ (This is important!)

#### Add CORS Headers:

1. Click **"Add Option"**
2. Select **"Response Headers"**
3. Click **"Add Header"** and add these:

| Header Name | Header Value |
|------------|--------------|
| `Access-Control-Allow-Origin` | `*` |
| `Access-Control-Allow-Methods` | `POST, OPTIONS` |
| `Access-Control-Allow-Headers` | `Content-Type` |

**Screenshot reference:**
```
┌─────────────────────────────────────────┐
│ Respond to Webhook                       │
├─────────────────────────────────────────┤
│ Response Code:  200                      │
│ Response Body:  [EMPTY - DO NOT FILL]    │
│                                          │
│ ✓ Response Headers                       │
│   • Access-Control-Allow-Origin: *       │
│   • Access-Control-Allow-Methods: POST   │
│   • Access-Control-Allow-Headers: ...    │
└─────────────────────────────────────────┘
```

### Step 5: Connect the Nodes

Make sure the workflow looks like this:

```
[Webhook] ──→ [Code] ──→ [Respond to Webhook]
```

All nodes should have lines connecting them!

### Step 6: Activate Workflow

1. Click **"Save"** (Ctrl+S)
2. Click the toggle switch at the top to **"Active"**
3. Verify it shows **🟢 Active** (green)

### Step 7: Test the Workflow

Open terminal and run:

```bash
curl -X POST https://your-n8n.app/webhook/matching \
  -H "Content-Type: application/json" \
  -d '{"user":"test123","action":"join"}'
```

**Expected response:**
```json
{
  "ok": true,
  "status": "waiting",
  "position": 1,
  "message": "Added to queue. Poll with action=check every 2-3 seconds."
}
```

✅ **If you see this response, Workflow 1 is working!**

---

## 🔧 Workflow 2: Signaling (WebRTC Exchange)

This workflow handles WebRTC offer/answer/ICE candidate exchange.

### Step 1: Create New Workflow

1. Click **"Add Workflow"** in n8n
2. Name it: **"Tossee Video Chat - Signaling"**
3. Click **"Save"**

### Step 2: Add Webhook Node

1. Click the **+** button to add a node
2. Search for **"Webhook"**
3. Click on **"Webhook"** node

#### Configure Webhook Node:

| Setting | Value |
|---------|-------|
| **HTTP Method** | POST |
| **Path** | `signaling` |
| **Response Mode** | "When Last Node Finishes" |
| **Response Code** | 200 |

4. Click **"Execute Node"** to test
5. **Copy the Production URL** - you'll need this later!
   - It looks like: `https://your-n8n.app/webhook/signaling`

### Step 3: Add Code Node

1. Click the **+** button next to Webhook node
2. Search for **"Code"**
3. Select **"Code"** node

#### Configure Code Node:

1. **Mode:** "Run Once for All Items"
2. **Language:** JavaScript
3. Click in the code editor
4. **Delete all existing code**
5. Open the file `improved-signaling-workflow.js`
6. **Copy ALL the code** from that file
7. **Paste it** into the n8n code editor

#### Verify the code:

- First line should be: `const store = this.getWorkflowStaticData('global');`
- Code should have a `switch` statement with cases for:
  - `case "offer"`
  - `case "answer"`
  - `case "candidate"`
  - `case "check"`
  - `case "disconnect"`

### Step 4: Add Respond to Webhook Node

1. Click the **+** button next to Code node
2. Search for **"Respond to Webhook"**
3. Select **"Respond to Webhook"** node

#### Configure Respond to Webhook:

1. **Response Code:** 200
2. **Response Body:** Leave **EMPTY** ❗

#### Add CORS Headers:

Same as Workflow 1:

| Header Name | Header Value |
|------------|--------------|
| `Access-Control-Allow-Origin` | `*` |
| `Access-Control-Allow-Methods` | `POST, OPTIONS` |
| `Access-Control-Allow-Headers` | `Content-Type` |

### Step 5: Connect the Nodes

```
[Webhook] ──→ [Code] ──→ [Respond to Webhook]
```

### Step 6: Activate Workflow

1. Click **"Save"** (Ctrl+S)
2. Click the toggle switch to **"Active"**
3. Verify it shows **🟢 Active**

### Step 7: Test the Workflow

```bash
curl -X POST https://your-n8n.app/webhook/signaling \
  -H "Content-Type: application/json" \
  -d '{"roomId":"test_room","type":"check"}'
```

**Expected response:**
```json
{
  "ok": true,
  "roomData": {
    "offers": [],
    "answers": [],
    "candidates": []
  }
}
```

✅ **If you see this response, Workflow 2 is working!**

---

## 🎯 Final Configuration

### Summary

You should now have **2 active workflows**:

1. **Tossee Video Chat - Matching**
   - URL: `https://your-n8n.app/webhook/matching`
   - Handles user pairing

2. **Tossee Video Chat - Signaling**
   - URL: `https://your-n8n.app/webhook/signaling`
   - Handles WebRTC exchange

### Update Client Code

Now update your HTML files with these URLs:

#### In `example-client-implementation.html`:

Find these lines (around line 40-41):

```javascript
const N8N_MATCHING_WEBHOOK = "YOUR_N8N_WEBHOOK_URL_HERE";
const N8N_SIGNALING_WEBHOOK = "YOUR_N8N_WEBHOOK_URL_HERE";
```

Replace with your actual URLs:

```javascript
const N8N_MATCHING_WEBHOOK = "https://your-n8n.app/webhook/matching";
const N8N_SIGNALING_WEBHOOK = "https://your-n8n.app/webhook/signaling";
```

Save the file.

---

## 🧪 Complete Testing

### Test 1: Use Test File

1. Open `test-n8n-webhooks.html` in your browser
2. Enter both webhook URLs
3. Click **"Save URLs"**
4. Run **Test 1: Join Queue** - should show ✅ success
5. Run **Test 3: Match Two Users** - should show ✅ both matched with same roomId
6. Run **Test 5: Check Signals** - should show ✅ roomData with arrays

### Test 2: Test Video Chat

1. Open `example-client-implementation.html` in **Chrome** (Tab 1)
2. Click **"Start Chat"**
3. Grant camera/microphone permissions
4. You should see: "Waiting for stranger... (Position: 1)"

5. Open `example-client-implementation.html` in another **Chrome tab** (Tab 2)
6. Click **"Start Chat"**
7. Grant camera/microphone permissions

**Expected result:**
- Both tabs should show: **"Connected to stranger! Setting up video..."**
- Then: **"Connected! Enjoy your chat!"**
- You should see yourself in "Your Video"
- You should see the other tab in "Stranger's Video"

---

## ⚠️ Common Issues

### Issue: "Waiting forever, never matches"

**Check:**
- Both workflows are **Active** (green toggle)
- Both webhook URLs are correct in HTML file
- Both browser tabs are open **simultaneously**
- Check browser console for errors (F12)

### Issue: "Connected but no video"

**Check:**
- Camera/microphone permissions granted
- Using Chrome or Firefox (Safari has WebRTC issues)
- Check browser console for WebRTC errors
- Try refreshing both tabs

### Issue: "Empty response from webhook"

**Check:**
- Workflow is **Active**
- All 3 nodes are **connected**
- Code is copied **exactly** from the .js files
- "Respond to Webhook" Response Body is **EMPTY**

**For detailed troubleshooting, see `TROUBLESHOOTING.md`**

---

## 🎉 Success!

If both tests pass, your n8n video chat system is fully working!

### What happens:

1. User A clicks "Start" → joins queue → waits
2. User B clicks "Start" → matches with User A → both get roomId
3. User A polls every 2s → finds match → gets same roomId
4. Both users connect via WebRTC
5. Both users exchange offers/answers/ICE candidates via signaling webhook
6. Video chat established!

---

## 📝 Notes

- **Queue cleanup:** Users waiting more than 2 minutes are automatically removed
- **Room storage:** Rooms persist until both users disconnect
- **Scalability:** For production, consider using Redis instead of workflow static data
- **Security:** Add authentication before deploying to production

---

## 🚀 Next Steps

1. **Integrate with WordPress** - Follow instructions in main README.md
2. **Add authentication** - Use real user IDs from your session
3. **Deploy to production** - Move from test to live domain
4. **Add features** - Text chat, screen sharing, filters, etc.

---

## 📚 Additional Resources

- [N8N Webhook Docs](https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.webhook/)
- [N8N Code Node Docs](https://docs.n8n.io/code-examples/methods-variables-examples/)
- [WebRTC API Documentation](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)

---

## ❓ Need Help?

If you're stuck:

1. Check `TROUBLESHOOTING.md` for common issues
2. Review n8n execution logs (Executions tab)
3. Check browser console (F12 → Console tab)
4. Verify both workflows are Active
5. Test with `curl` commands above
