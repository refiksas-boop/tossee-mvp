# Tossee Chat Report System Documentation

## Overview

The Tossee Chat Report System allows users to report inappropriate behavior during video chat sessions. Reports are stored in the database, administrators are notified, and the reporting user is automatically disconnected from the chat after submitting a report.

## Components

### 1. Database Tables

#### `wp_tossee_reports`
Stores all report submissions.

**Columns:**
- `id` - Unique report identifier
- `reporter_id` - Tossee ID of the user who submitted the report
- `reported_user_id` - Tossee ID of the user being reported
- `report_reason` - The reason for the report
- `additional_details` - Optional message from the reporter
- `report_status` - Status: 'pending', 'reviewed', or 'resolved'
- `created_at` - Timestamp of report submission
- `reviewed_at` - Timestamp when report was reviewed
- `reviewed_by` - WordPress user ID of the admin who reviewed it

#### `wp_tossee_notifications`
Stores in-app notifications for administrators.

**Columns:**
- `id` - Unique notification identifier
- `notification_type` - Type of notification (e.g., 'new_report')
- `reference_id` - ID of the related report
- `message` - Notification message text
- `is_read` - Boolean flag (0 = unread, 1 = read)
- `created_at` - Timestamp of notification creation

### 2. Files

#### `database-setup.php`
- Creates the `wp_tossee_reports` table on WordPress initialization
- Runs automatically when WordPress loads

#### `tossee-api.php`
REST API endpoints for the report system:

**Endpoints:**

1. **POST** `/wp-json/tossee/v1/report`
   - Submit a new report
   - No authentication required
   - Parameters:
     - `reporter_id` (required)
     - `reported_user_id` (required)
     - `report_reason` (required): 'harassment', 'nudity', or 'spam'
     - `additional_details` (optional)

2. **GET** `/wp-json/tossee/v1/reports`
   - Get all reports (admin only)
   - Query parameters:
     - `status`: Filter by status
     - `page`: Page number
     - `per_page`: Items per page

3. **PATCH** `/wp-json/tossee/v1/report/{id}`
   - Update report status (admin only)
   - Body: `{ "status": "resolved" }`

4. **GET** `/wp-json/tossee/v1/notifications`
   - Get notifications (admin only)

5. **POST** `/wp-json/tossee/v1/notifications/mark-read/{id}`
   - Mark notification as read (admin only)

#### `chat.html`
Chat interface with integrated report functionality:

**Features:**
- Video chat interface with local and remote video streams
- "Report" button in controls bar
- Report modal with three predefined reasons:
  - Harassment or offensive behavior
  - Nudity or sexual content
  - Spam, scam, or advertising
- Optional additional details textarea
- Automatic disconnection after report submission
- Success message confirmation

**User Flow:**
1. User clicks "Report" button during chat
2. Modal opens with report options
3. User selects a reason (required)
4. User optionally adds details
5. User clicks "Report" button
6. Modal closes, success message shows
7. User is disconnected from chat
8. User is redirected to lobby

#### `admin-reports.php`
Admin panel for viewing and managing reports:

**Features:**
- Dashboard with statistics:
  - Pending reports count
  - Total reports count
  - Resolved reports count
  - Reviewed reports count
- Filter reports by status
- View detailed report information
- Mark reports as resolved
- Real-time notification badge
- Pagination support

#### `functions.php`
Updated to include:
- `require_once get_stylesheet_directory() . '/database-setup.php';`
- `require_once get_stylesheet_directory() . '/tossee-api.php';`

## Installation & Setup

### 1. File Upload
Upload all files to your WordPress theme directory:
- `database-setup.php`
- `tossee-api.php`
- `chat.html`
- `admin-reports.php`

### 2. Database Tables
The tables will be created automatically when WordPress loads. To verify:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'wp_tossee_reports';
SHOW TABLES LIKE 'wp_tossee_notifications';

-- View table structure
DESCRIBE wp_tossee_reports;
DESCRIBE wp_tossee_notifications;
```

### 3. Accessing the Chat
Navigate to: `https://your-domain.com/chat.html?uid=USER_TOSSEE_ID`

Replace `USER_TOSSEE_ID` with the actual Tossee ID from the session or database.

### 4. Accessing the Admin Panel
Navigate to: `https://your-domain.com/admin-reports.php`

**Note:** You must be logged in as a WordPress administrator to view reports.

## Usage Guide

### For Users

1. **During Chat:**
   - Click the "Report" button in the bottom controls bar

2. **Submitting a Report:**
   - Select one of the three report reasons
   - Optionally add additional details
   - Click "Report" to submit
   - You will see a confirmation message
   - You will be automatically disconnected from the chat

### For Administrators

1. **Viewing Reports:**
   - Navigate to `admin-reports.php`
   - See all reports in the table
   - Filter by status using the dropdown

2. **Report Details:**
   - Click "View" button on any report
   - See full details in the modal
   - View reporter and reported user information
   - Read additional details if provided

3. **Managing Reports:**
   - Click "Resolve" button to mark as resolved
   - Reports can have three statuses:
     - **Pending**: Newly submitted, awaiting review
     - **Reviewed**: Viewed by admin but not yet resolved
     - **Resolved**: Completed and closed

4. **Notifications:**
   - Red badge shows unread notification count
   - Notifications are created automatically when reports are submitted

## Report Reasons

### 1. Harassment or offensive behavior
- Verbal abuse
- Threatening language
- Bullying or intimidation
- Hate speech
- Discriminatory comments

### 2. Nudity or sexual content
- Inappropriate nudity
- Sexual behavior
- Sexual harassment
- Explicit content

### 3. Spam, scam, or advertising
- Commercial advertising
- Spam messages
- Scam attempts
- Phishing
- Unwanted promotional content

## Security Features

1. **Input Sanitization:**
   - All user inputs are sanitized using WordPress functions
   - `sanitize_text_field()` for short text
   - `sanitize_textarea_field()` for longer text

2. **SQL Injection Prevention:**
   - Uses WordPress `$wpdb->prepare()` for all queries
   - Parameterized queries throughout

3. **Authentication:**
   - Admin endpoints require `manage_options` capability
   - User reports are logged even without authentication (to catch anonymous abuse)

4. **Data Validation:**
   - Required fields are checked
   - Report reasons are validated against allowed values
   - Status updates are validated

## API Response Examples

### Successful Report Submission
```json
{
  "success": true,
  "message": "Report submitted successfully",
  "report_id": 123
}
```

### Get Reports Response
```json
{
  "success": true,
  "reports": [
    {
      "id": 123,
      "reporter_id": "user_abc123",
      "reported_user_id": "user_xyz789",
      "report_reason": "Harassment or offensive behavior",
      "additional_details": "User was using abusive language",
      "report_status": "pending",
      "created_at": "2025-12-23 10:30:00",
      "reporter_username": "john_doe",
      "reporter_email": "john@example.com",
      "reported_username": "jane_smith",
      "reported_email": "jane@example.com"
    }
  ],
  "total": 45,
  "page": 1,
  "per_page": 20,
  "total_pages": 3
}
```

### Error Response
```json
{
  "code": "missing_fields",
  "message": "Missing required fields",
  "data": {
    "status": 400
  }
}
```

## Future Enhancements

### Recommended Features:
1. **Email Notifications**
   - Send email to admins when reports are submitted
   - Uncomment line in `tossee_send_report_notification()`

2. **User Blocking**
   - Automatically block users after multiple reports
   - Add `is_blocked` column to users table

3. **Report Analytics**
   - Generate reports by date range
   - Identify frequently reported users
   - Track report trends

4. **Appeal System**
   - Allow reported users to appeal
   - Admin can review appeals

5. **Moderation Queue**
   - Priority queue based on report severity
   - Auto-escalation for multiple reports on same user

6. **WebRTC Integration**
   - Integrate with actual WebRTC signaling
   - Store chat session IDs with reports
   - Include chat duration and connection info

## Troubleshooting

### Reports not saving
- Check database permissions
- Verify tables were created: `SHOW TABLES LIKE 'wp_tossee_%'`
- Check error logs: `wp-content/debug.log`

### Admin panel showing errors
- Verify user is logged in as administrator
- Check `manage_options` capability
- Clear browser cache

### API endpoints not working
- Verify `tossee-api.php` is included in `functions.php`
- Check WordPress permalink settings
- Test endpoint directly in browser

### Database connection errors
- Verify database credentials in `wp-config.php`
- Check database server is running
- Ensure user has correct permissions

## Support

For questions or issues, please contact the development team or submit an issue in the project repository.

---

**Version:** 1.0.0
**Last Updated:** December 23, 2025
**Author:** Tossee Development Team
