# Tossee MVP - Random Video Chat Platform

A random video chat platform similar to Omegle, built on WordPress with custom user management and WebRTC video connections.

## 🚀 Quick Start

1. **Setup:** See [SETUP.md](SETUP.md) for initial configuration
2. **Deploy:** Run `./deploy.sh` to deploy to production
3. **Test:** Run `./test-endpoints.sh` to verify endpoints
4. **Documentation:** See [README-DEPLOYMENT.md](README-DEPLOYMENT.md) for detailed docs

## 📁 Project Structure

```
tossee-mvp/
├── api/
│   ├── matching.php       # API proxy for n8n matching webhook
│   └── signaling.php      # API proxy for n8n signaling webhook
├── deploy.sh              # Automated deployment script
├── test-endpoints.sh      # Endpoint testing script
├── fix-astra-theme.php    # Theme redirect fix script
├── SETUP.md              # Initial setup guide
├── DEPLOYMENT_GUIDE.md   # Detailed deployment steps
└── README-DEPLOYMENT.md  # Complete documentation
```

## 🔗 URLs

- **Homepage:** https://tossee.com/
- **Chat:** https://chat.tossee.com/
- **Admin:** https://tossee.com/wp-admin/
- **n8n:** https://n8n.tossee.com/

## ⚙️ Technology Stack

- **Frontend:** HTML, JavaScript, WebRTC
- **Backend:** PHP, WordPress
- **Database:** MySQL (custom table: wp_tossee_users)
- **Signaling:** n8n webhooks
- **Server:** Hostinger VPS (62.72.34.8:65002)

## 📋 Features

- Custom user registration (username, email, password, DOB, photo)
- Session-based authentication ($_SESSION['tossee_id'])
- Random video matching (WebRTC)
- User profile pages
- Admin panel for user management
- n8n webhook integration for signaling

## 🔧 Recent Fixes

### Redirect Loop Fix (Dec 16, 2025)
Fixed infinite redirect loop caused by:
- Astra theme forcing non-logged-in users to /login
- peters-login-redirect plugin
- Custom tossee-redirect plugin

**Solution:**
- Created fix-astra-theme.php to remove redirect code
- Disabled problematic plugins
- Created deployment scripts for safe restoration

See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for details.

## 📖 Documentation

- **[SETUP.md](SETUP.md)** - Initial setup and prerequisites
- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Step-by-step deployment guide
- **[README-DEPLOYMENT.md](README-DEPLOYMENT.md)** - Complete documentation with troubleshooting
- **[Tossee — MVP Specification.txt](Tossee%20—%20MVP%20Specification.txt)** - Full MVP requirements

## 🧪 Testing

```bash
# Test all endpoints
./test-endpoints.sh

# Manual tests
curl -I https://tossee.com/                    # Should return HTTP 200
curl -I https://chat.tossee.com/               # Should return HTTP 200
curl -X POST https://chat.tossee.com/api/matching.php \
  -H "Content-Type: application/json" \
  -d '{"userId":"test","action":"join"}'       # Should return HTTP 200 or 502
```

## 🔐 Security

- SSH keys (.pem files) are git-ignored
- API endpoints only accept POST requests
- CORS configured for WebRTC
- WordPress admin protected by login
- Database uses custom tables (not WP default users)

## 📞 Support

**Server Details:**
- Host: Hostinger VPS
- IP: 62.72.34.8
- SSH Port: 65002
- User: u234011694

**Development Branch:**
- Branch: `claude/code-review-debugging-iqgMg`

---

**Status:** Ready for deployment
**Last Updated:** December 16, 2025