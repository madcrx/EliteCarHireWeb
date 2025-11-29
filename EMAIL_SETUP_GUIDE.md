# Email Configuration Guide for Elite Car Hire

This guide explains how to set up email functionality for Elite Car Hire on your live server.

## Overview

Elite Car Hire uses a **queue-based email system**:
1. When events occur (bookings, confirmations, replies, etc.), emails are added to the `email_queue` database table
2. A cron job runs every minute to process pending emails
3. Emails are sent via SMTP (Simple Mail Transfer Protocol)

## Email Events

The system sends emails for:
- **Contact form replies** - Admin responses to customer inquiries
- **Booking confirmations** - New booking notifications to customers and owners
- **Booking updates** - Changes to existing bookings
- **Payment confirmations** - Payment received notifications
- **Cancellations** - Booking cancellation notifications
- **User registrations** - Welcome emails and account approvals
- **Password resets** - Security-related emails

---

## Step 1: Configure SMTP Settings

### Option A: Using cPanel Email Account (Recommended for cPanel hosting)

If you're hosting with cPanel (like CyberLogic IT), use your domain's mail server:

1. **Create an email account** in cPanel:
   - Go to: **cPanel → Email Accounts**
   - Create: `noreply@elitecarhire.au` or `support@elitecarhire.au`
   - Set a strong password

2. **Get SMTP settings**:
   - **SMTP Host**: `mail.elitecarhire.au` (or your hosting's mail server)
   - **SMTP Port**: `587` (TLS) or `465` (SSL)
   - **SMTP Username**: `support@elitecarhire.au` (the full email address)
   - **SMTP Password**: The password you set in step 1
   - **Encryption**: `tls` (preferred) or `ssl`

3. **Add to your server's environment variables** (see Step 2 below)

### Option B: Using Gmail SMTP

If you have a Gmail account (or G Suite/Google Workspace):

1. **Enable 2-Factor Authentication** on your Google account
2. **Create an App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Name it "Elite Car Hire"
   - Copy the 16-character password

3. **Use these settings**:
   - **SMTP Host**: `smtp.gmail.com`
   - **SMTP Port**: `587`
   - **SMTP Username**: Your Gmail address (e.g., `youremail@gmail.com`)
   - **SMTP Password**: The App Password from step 2
   - **Encryption**: `tls`

### Option C: Using Third-Party Email Services

For high-volume email, consider:
- **SendGrid** (sendgrid.com) - Free tier: 100 emails/day
- **Mailgun** (mailgun.com) - Free tier: 5,000 emails/month
- **Amazon SES** (aws.amazon.com/ses) - Pay-as-you-go pricing

Each service provides SMTP credentials after signup.

---

## Step 2: Set Environment Variables on Live Server

### Method 1: Using .htaccess (Recommended for shared hosting)

Edit `/public_html/public/.htaccess` and add:

```apache
<IfModule mod_env.c>
    SetEnv SMTP_HOST "mail.elitecarhire.au"
    SetEnv SMTP_PORT "587"
    SetEnv SMTP_USER "support@elitecarhire.au"
    SetEnv SMTP_PASS "your_email_password_here"
</IfModule>
```

**Security Note**: Ensure `.htaccess` file has proper permissions (644) and is not publicly accessible.

### Method 2: Using .env file

1. Create file: `/public_html/.env`

```env
SMTP_HOST=mail.elitecarhire.au
SMTP_PORT=587
SMTP_USER=support@elitecarhire.au
SMTP_PASS=your_email_password_here
```

2. Load .env in `/public_html/config/app.php`:

```php
// Load .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}
```

3. Add `.env` to `.gitignore`:

```bash
echo ".env" >> .gitignore
```

### Method 3: Direct configuration in config file (NOT RECOMMENDED for production)

Edit `/public_html/config/app.php`:

```php
'email' => [
    'from_address' => 'support@elitecarhire.au',
    'from_name' => 'Elite Car Hire',
    'smtp_host' => 'mail.elitecarhire.au',  // Change this
    'smtp_port' => 587,                      // Change this
    'smtp_username' => 'support@elitecarhire.au',  // Change this
    'smtp_password' => 'your_password_here',       // Change this
    'smtp_encryption' => 'tls',
],
```

⚠️ **Security Warning**: This method exposes credentials in version control. Only use for testing.

---

## Step 3: Install PHPMailer (Optional but Recommended)

PHPMailer provides robust SMTP email sending with better error handling.

### Via Composer (if available):

```bash
cd /home/cyberlog/public_html/ech.cyberlogicit.com.au
composer require phpmailer/phpmailer
```

### Manual Installation:

1. Download: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
2. Extract to: `/public_html/vendor/phpmailer/phpmailer/`
3. Ensure this structure:
   ```
   vendor/phpmailer/phpmailer/
   ├── src/
   │   ├── PHPMailer.php
   │   ├── SMTP.php
   │   └── Exception.php
   ```

The email processor will automatically detect and use PHPMailer if available, otherwise it falls back to PHP's `mail()` function.

---

## Step 4: Set Up Cron Job

The cron job processes the email queue every minute.

### Via cPanel:

1. **Log in to cPanel**
2. Navigate to: **Advanced → Cron Jobs**
3. **Add New Cron Job**:
   - **Common Settings**: Every minute (*/1 * * * *)
   - **Command**:
     ```bash
     /usr/bin/php /home/cyberlog/public_html/ech.cyberlogicit.com.au/scripts/process-email-queue.php >> /home/cyberlog/public_html/ech.cyberlogicit.com.au/storage/logs/email-cron.log 2>&1
     ```

4. **Click "Add New Cron Job"**

### Manual Crontab Setup:

```bash
crontab -e
```

Add:
```
* * * * * /usr/bin/php /home/cyberlog/public_html/ech.cyberlogicit.com.au/scripts/process-email-queue.php >> /home/cyberlog/public_html/ech.cyberlogicit.com.au/storage/logs/email-cron.log 2>&1
```

### Verify Cron Job:

```bash
# List active cron jobs
crontab -l

# Check cron log
tail -f /home/cyberlog/public_html/ech.cyberlogicit.com.au/storage/logs/email-cron.log
```

---

## Step 5: Make Email Processor Executable

```bash
chmod +x /home/cyberlog/public_html/ech.cyberlogicit.com.au/scripts/process-email-queue.php
```

---

## Step 6: Test Email Functionality

### Test 1: Manual Email Queue Test

Add a test email to the queue:

```sql
INSERT INTO email_queue (to_email, to_name, subject, body_html, status)
VALUES ('your-email@example.com', 'Test User', 'Elite Car Hire Email Test',
        '<h1>Test Email</h1><p>If you receive this, email is working correctly!</p>', 'pending');
```

### Test 2: Run Processor Manually

```bash
php /home/cyberlog/public_html/ech.cyberlogicit.com.au/scripts/process-email-queue.php
```

Expected output:
```
[INFO] Found 1 pending email(s) to process.
[PROCESSING] Email ID: 1 to your-email@example.com
[SUCCESS] Email ID: 1 sent successfully.

[SUMMARY] Processed: 1 | Success: 1 | Failed: 0
```

### Test 3: Test via Admin Panel

1. Log in as Admin
2. Go to: **Contact Submissions**
3. Reply to a contact submission
4. Check if email appears in queue and gets sent

---

## Troubleshooting

### Issue: "SMTP connection failed"

**Solution:**
- Verify SMTP credentials are correct
- Check SMTP port is not blocked by firewall
- Try alternate ports: 587 (TLS), 465 (SSL), 25 (plain)
- Contact your hosting provider to ensure SMTP is allowed

### Issue: "Authentication failed"

**Solution:**
- Ensure you're using the FULL email address as username (e.g., `support@elitecarhire.au`)
- Verify password is correct (no extra spaces)
- For Gmail: Ensure you're using an App Password, not your account password
- Check if 2FA is required

### Issue: Emails stuck in queue (status = 'pending')

**Solution:**
- Verify cron job is running: `crontab -l`
- Check cron log: `tail -f storage/logs/email-cron.log`
- Run processor manually to see errors:
  ```bash
  php scripts/process-email-queue.php
  ```

### Issue: Emails marked as 'failed'

**Solution:**
- Check `email_queue.error_message` column:
  ```sql
  SELECT id, to_email, error_message FROM email_queue WHERE status = 'failed' ORDER BY created_at DESC LIMIT 10;
  ```
- Address the specific error message
- Reset failed emails to retry:
  ```sql
  UPDATE email_queue SET status = 'pending', attempts = 0 WHERE status = 'failed';
  ```

### Issue: Emails going to spam

**Solution:**
- **Set up SPF record** in DNS:
  ```
  v=spf1 a mx include:_spf.elitecarhire.au ~all
  ```
- **Set up DKIM** (check with hosting provider)
- **Use professional domain email** (not Gmail)
- **Verify email content** isn't triggering spam filters

---

## Monitoring Email Queue

### Check Queue Status:

```sql
-- Count pending emails
SELECT COUNT(*) FROM email_queue WHERE status = 'pending';

-- Check recent sent emails
SELECT * FROM email_queue WHERE status = 'sent' ORDER BY sent_at DESC LIMIT 10;

-- Check failed emails
SELECT * FROM email_queue WHERE status = 'failed' ORDER BY created_at DESC LIMIT 10;
```

### Clear Old Sent Emails (Optional Maintenance):

```sql
-- Delete sent emails older than 30 days
DELETE FROM email_queue WHERE status = 'sent' AND sent_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## Security Best Practices

1. ✅ **Never commit SMTP credentials** to version control
2. ✅ **Use environment variables** for sensitive configuration
3. ✅ **Restrict file permissions**:
   ```bash
   chmod 600 .env
   chmod 644 .htaccess
   ```
4. ✅ **Use App Passwords** for Gmail (not your main password)
5. ✅ **Enable rate limiting** to prevent email bombing
6. ✅ **Monitor email queue** for suspicious activity
7. ✅ **Use TLS encryption** (port 587) for SMTP

---

## Quick Reference: Email Settings by Provider

| Provider | SMTP Host | Port | Encryption | Notes |
|----------|-----------|------|------------|-------|
| **cPanel (Generic)** | `mail.yourdomain.com` | 587 | TLS | Use full email as username |
| **Gmail** | `smtp.gmail.com` | 587 | TLS | Requires App Password |
| **G Suite/Workspace** | `smtp.gmail.com` | 587 | TLS | Use workspace email |
| **SendGrid** | `smtp.sendgrid.net` | 587 | TLS | Use API key as password |
| **Mailgun** | `smtp.mailgun.org` | 587 | TLS | Get credentials from dashboard |
| **Amazon SES** | `email-smtp.region.amazonaws.com` | 587 | TLS | Use SMTP credentials (not IAM) |
| **Outlook/Office 365** | `smtp.office365.com` | 587 | TLS | Use full email as username |

---

## Support

For additional help:
- **Email**: support@elitecarhire.au
- **Phone**: 0406 907 849
- **Hosting Support**: CyberLogic IT

---

## Checklist

- [ ] SMTP credentials obtained
- [ ] Environment variables configured
- [ ] PHPMailer installed (optional)
- [ ] Cron job created and active
- [ ] Email processor script is executable
- [ ] Test email sent successfully
- [ ] Admin contact reply tested
- [ ] Monitoring email queue regularly
- [ ] SPF/DKIM records configured (optional)
