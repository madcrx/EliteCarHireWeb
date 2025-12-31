# Cron Job Troubleshooting - Emails Stuck in Pending

## Problem: Cron Not Running - Emails Stay Pending

If emails remain in "pending" status, the cron job is not executing properly.

## Step-by-Step Troubleshooting

### Step 1: Check Cron Job Configuration

Go to **cPanel → Cron Jobs** and verify your settings:

**Expected Configuration:**

**Email Queue Processor (runs every minute):**
```
Minute: *
Hour: *
Day: *
Month: *
Weekday: *
Command: /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

**Common Mistakes:**
- ❌ Wrong username in path (check if it's `cp825575` or different)
- ❌ Wrong directory (`public_html` vs `httpdocs` vs `www`)
- ❌ Missing full path to PHP binary
- ❌ Script doesn't have execution permissions

### Step 2: Find Your Correct Paths

**A. Find your actual username and path:**

In cPanel File Manager:
1. Look at the full path shown at the top
2. It should show something like: `/home/USERNAME/public_html`
3. Use that exact path in your cron command

**B. Find your PHP binary path:**

Common paths:
- `/usr/bin/php` (most common)
- `/usr/local/bin/php`
- `/opt/cpanel/ea-php81/root/usr/bin/php` (cPanel with specific PHP version)

To find it, create a test cron job that runs once:
```
* * * * * which php >> /home/cp825575/public_html/php-path.txt 2>&1
```

Wait 1 minute, then check `php-path.txt` file in File Manager for the correct path.

### Step 3: Test Script Manually

**Option A: Via cPanel Terminal**

1. Go to cPanel → Terminal
2. Run:
```bash
cd /home/cp825575/public_html
pwd  # Verify you're in the right directory
php scripts/process-email-queue.php
```

**Expected output:**
```
[INFO] No pending emails to process.
```
OR
```
[INFO] Found X pending email(s) to process.
[SUCCESS] Email ID: 1 sent successfully.
```

**If you see errors:**
- Note the exact error message
- Check error log: `cat storage/logs/error.log`

**Option B: Via Test Cron Job**

Create a one-time test cron job:
```
* * * * * /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/cron-test.txt 2>&1
```

Wait 1 minute, then check `cron-test.txt` in File Manager.

### Step 4: Check Log Files

**A. Check cron output log:**
```
/home/cp825575/public_html/storage/logs/email-queue.log
```

**B. Check error log:**
```
/home/cp825575/public_html/storage/logs/error.log
```

**C. Check cPanel cron email:**
- Check your email for cron job output
- cPanel sends emails when cron jobs produce errors

### Step 5: Fix Common Issues

#### Issue: "Permission denied"

**Fix:** Set execute permissions
```bash
chmod +x /home/cp825575/public_html/scripts/process-email-queue.php
```

Or update cron to explicitly use PHP:
```
* * * * * /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

#### Issue: "No such file or directory"

**Fix:** Verify paths are correct

1. Check script exists:
```bash
ls -la /home/cp825575/public_html/scripts/process-email-queue.php
```

2. Check storage/logs directory exists:
```bash
mkdir -p /home/cp825575/public_html/storage/logs
chmod 755 /home/cp825575/public_html/storage/logs
```

#### Issue: "php: command not found"

**Fix:** Use full path to PHP binary

Instead of just `php`, use full path:
```
/usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php
```

Or find your PHP path (see Step 2B above).

#### Issue: Cron runs but script errors

**Check the script output:**

1. Look in `/home/cp825575/public_html/storage/logs/email-queue.log`
2. Look for error messages like:
   - "Database connection failed" → Check database credentials
   - "Call to undefined function" → Bootstrap not loading correctly
   - "PHPMailer not found" → Need to run `composer install`

### Step 6: Verify Cron is Actually Running

**Create a simple test cron:**
```
* * * * * echo "Cron ran at $(date)" >> /home/cp825575/public_html/cron-heartbeat.txt
```

Wait 2-3 minutes, then check `cron-heartbeat.txt`. If it exists and has timestamps, cron IS running.

If the test cron works but the email cron doesn't, the problem is with the script itself, not cron.

### Step 7: Alternative - Run Manually Until Fixed

If you can't get cron working immediately, you can manually process emails:

**Via cPanel Terminal:**
```bash
cd /home/cp825575/public_html
php scripts/process-email-queue.php
```

Run this every few minutes until you fix the cron job.

## Quick Fix - Correct Cron Job Format

Based on the most common setup, use this cron job configuration:

**Minute:** `*`
**Hour:** `*`
**Day:** `*`
**Month:** `*`
**Weekday:** `*`

**Command:**
```bash
/usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
```

**OR if that doesn't work, try:**
```bash
cd /home/cp825575/public_html && /usr/bin/php scripts/process-email-queue.php >> storage/logs/email-queue.log 2>&1
```

## Debugging Checklist

- [ ] Verified correct username in path (replace `cp825575` with your actual username)
- [ ] Verified script file exists at that path
- [ ] Created `storage/logs` directory with correct permissions
- [ ] Tested script manually in Terminal - it runs without errors
- [ ] Verified PHP binary path is correct (`/usr/bin/php`)
- [ ] Checked cron email for error messages
- [ ] Checked log files for errors
- [ ] Confirmed cron service is running (test cron job)

## Still Not Working?

### Check these specific things:

1. **Wrong PHP version:**
   - Some hosts have multiple PHP versions
   - Try: `/opt/cpanel/ea-php81/root/usr/bin/php` (replace 81 with your PHP version)

2. **cPanel paths different:**
   - Your home might be `/home/USERNAME/httpdocs` instead of `/public_html`
   - Check in File Manager

3. **Script has syntax errors:**
   - Run: `php -l scripts/process-email-queue.php`
   - Should say: "No syntax errors detected"

4. **Database connection fails:**
   - Check `config/database.php` has correct credentials
   - Test database connection in the script

## Contact Information

If none of this works:

1. **Check cPanel error logs:**
   - cPanel → Errors → View last 300 error messages

2. **Contact hosting support:**
   - Tell them: "Cron job not executing PHP script"
   - Provide the exact cron command
   - Ask them to check cron logs

3. **Temporary workaround:**
   - Run script manually every few hours
   - Set up external cron service (like cron-job.org)

## After You Fix It

Once cron is working:

1. Submit a test contact form
2. Wait 1-2 minutes
3. Check database - status should change to 'sent' or 'failed'
4. Check `/home/cp825575/public_html/storage/logs/email-queue.log` for output

**Success looks like:**
```
[INFO] Found 1 pending email(s) to process.
[PROCESSING] Email ID: 1 to admin@elitecarhire.au
[SUCCESS] Email ID: 1 sent successfully.
[SUMMARY] Processed: 1 | Success: 1 | Failed: 0
```
