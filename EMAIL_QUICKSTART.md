# Email System Quick Start Guide

## 🚨 Problem: Emails Not Working

Your email notifications are not being sent because the email system needs to be configured.

## ✅ Quick Fix (5 Steps)

### Step 1: Install PHPMailer (2 minutes)

```bash
cd /home/user/EliteCarHireWeb
composer install
```

**Can't use composer?** See `docs/EMAIL_SETUP.md` for manual installation.

### Step 2: Configure SMTP Settings (3 minutes)

Edit `public/.htaccess` and uncomment/configure these lines (around line 70):

```apache
SetEnv SMTP_HOST "mail.elitecarhire.au"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USER "noreply@elitecarhire.au"
SetEnv SMTP_PASS "your-password-here"
```

**Don't have SMTP yet?** Use Gmail for testing:
```apache
SetEnv SMTP_HOST "smtp.gmail.com"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USER "your-email@gmail.com"
SetEnv SMTP_PASS "your-app-password"
```

Get Gmail App Password: https://myaccount.google.com/apppasswords

### Step 3: Set Up Cron Jobs (2 minutes)

**Via cPanel:**
1. Go to cPanel → Cron Jobs
2. Add two cron jobs:

**Cron Job 1** - Send queued emails (every minute):
```
* * * * * /usr/bin/php /home/user/EliteCarHireWeb/scripts/process-email-queue.php >> /home/user/EliteCarHireWeb/storage/logs/email-queue.log 2>&1
```

**Cron Job 2** - Send reminders (every hour):
```
0 * * * * /usr/bin/php /home/user/EliteCarHireWeb/cron/send_email_reminders.php >> /home/user/EliteCarHireWeb/storage/logs/email-reminders.log 2>&1
```

**Via SSH:**
```bash
crontab -e
```
Then paste the two lines above.

### Step 4: Create Log Directory

```bash
mkdir -p /home/user/EliteCarHireWeb/storage/logs
chmod 755 /home/user/EliteCarHireWeb/storage/logs
```

Or create via FTP: `storage/logs/` folder with 755 permissions.

### Step 5: Test Email

```bash
php /home/user/EliteCarHireWeb/scripts/process-email-queue.php
```

Expected output:
```
[INFO] No pending emails to process.
```

Or if there are queued emails:
```
[INFO] Found 1 pending email(s) to process.
[SUCCESS] Email ID: 1 sent successfully.
```

## ✅ Verification

**Check if PHPMailer is installed:**
```bash
ls -la vendor/phpmailer/phpmailer/src/PHPMailer.php
```

**Check if cron jobs are running:**
```bash
crontab -l | grep email
```

**Check email queue status:**
```sql
SELECT status, COUNT(*) as count FROM email_queue GROUP BY status;
```

## 🔧 Troubleshooting

**Issue:** "Composer: command not found"
- **Solution:** Install PHPMailer manually - see `docs/EMAIL_SETUP.md`

**Issue:** "SMTP connect() failed"
- **Solution:** Check SMTP host and port are correct
- **Solution:** Try port 465 with SSL instead of 587 with TLS

**Issue:** "Could not authenticate"
- **Solution:** Verify SMTP username and password
- **Solution:** For Gmail, use App Password not regular password

**Issue:** Emails still not sending
- **Solution:** Check cron jobs are configured: `crontab -l`
- **Solution:** Check logs: `tail -f storage/logs/email-queue.log`
- **Solution:** Run manually: `php scripts/process-email-queue.php`

## 📖 Full Documentation

For detailed setup instructions, see:
- **Full Email Setup Guide:** `docs/EMAIL_SETUP.md`
- **Email Reminders Guide:** `docs/EMAIL_REMINDERS.md`
- **SMTP Configuration Examples:** `public/.htaccess.email.example`

## 🆘 Need Help?

Common issues and solutions in `docs/EMAIL_SETUP.md` → Troubleshooting section
