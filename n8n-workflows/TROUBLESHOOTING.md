# N8N Video Chat - Troubleshooting Guide

## ❌ Error: "Unexpected end of JSON input"

This is the **most common error** and happens when your n8n webhook returns an empty or invalid response.

### 🔍 Root Causes

1. **Workflow not active**
2. **"Respond to Webhook" node not connected**
3. **JavaScript code has syntax errors**
4. **Wrong webhook URL**

---

## ✅ Step-by-Step Fix

### Step 1: Verify N8N Workflow is Active

1. Open your n8n dashboard
2. Find your workflow
3. Look for the **toggle switch** at the top
4. Make sure it says **"Active"** (not "Inactive")

```
┌─────────────────────────────┐
│  🟢 Active                   │  ← Should be GREEN
│  ⚪ Inactive                 │  ← If gray, click to activate
└─────────────────────────────┘
```

**If workflow is inactive:**
- Click the toggle to activate it
- Save the workflow (Ctrl+S)

---

### Step 2: Check Workflow Structure

Your workflow MUST have these nodes connected in order:

```
Webhook → Code in JavaScript → Respond to Webhook
```

**Common mistake:** Missing connection between nodes!

#### For Matching Workflow:
```
┌──────────┐     ┌────────────────────┐     ┌────────────────────┐
│ Webhook  │────→│ Code in JavaScript │────→│ Respond to Webhook │
└──────────┘     └────────────────────┘     └────────────────────┘
```

#### For Signaling Workflow:
```
┌───────────┐     ┌─────────────────────┐     ┌─────────────────────┐
│ Webhook1  │────→│ Code in JavaScript1 │────→│ Respond to Webhook1 │
└───────────┘     └─────────────────────┘     └─────────────────────┘
```

---

### Step 3: Configure Webhook Node Correctly

#### Webhook Node Settings:

1. **HTTP Method:** POST ✅
2. **Path:** `/matching` (or any unique path)
3. **Response Mode:** "Respond Immediately" or "When Last Node Finishes"
4. **Response Code:** 200

#### ⚠️ Common Mistakes:
- ❌ Method set to GET (should be POST)
- ❌ No path specified
- ❌ Response mode set incorrectly

---

### Step 4: Check JavaScript Code

#### In "Code in JavaScript" node:

1. Click on the node
2. Make sure the code is **exactly** from `improved-matching-workflow.js`
3. Check for these common syntax errors:
   - Missing semicolons
   - Unmatched brackets `{ }`
   - Typos in variable names

#### Test the code:
```javascript
// This should be at the top of your code:
const data = this.getWorkflowStaticData('global');

// This should be at the bottom:
return { ok: true, status: "waiting" };  // or matched
```

#### ⚠️ Common Mistakes:
- ❌ Returning `undefined` instead of an object
- ❌ Not using `return` statement
- ❌ Returning array `[{ }]` instead of object `{ }`

---

### Step 5: Configure "Respond to Webhook" Node

#### Settings:

1. **Response Code:** 200
2. **Response Body:** Leave EMPTY (it will use the return value from JavaScript)
3. **Response Headers:** Add CORS headers (see below)

#### Adding CORS Headers:

Click "Add Option" → "Response Headers" → Add these:

| Header Name | Header Value |
|------------|--------------|
| `Access-Control-Allow-Origin` | `*` |
| `Access-Control-Allow-Methods` | `POST, OPTIONS` |
| `Access-Control-Allow-Headers` | `Content-Type` |

#### ⚠️ Common Mistakes:
- ❌ Hardcoding response in "Response Body" (should be empty!)
- ❌ Missing CORS headers (causes errors in browser)
- ❌ Wrong response code (should be 200)

---

### Step 6: Get the Correct Webhook URL

1. Click on **Webhook** node
2. Look for **"Webhook URLs"** section
3. Copy the **Production URL** (not Test URL!)

Example:
```
❌ Test URL:     http://localhost:5678/webhook-test/...
✅ Production:   https://your-n8n.app/webhook/matching
```

#### ⚠️ Common Mistakes:
- ❌ Using test URL instead of production URL
- ❌ Workflow not active (production URL won't work!)
- ❌ Typos when copying URL

---

## 🧪 Testing Your Webhook

### Option 1: Use the Test File

1. Open `test-n8n-webhooks.html` in your browser
2. Enter your webhook URLs
3. Click "Test 1: Join Queue"
4. Check the results

### Option 2: Test with cURL

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
  "position": 1
}
```

### Option 3: Test in Browser DevTools

```javascript
fetch('https://your-n8n.app/webhook/matching', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ user: 'test123', action: 'join' })
})
.then(r => r.json())
.then(console.log)
.catch(console.error)
```

---

## 🚨 Common Error Messages

### Error: "Failed to fetch"

**Cause:** Network error or CORS issue

**Fix:**
1. Check webhook URL is correct
2. Add CORS headers (see Step 5)
3. Make sure workflow is active
4. Check your internet connection

---

### Error: "404 Not Found"

**Cause:** Webhook URL doesn't exist

**Fix:**
1. Verify workflow is **Active**
2. Copy the **Production URL** (not test URL)
3. Check for typos in URL

---

### Error: "500 Internal Server Error"

**Cause:** JavaScript code has errors

**Fix:**
1. Click on "Code in JavaScript" node
2. Look for red error messages
3. Check your code matches `improved-matching-workflow.js` exactly
4. Test the workflow in n8n (click "Execute Workflow")

---

### Error: Empty Response (0 bytes)

**Cause:** "Respond to Webhook" not connected or configured wrong

**Fix:**
1. Make sure all 3 nodes are connected
2. Clear "Response Body" in "Respond to Webhook" (should be empty!)
3. Check JavaScript code has `return` statement

---

## 🔧 Debugging Checklist

Use this checklist to debug your n8n setup:

- [ ] Workflow is **Active** (green toggle)
- [ ] All 3 nodes are **connected** in a line
- [ ] Webhook node set to **POST** method
- [ ] JavaScript code has NO syntax errors
- [ ] JavaScript code has `return` statement at the end
- [ ] "Respond to Webhook" has **empty Response Body**
- [ ] CORS headers are added
- [ ] Using **Production URL** (not test URL)
- [ ] Tested with `curl` or browser DevTools
- [ ] Check n8n execution logs for errors

---

## 📊 N8N Execution Logs

### How to View Logs:

1. In n8n, go to **"Executions"** tab (left sidebar)
2. Click on latest execution
3. Look for errors in red
4. Check what data was received and returned

### What to Look For:

**Input Data (Webhook received):**
```json
{
  "body": {
    "user": "user_123",
    "action": "join"
  }
}
```

**Output Data (What gets returned):**
```json
{
  "ok": true,
  "status": "waiting",
  "position": 1
}
```

**If you see errors:**
- Click on the error node (will be red)
- Read the error message
- Common issues: undefined variable, missing return, syntax error

---

## 🎯 Quick Fixes Summary

| Problem | Quick Fix |
|---------|-----------|
| Empty response | Activate workflow, connect nodes |
| CORS error | Add CORS headers to "Respond to Webhook" |
| 404 error | Use Production URL, activate workflow |
| 500 error | Fix JavaScript syntax errors |
| No match found | Test with 2 browser tabs simultaneously |
| Waiting forever | Check both webhooks (matching AND signaling) |

---

## 💡 Still Not Working?

If you've tried everything above:

1. **Create a NEW simple workflow** to test:
   ```
   Webhook → Respond to Webhook
   ```
   Set "Respond to Webhook" Response Body to: `{"test": "success"}`

   Test it with:
   ```bash
   curl -X POST https://your-n8n.app/webhook/test
   ```

   Should return: `{"test": "success"}`

2. **If simple test works**, the issue is in your JavaScript code
   - Copy code from `improved-matching-workflow.js` EXACTLY
   - Don't modify it until you verify it works

3. **If simple test fails**, the issue is with n8n setup
   - Check n8n is accessible from internet
   - Check firewall settings
   - Try restarting n8n instance

---

## 📞 Getting More Help

1. **Check n8n documentation:** https://docs.n8n.io/
2. **N8N community forum:** https://community.n8n.io/
3. **Check browser console** for detailed error messages
4. **Check n8n execution logs** for server-side errors

---

## ✅ Expected Behavior When Working

### Matching Workflow (Working):

**Request:**
```json
POST /webhook/matching
{"user": "user_123", "action": "join"}
```

**Response (first user):**
```json
{
  "ok": true,
  "status": "waiting",
  "position": 1,
  "message": "Added to queue. Poll with action=check every 2-3 seconds."
}
```

**Response (second user, matched):**
```json
{
  "ok": true,
  "status": "matched",
  "roomId": "room_a1b2c3d4",
  "users": ["user_123", "user_456"],
  "peer": "user_123"
}
```

### Signaling Workflow (Working):

**Request:**
```json
POST /webhook/signaling
{"roomId": "room_123", "type": "check"}
```

**Response:**
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

---

## 🎉 Success Indicators

You'll know everything is working when:

1. ✅ `test-n8n-webhooks.html` shows all tests passing
2. ✅ Two browser tabs can connect and see "Connected to stranger!"
3. ✅ Video streams appear in both tabs
4. ✅ No errors in browser console
5. ✅ n8n execution logs show successful runs

---

## 🔄 Testing Workflow

Follow this exact sequence to test:

1. **Activate both workflows** in n8n
2. **Get both webhook URLs** (Production URLs)
3. **Open `test-n8n-webhooks.html`** in browser
4. **Enter both URLs** and click "Save"
5. **Run Test 1** - should return `{"ok": true, "status": "waiting"}`
6. **Run Test 3** - should match two users with same roomId
7. **Open `example-client-implementation.html`** in two tabs
8. **Click "Start Chat"** in both tabs
9. **Verify they connect** and see each other

If ANY step fails, go back to the checklist above!
