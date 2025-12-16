# Tossee MVP Deployment & Fix Guide

## Issue Summary
The site had a redirect loop caused by:
1. **Astra theme** - functions.php forced all non-logged-in users to /login
2. **peters-login-redirect plugin** - Forced redirect to login page
3. **tossee-redirect.php plugin** - Custom redirect plugin

## Current State (Dec 16, 2025)
- ✅ SSL Certificate: Active (Lifetime SSL)
- ✅ DNS: Pointing to 62.72.34.8 (Hostinger VPS)
- ✅ Site loads: HTTP 200 (redirect loop fixed)
- ❌ Blank page: All plugins and theme disabled
- ❌ API endpoints: Still returning 404

## Problematic Plugins to NEVER Re-enable
```
❌ peters-login-redirect
❌ tossee-redirect.php (custom plugin in root)
```

## Safe Restoration Steps

### Step 1: Re-enable Astra Theme (Fixed Version)
The theme has been fixed to remove forced redirect code.

On server:
```bash
cd /home/u234011694/domains/tossee.com/public_html/wp-content/themes/
# The fixed version will be uploaded
```

### Step 2: Re-enable Essential Plugins Only
Recommended safe plugins to re-enable:
- Classic Editor (if used)
- Contact Form plugins (if needed)
- Elementor (if used for design)
- Any SEO plugins

**DO NOT re-enable:**
- peters-login-redirect
- tossee-redirect
- Any custom redirect plugins

### Step 3: Fix API Endpoints (.htaccess)
The WordPress .htaccess is blocking /api/ requests.

Add this to `/home/u234011694/domains/tossee.com/public_html/chat/.htaccess`:
```apache
# Disable WordPress rewrites for chat directory
RewriteEngine Off

# Allow direct access to API files
<FilesMatch "\.(php|html|js)$">
    Order allow,deny
    Allow from all
</FilesMatch>
```

### Step 4: Verify API File Locations
Ensure these files exist and are executable:
```
/home/u234011694/domains/tossee.com/public_html/chat/api/matching.php
/home/u234011694/domains/tossee.com/public_html/chat/api/signaling.php
```

### Step 5: Test Endpoints
```bash
# Test matching endpoint
curl -X POST https://chat.tossee.com/api/matching.php \
  -H "Content-Type: application/json" \
  -d '{"userId":"test123","action":"join"}'

# Test signaling endpoint
curl -X POST https://chat.tossee.com/api/signaling.php \
  -H "Content-Type: application/json" \
  -d '{"type":"offer","userId":"test123"}'
```

## File Locations Reference

### Production Server: 62.72.34.8 (Port 65002)
```
WordPress Root: /home/u234011694/domains/tossee.com/public_html/
Chat Subdomain: /home/u234011694/domains/tossee.com/public_html/chat/
API Files:      /home/u234011694/domains/tossee.com/public_html/chat/api/
Plugins:        /home/u234011694/domains/tossee.com/public_html/wp-content/plugins/
Themes:         /home/u234011694/domains/tossee.com/public_html/wp-content/themes/
```

### Backup Locations (Currently Disabled)
```
Disabled Plugins: /home/u234011694/domains/tossee.com/public_html/wp-content/plugins.DISABLED/
Disabled Theme:   /home/u234011694/domains/tossee.com/public_html/wp-content/themes/astra.DISABLED/
```

## n8n Webhook URLs
- Matching: `https://n8n.tossee.com/webhook/next`
- Signaling: `https://n8n.tossee.com/webhook/signal`

## WordPress Login
After restoration:
- URL: https://tossee.com/wp-admin/
- Make site publicly accessible (no forced login for homepage)
- Only protect wp-admin area

## Expected Behavior After Fix
1. ✅ tossee.com loads homepage (public access, no login required)
2. ✅ chat.tossee.com loads chat interface
3. ✅ /api/matching.php accepts POST requests
4. ✅ /api/signaling.php accepts POST requests
5. ✅ "Next" button connects to n8n workflow
6. ✅ wp-admin accessible for logged-in users only
