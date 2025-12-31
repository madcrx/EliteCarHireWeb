# Quick Fix Guide - Cron Job Not Running

## Problem

Emails are stuck in "pending" status because the cron job is not running or failing.

## Quick Diagnostic Steps

### Step 1: Run the Diagnostic Script

Open cPanel Terminal and run:

```bash
cd /home/cp825575/public_html
php scripts/test-email-system.php
```

This will show you exactly what's working and what's not.

### Step 2: Check for Pending Emails (via phpMyAdmin)

Go to phpMyAdmin and run this SQL query:

```sql
SELECT id, to_email, subject, status, attempts, error_message, created_at, last_attempt
FROM email_queue
WHERE status = 'pending'
ORDER BY created_at DESC;
```

If you see emails stuck in "pending" status, the cron job is not processing them.

### Step 3: Test the Email Queue Processor Manually

In cPanel Terminal, run:

```bash
cd /home/cp825575/public_html
php scripts/process-email-queue.php
```

**Expected output if working:**
```
[INFO] Found X pending email(s) to process.
[PROCESSING] Email ID: 1 to admin@elitecarhire.au
[SUCCESS] Email ID: 1 sent successfully.
[SUMMARY] Processed: 1 | Success: 1 | Failed: 0
```

**If you see errors:**
- Note the exact error message
- Check the solutions below

## Common Errors & Solutions

### Error: "Call to undefined function config()"

**Cause:** Bootstrap file not loading correctly

**Fix:** Already fixed in latest code - upload the latest `app/bootstrap.php` file to your server

### Error: "PHPMailer Error: SMTP connect() failed"

**Cause:** SMTP not configured or PHPMailer not installed

**Fix:**

1. Install PHPMailer:
```bash
cd /home/cp825575/public_html
composer install
```

2. Configure SMTP in root `.htaccess` (NOT public/.htaccess):

```apache
# Add these lines to /home/cp825575/public_html/.htaccess

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

Get your SMTP password from: cPanel → Email Accounts → noreply@elitecarhire.au → Connect Devices

### Error: "No such file or directory"

**Cause:** Cron job has wrong path

**Fix:** Verify your cron job paths match your actual directory structure

**To find your correct paths:**

1. In cPanel File Manager, note the full path shown at the top
2. In cPanel Terminal, run:
```bash
pwd
which php
```

3. Update your cron job with the correct paths

**Correct cron job format:**
```
* * * * * /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

Replace:
- `/usr/bin/php` with the output of `which php`
- `/home/cp825575/public_html` with your actual directory path

### Error: "Permission denied"

**Cause:** Script doesn't have execute permissions or log directory not writable

**Fix:**

```bash
cd /home/cp825575/public_html
chmod +x scripts/process-email-queue.php
chmod 755 storage/logs
```

## Testing the Cron Job

### Test 1: Verify Cron Service Works

Add this temporary test cron job in cPanel → Cron Jobs:

**Minute:** `*`
**Hour:** `*`
**Day:** `*`
**Month:** `*`
**Weekday:** `*`
**Command:**
```bash
/usr/bin/php /home/cp825575/public_html/scripts/test-cron.php >> /home/cp825575/public_html/storage/logs/cron-test.log 2>&1
```

Wait 2-3 minutes, then check if `storage/logs/cron-test.log` was created.

- **If file exists:** Cron is working! Problem is with the email script itself.
- **If no file:** Cron service not running or paths are wrong.

### Test 2: Check Actual Email Queue Processor

Your production cron job should be:

**Minute:** `*`
**Hour:** `*`
**Day:** `*`
**Month:** `*`
**Weekday:** `*`
**Command:**
```bash
/usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

After saving, wait 2-3 minutes and check `storage/logs/email-queue.log`

## Checking Log Files

### Via cPanel File Manager:

1. Go to File Manager
2. Navigate to `storage/logs/`
3. Check these files:
   - `email-queue.log` - Cron job output
   - `error.log` - PHP errors
   - `cron-test.log` - Test cron output

### Via cPanel Terminal:

```bash
cd /home/cp825575/public_html

# Check email queue log
tail -50 storage/logs/email-queue.log

# Check error log
tail -50 storage/logs/error.log

# Check if logs are being written
ls -lah storage/logs/
```

## Complete Fix Checklist

Work through this checklist in order:

- [ ] Upload latest code files to server (especially `app/bootstrap.php`)
- [ ] Install PHPMailer: `composer install`
- [ ] Configure SMTP in root `.htaccess`
- [ ] Verify cron job paths are correct
- [ ] Test script manually: `php scripts/process-email-queue.php`
- [ ] Add test cron job and verify it runs
- [ ] Check `storage/logs/email-queue.log` for output
- [ ] Submit test contact form
- [ ] Wait 1-2 minutes
- [ ] Check database - status should change from "pending" to "sent"

## Manual Processing (Temporary Workaround)

If you can't fix the cron job immediately, manually process emails:

```bash
cd /home/cp825575/public_html
php scripts/process-email-queue.php
```

Run this command every 5-10 minutes until the cron job is fixed.

## Still Not Working?

1. **Run the diagnostic script:**
   ```bash
   php scripts/test-email-system.php
   ```

2. **Check detailed logs:**
   ```bash
   tail -100 storage/logs/error.log
   ```

3. **Verify SMTP credentials:**
   - Go to cPanel → Email Accounts
   - Click "Connect Devices" next to noreply@elitecarhire.au
   - Use those exact settings in `.htaccess`

4. **Contact hosting support:**
   - Tell them: "Cron job not executing PHP script"
   - Provide the exact cron command
   - Ask them to check cron logs

## Reference

- **Full troubleshooting guide:** CRON_TROUBLESHOOTING.md
- **Email setup guide:** EMAIL_QUICKSTART.md
- **Technical diagnosis:** EMAIL_DIAGNOSIS.md
