# Tossee Core WordPress Plugin

Custom user registration and authentication system for Tossee platform.

## 🚀 Quick Start

### 1. Installation

```bash
# Upload to WordPress
/wp-content/plugins/tossee-core/tossee-core.php
```

### 2. Activate
WordPress Admin → Plugins → Activate "Tossee Core"

### 3. Create Pages

**Register Page** (`/register`):
```
[tossee_register_form redirect_to="https://chat.tossee.com/"]
```

**Login Page** (`/login`):
```
[tossee_login_form redirect_to="https://chat.tossee.com/"]
```

### 4. Done!
Users can now register and login. View them in: **WordPress Admin → Tossee Users**

---

## ⚠️ TROUBLESHOOTING: Registration Not Working?

### Quick Fixes:

#### 1. Enable Debug Mode
Edit `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### 2. Run Debug Script
1. Upload `debug-tossee.php` to WordPress root
2. Open: `https://your-domain.com/debug-tossee.php`
3. Check all items are ✓ (green)
4. Try the test form at bottom

#### 3. Check Database Table
Go to phpMyAdmin and verify table `wp_tossee_users` exists.

If not, deactivate and reactivate the plugin.

#### 4. Check Error Log
View: `/wp-content/debug.log`

Look for lines with `[TOSSEE]` to see what went wrong.

#### 5. Common Issues:

**Photo too large?**
Add to `wp-config.php`:
```php
@ini_set('upload_max_filesize', '32M');
@ini_set('post_max_size', '32M');
```

**Table doesn't exist?**
Deactivate → Activate plugin again.

**Form doesn't submit?**
Check browser console (F12) for JavaScript errors.

---

## 📖 Full Documentation

- **Installation Guide:** `TOSSEE-PLUGIN-INSTALLATION.md`
- **Debug Guide:** `TOSSEE-DEBUG-GUIDE.md`
- **Integration Examples:** `tossee-integration-example.php`

---

## ✨ Features

- ✅ Custom user registration with photo verification
- ✅ Login/logout with persistent sessions
- ✅ Unique Tossee ID for each user (never changes)
- ✅ Cookie-based authentication across subdomains
- ✅ Admin panel for user management
- ✅ Age verification (18+)
- ✅ Block/unblock users
- ✅ Password reset via email
- ✅ Terms & Conditions modal

---

## 🔧 Developer Info

### Get Current User:
```php
$user = tossee_get_current_user();
if ($user) {
    echo "Hello, " . $user->username;
    echo "Your ID: " . $user->tossee_id;
}
```

### Check if Logged In:
```php
if (tossee_get_current_user_id()) {
    // User is logged in
}
```

### Get User by ID:
```php
$user = tossee_get_user_by_id('tossee_abc123def456');
```

---

## 📊 Database

Table: `wp_tossee_users`

Key fields:
- `tossee_id` - Unique ID (never changes)
- `username` - Username
- `email` - Email (unique)
- `password_hash` - Bcrypt hash
- `photo` - Base64 image
- `is_blocked` - 0=active, 1=blocked

---

## 🆘 Need Help?

1. Read `TOSSEE-DEBUG-GUIDE.md`
2. Run `debug-tossee.php`
3. Check `/wp-content/debug.log`
4. Verify table exists in phpMyAdmin

---

## 📝 Files

- `tossee-core.php` - Main plugin
- `debug-tossee.php` - Debug tool
- `TOSSEE-PLUGIN-INSTALLATION.md` - Full guide
- `TOSSEE-DEBUG-GUIDE.md` - Troubleshooting
- `tossee-integration-example.php` - Code examples

---

**Version:** 1.0.0
**Author:** Andrius / Tossee
**License:** Proprietary
