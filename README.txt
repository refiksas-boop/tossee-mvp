=== Tossee Report System ===
Contributors: Tossee Team
Tags: reports, moderation, chat, admin, user-management
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Complete chat report system with user blocking, direct messaging, and comprehensive admin controls.

== Description ==

Tossee Report System is a comprehensive WordPress plugin that provides:

* **Chat Report System** - Users can report inappropriate behavior during video chats
* **Admin Dashboard** - View and manage all reports with filtering and search
* **User Blocking** - Block/unblock users with custom reasons
* **Direct Messaging** - Send messages directly to users from admin panel
* **Button Controls** - Enable/disable report button globally
* **Notifications** - Real-time notification system for new reports
* **Statistics Dashboard** - View pending, reviewed, and resolved reports

== Installation ==

1. Upload the `tossee-report-system` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Database tables will be created automatically on activation
4. Access admin panel from WordPress dashboard under "Tossee Reports"

== Frequently Asked Questions ==

= Where can I access the admin panel? =

After activation, go to WordPress Dashboard → Tossee Reports

= Where is the chat page? =

The chat page is available at: `https://your-site.com/?tossee_chat=1`

= How do I enable/disable the report button? =

Go to Tossee Reports → Settings and toggle the "Enable Report Button" option

= Can I customize the report reasons? =

The current version includes three predefined reasons:
- Harassment or offensive behavior
- Nudity or sexual content
- Spam, scam, or advertising

== Screenshots ==

1. Admin dashboard showing all reports
2. Report detail modal with user actions
3. Chat interface with report button
4. Settings page

== Changelog ==

= 1.0.0 =
* Initial release
* Chat report system
* Admin dashboard
* User blocking
* Direct messaging
* Settings control

== Upgrade Notice ==

= 1.0.0 =
Initial release

== Database Tables ==

This plugin creates the following database tables:

* `wp_tossee_users` - User accounts
* `wp_tossee_reports` - Report submissions
* `wp_tossee_admin_messages` - Admin messages to users
* `wp_tossee_notifications` - System notifications
* `wp_tossee_settings` - Plugin settings

== API Endpoints ==

The plugin provides REST API endpoints:

* POST `/wp-json/tossee/v1/report` - Submit a report
* GET `/wp-json/tossee/v1/reports` - Get all reports (admin)
* POST `/wp-json/tossee/v1/user/{id}/block` - Block user (admin)
* POST `/wp-json/tossee/v1/message` - Send message (admin)

== Support ==

For support, please visit https://tossee.com or open an issue on GitHub.
