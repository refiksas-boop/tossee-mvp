# Tossee MVP - Deployment & Fix Documentation

## 🎯 Quick Start

### 1. Deploy to Server
```bash
./deploy.sh
```

### 2. Fix Astra Theme
Visit: https://tossee.com/fix-astra-theme.php
Then delete the file: `rm ~/domains/tossee.com/public_html/fix-astra-theme.php`

### 3. Test Everything
```bash
./test-endpoints.sh
```

---

## 📋 Problem Summary

### What Happened?
The site had a **redirect loop** (ERR_TOO_MANY_REDIRECTS) caused by:

1. **Astra theme** `functions.php` - Forced ALL non-logged-in users to `/login`
2. **peters-login-redirect plugin** - Additional redirect layer
3. **tossee-redirect.php** - Custom redirect plugin

### Why Did It Break?
The SSL certificate expired on Dec 12, 2025. When renewed:
- Users were logged out (cookies cleared)
- The redirect code kicked in for non-logged-in users
- Created infinite redirect loop

---

## 🔧 Files Created

### API Proxy Files
- `api/matching.php` - Forwards matching requests to n8n webhook
- `api/signaling.php` - Forwards WebRTC signaling to n8n webhook

### Deployment Scripts
- `deploy.sh` - Deploys all files to Hostinger VPS
- `test-endpoints.sh` - Tests all endpoints
- `fix-astra-theme.php` - Removes redirect code from Astra theme

### Documentation
- `DEPLOYMENT_GUIDE.md` - Detailed deployment steps
- `README-DEPLOYMENT.md` - This file

---

## 🚀 Deployment Steps

### Prerequisites
- SSH key: `tossee-mvp-key.pem` in project root
- Access to Hostinger VPS (62.72.34.8:65002)
- n8n instance at n8n.tossee.com

### Step 1: Deploy Files
```bash
cd /home/user/tossee-mvp
./deploy.sh
```

This will:
- Upload API files to `/chat/api/`
- Create `.htaccess` files to bypass WordPress routing
- Upload the theme fix script
- Set correct permissions

### Step 2: Fix Astra Theme
**Option A: Via Browser (Recommended)**
1. Visit: https://tossee.com/fix-astra-theme.php
2. Check output for success messages
3. Delete the file after use

**Option B: Via SSH**
```bash
ssh -i tossee-mvp-key.pem -p 65002 u234011694@62.72.34.8
cd ~/domains/tossee.com/public_html
php fix-astra-theme.php
rm fix-astra-theme.php
```

### Step 3: Restore Plugins
1. Log into wp-admin: https://tossee.com/wp-admin/
2. Go to Plugins
3. Re-enable plugins **ONE BY ONE**
4. **AVOID these plugins:**
   - peters-login-redirect
   - tossee-redirect
   - Any custom redirect plugins

**Safe plugins to enable:**
- Classic Editor
- Contact Form 7
- Elementor (if used)
- Yoast SEO / Rank Math
- WooCommerce (if needed)

### Step 4: Activate Astra Theme
1. In wp-admin: Appearance → Themes
2. Activate "Astra" (the fixed version)
3. Test homepage: https://tossee.com/

### Step 5: Test Everything
```bash
./test-endpoints.sh
```

Expected results:
- ✅ Homepage (tossee.com): HTTP 200
- ✅ Chat (chat.tossee.com): HTTP 200
- ✅ Matching API: HTTP 200 or 502*
- ✅ Signaling API: HTTP 200 or 502*

*502 = API proxy works, but n8n workflow may be inactive

---

## 📁 File Structure

### On Server: `/home/u234011694/domains/tossee.com/public_html/`

```
public_html/
├── wp-admin/                    # WordPress admin
├── wp-content/
│   ├── plugins/                 # Active plugins
│   ├── plugins.DISABLED/        # Problematic plugins (backup)
│   └── themes/
│       ├── astra/               # Fixed theme (active)
│       └── astra.DISABLED/      # Original theme (backup)
├── chat/                        # Chat subdomain (chat.tossee.com)
│   ├── index.html              # Chat interface
│   ├── api/
│   │   ├── matching.php        # Matching API proxy
│   │   ├── signaling.php       # Signaling API proxy
│   │   └── .htaccess           # Bypass WordPress routing
│   └── .htaccess               # Disable rewrites
└── .htaccess                   # WordPress main
```

---

## 🔍 Troubleshooting

### Issue: API returns 404
**Cause:** WordPress .htaccess is rewriting requests

**Fix:**
1. Check `/chat/api/.htaccess` exists
2. Should contain: `RewriteEngine Off`
3. Test directly: `curl -I https://chat.tossee.com/api/matching.php`

### Issue: Redirect loop returns
**Cause:** Problematic plugin re-enabled

**Fix:**
1. Disable all plugins
2. Re-enable ONE BY ONE
3. Test after each: `curl -I https://tossee.com/`
4. Identify which plugin causes HTTP 301/302

### Issue: n8n not receiving requests
**Cause:** Workflows may be inactive

**Fix:**
1. Check n8n dashboard: https://n8n.tossee.com/
2. Activate workflows:
   - "Matching" (webhook: /webhook/next)
   - "Signaling" (webhook: /webhook/signal)
3. Test: `./test-endpoints.sh`

### Issue: Homepage blank/white
**Cause:** Theme or plugins disabled

**Fix:**
1. Activate Astra theme (fixed version)
2. Enable essential plugins
3. Check WordPress error log: `~/domains/tossee.com/logs/error_log`

---

## 🔐 Security Notes

### After Deployment
1. **Delete** `fix-astra-theme.php` (security risk if left public)
2. **Verify** permissions:
   ```bash
   chmod 644 ~/domains/tossee.com/public_html/chat/api/*.php
   chmod 755 ~/domains/tossee.com/public_html/chat/api/
   ```
3. **Test** that only POST methods work on API endpoints

### Plugin Safety
- ❌ **NEVER** re-enable:
  - `peters-login-redirect`
  - `tossee-redirect.php`
- ✅ Safe to enable:
  - Standard WordPress plugins from official repository
  - Verified theme-specific plugins

---

## 📊 API Endpoints Reference

### Matching API
**URL:** `https://chat.tossee.com/api/matching.php`
**Method:** POST
**Body:**
```json
{
  "userId": "user123",
  "action": "join"
}
```
**Forwards to:** `https://n8n.tossee.com/webhook/next`

### Signaling API
**URL:** `https://chat.tossee.com/api/signaling.php`
**Method:** POST
**Body:**
```json
{
  "type": "offer|answer|ice",
  "userId": "user123",
  "targetId": "user456",
  "data": {}
}
```
**Forwards to:** `https://n8n.tossee.com/webhook/signal`

---

## 🧪 Testing Commands

### Test Homepage
```bash
curl -I https://tossee.com/
# Expected: HTTP/2 200
```

### Test Chat
```bash
curl -I https://chat.tossee.com/
# Expected: HTTP/2 200
```

### Test Matching API
```bash
curl -X POST https://chat.tossee.com/api/matching.php \
  -H "Content-Type: application/json" \
  -d '{"userId":"test123","action":"join"}'
# Expected: HTTP 200 or 502 (if n8n inactive)
```

### Test Signaling API
```bash
curl -X POST https://chat.tossee.com/api/signaling.php \
  -H "Content-Type: application/json" \
  -d '{"type":"offer","userId":"test123"}'
# Expected: HTTP 200 or 502 (if n8n inactive)
```

### Test n8n Directly
```bash
curl -X POST https://n8n.tossee.com/webhook/next \
  -H "Content-Type: application/json" \
  -d '{"test":true}'
# Expected: HTTP 200 (if workflow active)
```

---

## 📞 Support

### Server Details
- **Host:** Hostinger VPS
- **IP:** 62.72.34.8
- **SSH Port:** 65002
- **User:** u234011694

### n8n Details
- **URL:** https://n8n.tossee.com/
- **Matching Webhook:** /webhook/next
- **Signaling Webhook:** /webhook/signal

### WordPress
- **Admin:** https://tossee.com/wp-admin/
- **Database:** wp_tossee_users (custom table)
- **Auth:** Session-based ($_SESSION['tossee_id'])

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] Homepage loads (https://tossee.com/) - HTTP 200
- [ ] No redirect loop
- [ ] wp-admin accessible (https://tossee.com/wp-admin/)
- [ ] Chat page loads (https://chat.tossee.com/)
- [ ] Matching API responds (even if 502)
- [ ] Signaling API responds (even if 502)
- [ ] n8n workflows active (2/2)
- [ ] Theme looks correct
- [ ] Plugins functioning
- [ ] fix-astra-theme.php deleted

---

**Last Updated:** December 16, 2025
**Status:** Ready for deployment
