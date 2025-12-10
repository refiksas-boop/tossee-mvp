# Tossee Core Plugin - Installation Guide

## Overview
Tossee Core is a custom WordPress plugin that creates a separate user system with its own database table, registration/login flow, and REST API for profile management.

## Features
- ✅ Custom user database (`wp_tossee_users`)
- ✅ Registration form with photo verification
- ✅ Login/logout system
- ✅ REST API endpoint for profile data
- ✅ Admin panel for user management
- ✅ Session-based authentication across subdomains

## Installation Steps

### 1. Upload Plugin
Copy `tossee-core.php` to your WordPress plugins directory:
```bash
wp-content/plugins/tossee-core/tossee-core.php
```

### 2. Activate Plugin
Go to WordPress Admin → Plugins → Activate "Tossee Core"

This will automatically create the `wp_tossee_users` database table.

### 3. Create Registration Page
1. Go to WordPress Admin → Pages → Add New
2. Title: "Register"
3. Add shortcode: `[tossee_register_form]`
4. Publish the page

### 4. Upload Profile Page
1. Upload `profile.html` to your theme or create a custom page template
2. Make sure it's accessible at `/profile` or your preferred URL
3. The profile page will automatically fetch data from `/wp-json/tossee/v1/profile`

## How It Works

### Registration Flow
1. User fills registration form (username, email, password, date of birth, photo)
2. Form validates age (18+) and photo
3. User accepts Terms & Conditions
4. Handler saves data to `wp_tossee_users` table
5. Session and cookie are set with `tossee_id`
6. User is redirected to `chat.tossee.com` with UID parameter

### Login Flow
1. User enters email and password
2. Handler verifies credentials against `wp_tossee_users` table
3. Session and cookie are set with `tossee_id`
4. User is redirected to chat subdomain

### Profile Display
1. Profile page loads
2. JavaScript fetches `/wp-json/tossee/v1/profile`
3. API checks for session/cookie `tossee_uid` or `tossee_id`
4. Returns user data (username, email, dob, photo, etc.)
5. JavaScript populates profile fields

## REST API Endpoints

### GET `/wp-json/tossee/v1/profile`
Returns current user's profile data.

**Authentication:** Session/Cookie based (`tossee_uid` or `tossee_id`)

**Response:**
```json
{
  "tossee_id": "tossee_abc123",
  "username": "john_doe",
  "email": "john@example.com",
  "first_name": "",
  "last_name": "",
  "gender": "",
  "country": "",
  "city": "",
  "dob": "1990-01-15",
  "photo": "data:image/png;base64,...",
  "created_at": "2025-12-10 10:30:00"
}
```

**Error Response (401):**
```json
{
  "code": "not_logged_in",
  "message": "User not logged in",
  "data": {
    "status": 401
  }
}
```

## Database Structure

### Table: `wp_tossee_users`
- `id` - Primary key
- `tossee_id` - Unique user identifier (e.g., "tossee_abc123")
- `username` - Unique username
- `email` - Unique email address
- `password_hash` - Password hash (bcrypt)
- `first_name` - First name (optional)
- `last_name` - Last name (optional)
- `gender` - Gender (optional)
- `country` - Country (optional)
- `city` - City (optional)
- `dob` - Date of birth (required)
- `photo` - Base64 encoded photo (required)
- `is_blocked` - Block status (0 or 1)
- `notes` - Admin notes (text)
- `created_at` - Registration timestamp
- `updated_at` - Last update timestamp

## Admin Panel

Access the Tossee Users panel at:
**WordPress Admin → Tossee Users**

Features:
- View all registered users
- View user details
- Block/unblock users
- Reset passwords (sends email)
- Delete users
- Add admin notes

## Session Management

The plugin uses both PHP sessions and cookies for authentication:
- Session variable: `$_SESSION['tossee_id']` or `$_SESSION['tossee_uid']`
- Cookie: `tossee_uid` (domain: `.tossee.com`, 30 days)

This allows authentication to work across subdomains (e.g., `chat.tossee.com`, `tossee.com`).

## Current Limitations

**Note:** Currently, the registration form only collects:
- Username
- Email
- Password
- Date of birth
- Photo

The following fields will show "-" in the profile until you add them to the registration form or create an edit profile feature:
- First name
- Last name
- Gender
- Country
- City
- Hobbies
- About

## Troubleshooting

### Profile shows "not logged in"
- Check that session is started (plugin handles this)
- Check browser cookies - `tossee_uid` should be set
- Check PHP session - `$_SESSION['tossee_id']` should exist

### Registration redirects but profile is empty
- Check that the `wp_tossee_users` table exists
- Verify registration actually inserted data (check database)
- Check browser console for JavaScript errors
- Verify API endpoint is accessible: `/wp-json/tossee/v1/profile`

### Photo not displaying
- Verify photo is stored as base64 with `data:image/` prefix
- Check browser console for errors
- Verify photo data is returned by API

## Security Notes

- Passwords are hashed using PHP's `password_hash()` (bcrypt)
- SQL queries use `$wpdb->prepare()` for protection against SQL injection
- Admin actions use WordPress nonces for CSRF protection
- User input is sanitized using WordPress functions
- Password hash is never exposed via REST API

## Support

For issues or questions, check the plugin code comments or WordPress debug logs (`WP_DEBUG` must be enabled).
