# Email Reminders System

## Overview

The email reminder system automatically sends follow-up emails for unanswered notifications after 12 hours. This ensures that important actions (booking requests, vehicle approvals, cancellations, contact forms) don't get overlooked.

## How It Works

1. **Tracking**: When a notification email is sent (booking request, vehicle approval request, etc.), it's tracked in the `email_reminders` database table
2. **Monitoring**: A cron job runs hourly to check for emails that were sent more than 12 hours ago without a response
3. **Reminders**: If no action has been taken, a reminder email is sent to the recipient
4. **Response Detection**: When the recipient takes action (approves, rejects, responds), the system marks the email as responded and stops sending reminders

## Database Schema

The system uses the `email_reminders` table:

```sql
CREATE TABLE email_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_type VARCHAR(50) NOT NULL,           -- booking_request, vehicle_approval, etc.
    entity_type VARCHAR(50) NOT NULL,          -- booking, vehicle, pending_change, contact_submission
    entity_id INT NOT NULL,                    -- ID of the related entity
    recipient_email VARCHAR(255) NOT NULL,     -- Email address of recipient
    subject VARCHAR(255) NOT NULL,             -- Email subject line
    sent_at DATETIME NOT NULL,                 -- When the original email was sent
    reminder_sent_at DATETIME NULL,            -- When the reminder was sent
    response_received TINYINT(1) DEFAULT 0,    -- 1 if action was taken
    response_received_at DATETIME NULL,        -- When the response was received
    ...
);
```

## Supported Email Types

### 1. Booking Requests (`booking_request`)
- **Sent to**: Vehicle owner
- **When**: New booking is created
- **Response**: Owner confirms or rejects the booking
- **Entity**: `booking` table

### 2. Vehicle Approvals (`vehicle_approval`)
- **Sent to**: Admin
- **When**: Owner submits a new vehicle listing
- **Response**: Admin approves or rejects the vehicle
- **Entity**: `vehicle` table

### 3. Cancellation Requests (`cancellation_request`)
- **Sent to**: Admin
- **When**: Customer requests a booking cancellation
- **Response**: Admin approves or rejects the cancellation
- **Entity**: `pending_change` table

### 4. Contact Forms (`contact_form`)
- **Sent to**: Admin
- **When**: User submits a contact form
- **Response**: Admin replies to the inquiry
- **Entity**: `contact_submission` table

## Setup Instructions

### 1. Run Database Migration

The migration should already be complete if you followed the installation process. To verify or re-run:

```bash
php database/run_migration.php database/migrations/create_email_reminders_table.sql
```

### 2. Configure Cron Job

Add the following line to your crontab to run the reminder check every hour:

```bash
# Open crontab editor
crontab -e

# Add this line (replace paths with your actual paths):
0 * * * * /usr/bin/php /path/to/EliteCarHireWeb/cron/send_email_reminders.php >> /path/to/logs/email_reminders.log 2>&1
```

**Example for production:**
```bash
0 * * * * /usr/bin/php /var/www/html/EliteCarHireWeb/cron/send_email_reminders.php >> /var/www/html/storage/logs/email_reminders.log 2>&1
```

### 3. Create Log Directory (if needed)

```bash
mkdir -p /path/to/logs
chmod 755 /path/to/logs
```

### 4. Verify Setup

To test the cron job manually:

```bash
php cron/send_email_reminders.php
```

Expected output:
```
[2025-11-27 10:00:00] Starting email reminder check...
[2025-11-27 10:00:00] No emails need reminders at this time.
[2025-11-27 10:00:00] Email reminder check completed.
```

## Helper Functions

The system provides these helper functions (defined in `app/helpers.php`):

### `trackEmailForReminder($emailType, $entityType, $entityId, $recipientEmail, $subject)`
Tracks an email for potential reminder sending.

**Parameters:**
- `$emailType`: Type of email (booking_request, vehicle_approval, cancellation_request, contact_form)
- `$entityType`: Entity type (booking, vehicle, pending_change, contact_submission)
- `$entityId`: ID of the related entity
- `$recipientEmail`: Email address of recipient
- `$subject`: Email subject line

**Example:**
```php
$vehiclesEmail = config('email.vehicle_approvals');
$subject = "New Vehicle Submission - {$vehicleName}";
sendEmail($vehiclesEmail, $subject, $body);
trackEmailForReminder('vehicle_approval', 'vehicle', $vehicleId, $vehiclesEmail, $subject);
```

### `markEmailReminderResponded($entityType, $entityId)`
Marks an email reminder as responded when action is taken.

**Parameters:**
- `$entityType`: Entity type (booking, vehicle, pending_change, contact_submission)
- `$entityId`: Entity ID

**Example:**
```php
// When admin approves a vehicle
db()->execute("UPDATE vehicles SET status = 'approved' WHERE id = ?", [$id]);
markEmailReminderResponded('vehicle', $id);
```

### `getEmailsNeedingReminders()`
Gets all emails that need reminders (sent >12 hours ago, no response, no reminder sent yet).

**Returns:** Array of email reminders

### `markReminderSent($reminderId)`
Marks a reminder as sent.

**Parameters:**
- `$reminderId`: Reminder ID

## Integration Points

The system is already integrated at these points:

### Tracking (When Emails Are Sent)
1. **BookingController** - New booking requests to owners
2. **OwnerController** - Vehicle submissions to admin
3. **CustomerController** - Cancellation requests to admin
4. **PublicController** - Contact form submissions to admin

### Response Marking (When Actions Are Taken)
1. **OwnerController** - Booking confirmations/rejections
2. **AdminController** - Vehicle approvals/rejections
3. **AdminController** - Cancellation approvals
4. **AdminController** - Contact form replies

## Monitoring

### Check Reminder Logs
```bash
tail -f /path/to/logs/email_reminders.log
```

### Query Database
```sql
-- See all pending reminders
SELECT * FROM email_reminders
WHERE response_received = 0
AND reminder_sent_at IS NULL
ORDER BY sent_at;

-- See reminders sent in last 24 hours
SELECT * FROM email_reminders
WHERE reminder_sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Count pending vs responded
SELECT
    response_received,
    COUNT(*) as count
FROM email_reminders
GROUP BY response_received;
```

## Troubleshooting

### Reminders Not Being Sent

1. **Check cron job is running:**
```bash
grep "CRON" /var/log/syslog | grep "send_email_reminders"
```

2. **Check for errors in log:**
```bash
tail -n 100 /path/to/logs/email_reminders.log
```

3. **Manually run the cron script:**
```bash
php cron/send_email_reminders.php
```

### Emails Marked as Responded But Still Getting Reminders

Check if the `markEmailReminderResponded()` function is being called when actions are taken. Add logging:
```php
error_log("Marking email reminder responded for {$entityType} #{$entityId}");
markEmailReminderResponded($entityType, $entityId);
```

### Database Connection Issues

Ensure the `app/bootstrap.php` file exists and properly loads the database configuration.

## Configuration

### Change Reminder Interval

Edit the query in `app/helpers.php` function `getEmailsNeedingReminders()`:

```php
// Default: 12 hours
AND sent_at < DATE_SUB(NOW(), INTERVAL 12 HOUR)

// Change to 24 hours:
AND sent_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)

// Change to 6 hours:
AND sent_at < DATE_SUB(NOW(), INTERVAL 6 HOUR)
```

### Customize Reminder Email Templates

Edit the reminder email templates in `cron/send_email_reminders.php`:
- `sendBookingRequestReminder()`
- `sendVehicleApprovalReminder()`
- `sendCancellationRequestReminder()`
- `sendContactFormReminder()`

## Future Enhancements

Potential improvements to consider:

1. **Multiple Reminders**: Send additional reminders after 24, 48 hours
2. **Escalation**: CC other admins if still no response after X hours
3. **Dashboard Widget**: Show pending reminder count on admin dashboard
4. **Email Preferences**: Allow users to configure reminder frequency
5. **SMS Reminders**: Send SMS for urgent items
6. **Statistics**: Track response times and reminder effectiveness
