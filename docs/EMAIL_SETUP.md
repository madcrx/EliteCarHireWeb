# Email System Setup Guide

## Overview

Elite Car Hire uses a queue-based email system to send notifications to admins, owners, and customers. Emails are queued in the database and processed by a background cron job using SMTP.

## Current Issue

**Emails are not being sent** because:
1. ❌ PHPMailer library is not installed
2. ❌ Cron jobs are not configured
3. ❌ SMTP settings may not be configured

## Complete Setup Instructions

### Step 1: Install PHPMailer

PHPMailer is required for reliable SMTP email sending.

**Option A: Using Composer (Recommended)**

If you have SSH access to your server:

```bash
cd /path/to/EliteCarHireWeb
composer install
```

This will install PHPMailer and create the `vendor/` directory.

**Option B: Manual Installation**

If you don't have Composer access:

1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract the files
3. Upload the PHPMailer folder to: `/path/to/EliteCarHireWeb/vendor/phpmailer/phpmailer/src/`
4. Ensure the following files exist:
   - `vendor/phpmailer/phpmailer/src/PHPMailer.php`
   - `vendor/phpmailer/phpmailer/src/SMTP.php`
   - `vendor/phpmailer/phpmailer/src/Exception.php`

### Step 2: Configure SMTP Settings

You need to configure SMTP credentials for sending emails. You can use:
- Gmail SMTP
- Your hosting provider's SMTP
- Third-party email services (SendGrid, Mailgun, etc.)

**Option A: Gmail SMTP (For Testing)**

⚠️ **Warning:** Gmail has daily sending limits. Use only for testing, not production.

1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use these settings:
   - SMTP Host: `smtp.gmail.com`
   - SMTP Port: `587`
   - SMTP Username: `your-email@gmail.com`
   - SMTP Password: `your-app-password`
   - SMTP Encryption: `tls`

**Option B: cPanel Email (Recommended for Hosting)**

Most web hosting providers offer email services:

1. Log into cPanel
2. Go to "Email Accounts"
3. Create an email account: `noreply@elitecarhire.au`
4. Get SMTP settings from cPanel (usually under "Email Accounts" → "Connect Devices")
5. Typical settings:
   - SMTP Host: `mail.elitecarhire.au` or `elitecarhire.au`
   - SMTP Port: `587` or `465`
   - SMTP Username: `noreply@elitecarhire.au`
   - SMTP Password: (the password you set)
   - SMTP Encryption: `tls` (for port 587) or `ssl` (for port 465)

**Option C: SendGrid / Mailgun (Recommended for Production)**

Professional email services with better deliverability:

**SendGrid:**
- Sign up at: https://sendgrid.com
- SMTP Host: `smtp.sendgrid.net`
- SMTP Port: `587`
- SMTP Username: `apikey`
- SMTP Password: (your SendGrid API key)

**Mailgun:**
- Sign up at: https://www.mailgun.com
- Get SMTP credentials from: Domain Settings → SMTP Credentials
- SMTP Host: `smtp.mailgun.org`
- SMTP Port: `587`
- SMTP Username: (provided by Mailgun)
- SMTP Password: (provided by Mailgun)

### Step 3: Set Environment Variables

**Method 1: Using .htaccess (cPanel/Apache)**

Edit `/home/user/EliteCarHireWeb/public/.htaccess` and add:

```apache
# Email Configuration
SetEnv SMTP_HOST "smtp.gmail.com"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USER "noreply@elitecarhire.au"
SetEnv SMTP_PASS "your-password-here"

# Email Addresses
SetEnv ADMIN_EMAIL "admin@elitecarhire.au"
SetEnv BOOKING_EMAIL "bookings@elitecarhire.au"
SetEnv PAYMENT_EMAIL "payments@elitecarhire.au"
SetEnv SUPPORT_EMAIL "support@elitecarhire.au"
```

Replace the values with your actual SMTP credentials.

**Method 2: Using .env file**

Create a file `/home/user/EliteCarHireWeb/.env`:

```bash
# SMTP Settings
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=noreply@elitecarhire.au
SMTP_PASS=your-password-here

# Email Addresses
ADMIN_EMAIL=admin@elitecarhire.au
BOOKING_EMAIL=bookings@elitecarhire.au
PAYMENT_EMAIL=payments@elitecarhire.au
SUPPORT_EMAIL=support@elitecarhire.au
```

### Step 4: Configure Cron Jobs

Emails are sent by a background cron job that processes the queue. You need to set up TWO cron jobs:

#### Cron Job 1: Process Email Queue (Every Minute)

This sends queued emails:

```bash
* * * * * /usr/bin/php /home/user/EliteCarHireWeb/scripts/process-email-queue.php >> /home/user/EliteCarHireWeb/storage/logs/email-queue.log 2>&1
```

#### Cron Job 2: Send Email Reminders (Every Hour)

This sends reminder emails for unanswered notifications:

```bash
0 * * * * /usr/bin/php /home/user/EliteCarHireWeb/cron/send_email_reminders.php >> /home/user/EliteCarHireWeb/storage/logs/email-reminders.log 2>&1
```

**How to Add Cron Jobs:**

**Via cPanel:**
1. Log into cPanel
2. Go to "Cron Jobs"
3. Add new cron job
4. Set command and frequency

**Via SSH:**
1. SSH into your server
2. Run: `crontab -e`
3. Add the two lines above
4. Save and exit

**Example for cPanel hosting:**
```
Minute: *
Hour: *
Day: *
Month: *
Weekday: *
Command: /usr/bin/php /home/yourusername/public_html/scripts/process-email-queue.php >> /home/yourusername/public_html/storage/logs/email-queue.log 2>&1
```

### Step 5: Create Log Directories

Create directories for log files:

```bash
mkdir -p /home/user/EliteCarHireWeb/storage/logs
chmod 755 /home/user/EliteCarHireWeb/storage/logs
```

Or via FTP: Create the `storage/logs` folder and set permissions to 755.

### Step 6: Test Email System

**Test 1: Queue an Email**

1. Log into the website as a customer
2. Go to Contact page and submit a message
3. Check if email is queued:

```sql
SELECT * FROM email_queue ORDER BY created_at DESC LIMIT 5;
```

You should see a row with `status = 'pending'`.

**Test 2: Process Queue Manually**

Run the email queue processor manually:

```bash
php /home/user/EliteCarHireWeb/scripts/process-email-queue.php
```

Expected output:
```
[INFO] Found 1 pending email(s) to process.
[PROCESSING] Email ID: 1 to admin@elitecarhire.au
[SUCCESS] Email ID: 1 sent successfully.

[SUMMARY] Processed: 1 | Success: 1 | Failed: 0
```

**Test 3: Check Email Status**

```sql
SELECT id, to_email, subject, status, sent_at, error_message
FROM email_queue
ORDER BY created_at DESC
LIMIT 10;
```

- `status = 'sent'` means email was sent successfully
- `status = 'failed'` means there was an error (check `error_message`)
- `status = 'pending'` means email is waiting to be sent

**Test 4: Check Logs**

```bash
tail -f /home/user/EliteCarHireWeb/storage/logs/email-queue.log
```

## Troubleshooting

### Issue: "PHPMailer Error: SMTP connect() failed"

**Cause:** Cannot connect to SMTP server

**Solutions:**
1. Verify SMTP host and port are correct
2. Check if your server's firewall blocks outgoing SMTP connections
3. Try different port: 587 (TLS) or 465 (SSL)
4. Contact your hosting provider to enable SMTP

### Issue: "SMTP Error: Could not authenticate"

**Cause:** Invalid username or password

**Solutions:**
1. Double-check SMTP username and password
2. For Gmail: Use App Password, not your regular password
3. Verify account is active and not locked

### Issue: Emails stuck in "pending" status

**Cause:** Cron job not running

**Solutions:**
1. Verify cron jobs are configured correctly
2. Check cron job logs: `grep CRON /var/log/syslog`
3. Run manually: `php scripts/process-email-queue.php`
4. Check file paths in cron job are absolute paths

### Issue: "PHP mail() function failed"

**Cause:** PHPMailer not installed, falling back to PHP mail()

**Solutions:**
1. Install PHPMailer (see Step 1)
2. Verify files exist: `vendor/phpmailer/phpmailer/src/PHPMailer.php`
3. Check PHP mail is enabled: `php -i | grep sendmail`

### Issue: Emails sent but not received

**Cause:** Email marked as spam or domain not configured

**Solutions:**
1. Check recipient's spam folder
2. Configure SPF records for your domain
3. Configure DKIM for your domain
4. Use a professional email service (SendGrid, Mailgun)
5. Send from a proper domain email (not Gmail for production)

### Issue: High bounce rate or spam complaints

**Solutions:**
1. Use double opt-in for newsletters
2. Include unsubscribe links
3. Send from verified domain
4. Don't send too many emails at once
5. Monitor email reputation

## Email Workflow

### How Email System Works

1. **Application triggers email:** User action (booking, contact form, etc.)
2. **Email queued:** `sendEmail()` function adds to `email_queue` table
3. **Cron job runs:** `process-email-queue.php` runs every minute
4. **Email sent:** Script sends via SMTP using PHPMailer
5. **Status updated:** Email marked as 'sent' or 'failed' in database

### Email Types Sent by System

1. **Booking Confirmations** - To customers and owners
2. **Vehicle Approval Notifications** - To admins and owners
3. **Payment Confirmations** - To customers
4. **Cancellation Notifications** - To all parties
5. **Contact Form Submissions** - To admins
6. **Reminder Emails** - For unanswered notifications

## Production Checklist

Before going live, ensure:

- [ ] PHPMailer is installed
- [ ] SMTP credentials are configured
- [ ] Environment variables are set
- [ ] Both cron jobs are running
- [ ] Log directories exist and are writable
- [ ] Test email sent successfully
- [ ] Check spam folder settings
- [ ] SPF/DKIM records configured
- [ ] Using professional email service (not Gmail)
- [ ] Monitoring email queue for errors

## Monitoring

### Check Email Queue Status

```sql
-- Count by status
SELECT status, COUNT(*) as count
FROM email_queue
GROUP BY status;

-- Recent failures
SELECT id, to_email, subject, error_message, attempts
FROM email_queue
WHERE status = 'failed'
ORDER BY created_at DESC
LIMIT 10;

-- Pending emails older than 1 hour
SELECT id, to_email, subject, created_at
FROM email_queue
WHERE status = 'pending'
AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

### Check Cron Job Logs

```bash
# Email queue log
tail -100 /home/user/EliteCarHireWeb/storage/logs/email-queue.log

# Email reminders log
tail -100 /home/user/EliteCarHireWeb/storage/logs/email-reminders.log

# System cron log
grep CRON /var/log/syslog | grep email
```

## Security Notes

1. **Never commit SMTP passwords to Git**
2. Use environment variables for all credentials
3. Use App Passwords for Gmail (not your main password)
4. Enable 2FA on all email accounts
5. Rotate SMTP passwords regularly
6. Monitor for unauthorized email sending
7. Set up rate limiting to prevent abuse

## Support

If you continue to have email issues:

1. Check error logs: `storage/logs/email-queue.log`
2. Test manually: `php scripts/process-email-queue.php`
3. Verify SMTP settings with your email provider
4. Contact hosting provider for SMTP support
5. Consider using professional email service (SendGrid, Mailgun)
