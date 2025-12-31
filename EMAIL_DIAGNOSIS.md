# Email System Diagnosis & Fixes

## Issues Found

After reviewing the live website and email notification code, I identified **CRITICAL BUGS** that prevent emails from working:

### 1. ❌ Bootstrap Script Errors (CRITICAL)

**Problem:**
- `scripts/process-email-queue.php` was loading config incorrectly
- Missing Composer autoloader (needed for PHPMailer)
- Missing helpers.php file (contains config() and db() functions)
- Used deprecated Database::getInstance() instead of db()

**Impact:** Email queue processor crashes immediately on execution

**Fix Applied:**
- Updated `app/bootstrap.php` to include Composer autoloader
- Updated `app/bootstrap.php` to return $config
- Updated `scripts/process-email-queue.php` to use proper bootstrap
- Updated `cron/send_email_reminders.php` to use proper bootstrap

### 2. ⚠️ PHPMailer Not Installed

**Problem:**
- PHPMailer library required for SMTP email sending is not installed
- Script falls back to PHP mail() which often doesn't work or gets blocked as spam

**Impact:** Emails may fail or go to spam

**Solution:** Run `composer install` (see EMAIL_QUICKSTART.md)

### 3. ⚠️ SMTP Not Configured

**Problem:**
- No SMTP credentials configured in `.htaccess` files
- Email config uses defaults from config/app.php which point to localhost

**Impact:** SMTP connection fails, emails can't be sent

**Solution:** Configure SMTP in root `.htaccess` (see EMAIL_TASKS_COMPLETE.md Task 2)

### 4. ✓ Cron Jobs Set Up (GOOD)

**Status:** User already has cron jobs configured correctly
- Email queue processor: Every minute ✓
- Email reminders: Every hour ✓

**Paths:** Need to verify they're pointing to correct directories

## What Was Fixed in This Commit

### Files Modified:

1. **app/bootstrap.php**
   - Added Composer autoloader loading
   - Added return statement for $config
   - Now properly loads all dependencies for CLI scripts

2. **scripts/process-email-queue.php**
   - Fixed bootstrap loading (was broken)
   - Changed from Database::getInstance() to db()
   - Now uses proper application bootstrap

3. **cron/send_email_reminders.php**
   - Fixed bootstrap path
   - Now uses proper application bootstrap

## How Email System Works

```
User Action (booking, contact form, etc.)
    ↓
Application calls sendEmail()
    ↓
Email queued in database (email_queue table)
    ↓
Cron runs every minute → scripts/process-email-queue.php
    ↓
Script loads pending emails
    ↓
Sends via SMTP using PHPMailer (or falls back to PHP mail())
    ↓
Updates database status: 'sent', 'failed', or 'pending'
```

## Testing the Fixes

### Test 1: Run Email Queue Processor Manually

```bash
cd /home/cp825575/public_html
php scripts/process-email-queue.php
```

**Before Fix:** Would crash with "Call to undefined function config()"
**After Fix:** Should output: `[INFO] No pending emails to process.` (or process emails if any exist)

### Test 2: Check For Errors

```bash
tail -f storage/logs/error.log
```

Look for any PHP errors when running the script.

### Test 3: Queue Test Email

Log into the website and submit a contact form, then check:

```sql
SELECT id, to_email, subject, status, error_message, attempts
FROM email_queue
ORDER BY created_at DESC
LIMIT 5;
```

Should see email with `status = 'pending'` initially.

Run processor manually:
```bash
php scripts/process-email-queue.php
```

Then check database again - status should change to 'sent' or 'failed' with error message.

## Remaining Steps to Complete Email Setup

Even with these fixes, emails still won't work until:

### ✅ Step 1: Install PHPMailer (REQUIRED)

```bash
cd /home/cp825575/public_html
composer install
```

Or upload PHPMailer manually (see EMAIL_SETUP.md)

### ✅ Step 2: Configure SMTP (REQUIRED)

Edit: `/home/cp825575/public_html/.htaccess` (ROOT .htaccess, not public/.htaccess)

Add after the Stripe configuration section:

```apache
# ========================================
# Email Configuration
# ========================================

# SMTP Server Settings (cPanel Email)
SetEnv SMTP_HOST "mail.elitecarhire.au"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USER "noreply@elitecarhire.au"
SetEnv SMTP_PASS "your-email-password-here"

# Email Addresses
SetEnv ADMIN_EMAIL "admin@elitecarhire.au"
SetEnv BOOKING_EMAIL "bookings@elitecarhire.au"
```

**Get SMTP credentials:** cPanel → Email Accounts → noreply@elitecarhire.au → Connect Devices

### ✅ Step 3: Verify Cron Jobs

Check that cron job paths match your actual directory:

```
* * * * * /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

## Email Notifications Currently Implemented

The system sends emails for:

✉️ **Booking System:**
- Customer: Booking created confirmation
- Owner: New booking request notification
- Admin: New booking notification
- Customer: Booking confirmed by owner
- All parties: Cancellation notifications

✉️ **Vehicle Management:**
- Admin: New vehicle submission
- Owner: Vehicle approved/rejected

✉️ **Payments:**
- Customer: Payment confirmation
- All parties: Refund notifications

✉️ **Contact Forms:**
- Admin: New inquiry received
- Customer: "We received your message" confirmation

✉️ **Reminders (12 hours later):**
- All of the above if no action taken

## Common Error Messages & Solutions

### "Call to undefined function config()"
**Cause:** Bootstrap not loading helpers.php
**Fix:** Applied in this commit ✓

### "Class 'Database' not found"
**Cause:** Bootstrap not loading Database.php
**Fix:** Applied in this commit ✓

### "PHPMailer Error: SMTP connect() failed"
**Cause:** SMTP not configured or PHPMailer not installed
**Fix:** Complete Step 1 & 2 above

### "Could not authenticate"
**Cause:** Wrong SMTP username/password
**Fix:** Verify credentials in cPanel

### Emails stuck in 'pending' status
**Cause:** Cron job not running or has wrong path
**Fix:** Check crontab -l and verify paths

## Priority Actions

1. **IMMEDIATE:** Deploy these code fixes to live server
2. **HIGH:** Install PHPMailer: `composer install`
3. **HIGH:** Configure SMTP in root .htaccess
4. **MEDIUM:** Test email system
5. **LOW:** Monitor email queue for failures

## Files Changed in This Fix

- `app/bootstrap.php` - Fixed to load Composer and return config
- `scripts/process-email-queue.php` - Fixed bootstrap loading
- `cron/send_email_reminders.php` - Fixed bootstrap loading

## Next Steps After Deploy

1. Upload fixed files to live server
2. Run: `composer install`
3. Configure SMTP in root .htaccess
4. Test: `php scripts/process-email-queue.php`
5. Submit contact form and verify email is sent
6. Monitor logs: `tail -f storage/logs/email-queue.log`

---

## Support

See detailed setup instructions:
- **Quick Setup:** EMAIL_QUICKSTART.md
- **Complete Guide:** docs/EMAIL_SETUP.md
- **Task List:** EMAIL_TASKS_COMPLETE.md
