# Tossee MVP - Initial Setup

## Prerequisites

### 1. SSH Key
You need the private SSH key to deploy to the server.

Place the key file in the project root:
```bash
cp /path/to/your/tossee-mvp-key.pem /home/user/tossee-mvp/
chmod 600 tossee-mvp-key.pem
```

**Security:** The key file is in `.gitignore` and will not be committed to the repository.

### 2. Server Access
- **Server:** 62.72.34.8
- **Port:** 65002
- **User:** u234011694
- **Key:** tossee-mvp-key.pem

Test connection:
```bash
ssh -i tossee-mvp-key.pem -p 65002 u234011694@62.72.34.8
```

---

## Quick Deployment

Once you have the SSH key:

```bash
# 1. Deploy files
./deploy.sh

# 2. Fix Astra theme (via browser or SSH)
# Browser: https://tossee.com/fix-astra-theme.php
# SSH:
ssh -i tossee-mvp-key.pem -p 65002 u234011694@62.72.34.8 \
  "cd ~/domains/tossee.com/public_html && php fix-astra-theme.php"

# 3. Test endpoints
./test-endpoints.sh

# 4. Access wp-admin to restore plugins
# https://tossee.com/wp-admin/
```

---

## Manual Deployment (if script fails)

### Upload API Files
```bash
scp -i tossee-mvp-key.pem -P 65002 \
  api/matching.php api/signaling.php \
  u234011694@62.72.34.8:~/domains/tossee.com/public_html/chat/api/
```

### Upload Fix Script
```bash
scp -i tossee-mvp-key.pem -P 65002 \
  fix-astra-theme.php \
  u234011694@62.72.34.8:~/domains/tossee.com/public_html/
```

### Create .htaccess Files
```bash
ssh -i tossee-mvp-key.pem -p 65002 u234011694@62.72.34.8

# For chat directory
cat > ~/domains/tossee.com/public_html/chat/.htaccess <<EOF
RewriteEngine Off
<FilesMatch "\.(php|html|js)$">
    Require all granted
</FilesMatch>
EOF

# For API directory
cat > ~/domains/tossee.com/public_html/chat/api/.htaccess <<EOF
RewriteEngine Off
<FilesMatch "\.php$">
    Require all granted
</FilesMatch>
EOF
```

---

## Files Overview

### Created Files
- **api/matching.php** - Matching API proxy to n8n
- **api/signaling.php** - Signaling API proxy to n8n
- **fix-astra-theme.php** - Removes redirect code from theme
- **deploy.sh** - Automated deployment script
- **test-endpoints.sh** - Endpoint testing script
- **DEPLOYMENT_GUIDE.md** - Detailed deployment guide
- **README-DEPLOYMENT.md** - Complete documentation

### Git Branch
- Branch: `claude/code-review-debugging-iqgMg`
- Workflow: Development → Commit → Push → Create PR

---

## Next Steps After Setup

1. **Deploy:** Run `./deploy.sh`
2. **Fix Theme:** Visit https://tossee.com/fix-astra-theme.php
3. **Test:** Run `./test-endpoints.sh`
4. **Restore Plugins:** Access wp-admin and enable safe plugins
5. **Activate n8n:** Enable workflows in n8n dashboard
6. **Test Chat:** Visit https://chat.tossee.com/

---

## Troubleshooting

### "Permission denied (publickey)"
- Check key permissions: `chmod 600 tossee-mvp-key.pem`
- Verify key location: `ls -la tossee-mvp-key.pem`
- Test connection: `ssh -i tossee-mvp-key.pem -p 65002 u234011694@62.72.34.8`

### "Connection refused"
- Verify server IP: 62.72.34.8
- Verify port: 65002
- Check firewall/VPN settings

### "No such file or directory"
- Ensure you're in the project directory: `cd /home/user/tossee-mvp`
- Check file exists: `ls -la deploy.sh`

---

## Security Checklist

- [ ] SSH key has 600 permissions
- [ ] Key is NOT committed to git
- [ ] fix-astra-theme.php deleted after use
- [ ] Only safe plugins enabled
- [ ] wp-admin accessible only to authorized users
- [ ] API endpoints only accept POST requests
- [ ] HTTPS enabled on all endpoints

---

**Ready to Deploy!**
