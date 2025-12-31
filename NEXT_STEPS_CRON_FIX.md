# Next Steps to Fix Cron Job Issue

## Current Situation

You reported: **"cron not running - status is still pending"**

This means emails are being queued in the database but the cron job is not processing them.

## What I've Done

I've created comprehensive diagnostic tools to help you identify and fix the problem:

### 1. **Diagnostic Script** (`scripts/test-email-system.php`)

Run this first to see exactly what's working and what's broken:

```bash
cd /home/cp825575/public_html
php scripts/test-email-system.php
```

This will check:
- ✓ Bootstrap loading
- ✓ Database connection
- ✓ Pending emails in queue
- ✓ PHPMailer installation status
- ✓ SMTP configuration
- ✓ Directory permissions
- ✓ Email sending capability

### 2. **Cron Test Script** (`scripts/test-cron.php`)

Use this to verify your cron service is actually running:

Add as a test cron job:
```
* * * * * /usr/bin/php /home/cp825575/public_html/scripts/test-cron.php >> /home/cp825575/public_html/storage/logs/cron-test.log 2>&1
```

Wait 2-3 minutes, then check if `storage/logs/cron-test.log` was created.

### 3. **Quick Fix Guide** (`CRON_QUICK_FIX.md`)

Step-by-step guide with:
- Quick diagnostic steps
- Common errors and solutions
- Cron job testing procedures
- Complete fix checklist

### 4. **Detailed Troubleshooting** (`CRON_TROUBLESHOOTING.md`)

Comprehensive 250+ line guide covering:
- Path verification
- Manual testing procedures
- Log file checking
- Common error fixes
- Debugging checklist

### 5. **SQL Monitoring Queries** (`EMAIL_QUEUE_SQL_QUERIES.md`)

14 SQL queries you can run in phpMyAdmin to:
- Monitor pending emails
- Check email processing status
- Identify failed emails
- Diagnose cron issues

## What You Need to Do Now

Follow these steps **in order**:

### Step 1: Upload Latest Code Files

The latest fixes for the email system are in the repository. Make sure these files are uploaded to your server:

```
app/bootstrap.php
scripts/process-email-queue.php
scripts/test-email-system.php
scripts/test-cron.php
```

### Step 2: Run the Diagnostic Script

In cPanel Terminal:

```bash
cd /home/cp825575/public_html
php scripts/test-email-system.php
```

This will tell you exactly what's wrong.

### Step 3: Fix Based on Diagnostic Output

**If it says "PHPMailer NOT installed":**
```bash
composer install
```

**If it says "SMTP not configured":**
Edit `/home/cp825575/public_html/.htaccess` (root .htaccess) and add:

```apache
# ========================================
# Email Configuration
# ========================================
SetEnv SMTP_HOST "mail.elitecarhire.au"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USER "noreply@elitecarhire.au"
SetEnv SMTP_PASS "your-email-password-here"
SetEnv ADMIN_EMAIL "admin@elitecarhire.au"
SetEnv BOOKING_EMAIL "bookings@elitecarhire.au"
```

Get SMTP password from: cPanel → Email Accounts → noreply@elitecarhire.au → Connect Devices

**If it says "Directory not writable":**
```bash
chmod 755 /home/cp825575/public_html/storage/logs
```

### Step 4: Test Email Queue Processor Manually

```bash
cd /home/cp825575/public_html
php scripts/process-email-queue.php
```

**Expected output:**
```
[INFO] Found X pending email(s) to process.
[PROCESSING] Email ID: 1 to admin@elitecarhire.au
[SUCCESS] Email ID: 1 sent successfully.
```

**If you see errors,** refer to CRON_QUICK_FIX.md for solutions.

### Step 5: Verify Cron Job Configuration

In cPanel → Cron Jobs, verify your email queue processor cron job:

**Should be:**
```
Minute: *
Hour: *
Day: *
Month: *
Weekday: *
Command: /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

**Verify paths are correct:**
- Replace `/home/cp825575` with your actual username
- Replace `/usr/bin/php` with your actual PHP binary path (run `which php` in Terminal)

### Step 6: Test Cron Job is Running

Add the test cron job:
```
* * * * * /usr/bin/php /home/cp825575/public_html/scripts/test-cron.php >> /home/cp825575/public_html/storage/logs/cron-test.log 2>&1
```

Wait 2-3 minutes, then check if `storage/logs/cron-test.log` exists.

- **If file exists:** Cron is working! Problem is with the email script.
- **If no file:** Cron not running or paths are wrong.

### Step 7: Monitor Email Queue

In phpMyAdmin, run this query:

```sql
SELECT id, to_email, subject, status, attempts, error_message, created_at, last_attempt
FROM email_queue
WHERE status = 'pending'
ORDER BY created_at DESC;
```

If emails are stuck in "pending" with 0 attempts for > 5 minutes, the cron job is NOT running.

See `EMAIL_QUEUE_SQL_QUERIES.md` for more monitoring queries.

### Step 8: Check Logs

Check these log files for errors:

```bash
cd /home/cp825575/public_html

# Check cron output
tail -50 storage/logs/email-queue.log

# Check PHP errors
tail -50 storage/logs/error.log

# Check cron test
tail -50 storage/logs/cron-test.log
```

## Most Likely Issues

Based on "cron not running - status is still pending", the problem is likely one of these:

### 1. **Cron Job Has Wrong Paths** (Most Common)

Your username might not be `cp825575` or directory might not be `public_html`.

**Fix:** Find your actual paths and update the cron job.

### 2. **PHPMailer Not Installed**

Without PHPMailer, emails can't be sent via SMTP.

**Fix:** Run `composer install`

### 3. **SMTP Not Configured**

Even if PHPMailer is installed, it needs SMTP credentials.

**Fix:** Configure SMTP in root `.htaccess`

### 4. **Cron Service Not Enabled**

Some hosts require you to enable cron jobs in cPanel.

**Fix:** Contact hosting support to enable cron service.

### 5. **PHP Path Wrong**

Cron job might be using wrong PHP binary path.

**Fix:** Run `which php` in Terminal and use that path in cron job.

## Quick Diagnostic Commands

Run these in cPanel Terminal to quickly check:

```bash
# Check current directory
pwd

# Check PHP path
which php

# Check if email queue processor exists
ls -la scripts/process-email-queue.php

# Check if storage/logs directory exists and is writable
ls -lah storage/logs/

# Test email queue processor manually
php scripts/process-email-queue.php

# Check recent log entries
tail -20 storage/logs/email-queue.log
```

## Expected Timeline

Once you fix the issues:

1. **Immediately:** Manual script execution should work
2. **Within 1-2 minutes:** Cron job should start processing emails
3. **Within 5 minutes:** All pending emails should be sent or marked as failed

## Temporary Workaround

If you can't fix the cron job immediately, you can manually process emails:

```bash
cd /home/cp825575/public_html
php scripts/process-email-queue.php
```

Run this every 5-10 minutes until the cron job is fixed.

## Reference Documentation

I've created these comprehensive guides:

1. **CRON_QUICK_FIX.md** - Quick reference for common fixes
2. **CRON_TROUBLESHOOTING.md** - Detailed 200+ line troubleshooting guide
3. **EMAIL_QUEUE_SQL_QUERIES.md** - SQL queries for monitoring
4. **EMAIL_DIAGNOSIS.md** - Technical diagnosis of email system issues
5. **EMAIL_QUICKSTART.md** - Quick setup guide

## Still Stuck?

If you've followed all these steps and it's still not working:

1. Run the diagnostic script and send me the full output
2. Check `storage/logs/error.log` and send any error messages
3. Verify your cron job configuration and send screenshots
4. Contact your hosting support with this info:
   - "Cron job not executing PHP script"
   - Provide the exact cron command
   - Ask them to check cron logs

---

## Summary

**Problem:** Cron job not running, emails stuck in pending status

**Solution:**
1. Run diagnostic script: `php scripts/test-email-system.php`
2. Install PHPMailer: `composer install`
3. Configure SMTP in root `.htaccess`
4. Verify cron job paths are correct
5. Test cron job with test script
6. Monitor logs for errors

**Expected Result:** Emails should be processed within 1-2 minutes of being queued

**Files to Reference:**
- `CRON_QUICK_FIX.md` - Start here
- `CRON_TROUBLESHOOTING.md` - If quick fix doesn't work
- `EMAIL_QUEUE_SQL_QUERIES.md` - For monitoring in phpMyAdmin
