# EMAIL SYSTEM - COMPLETE TASK LIST

## Required Tasks to Get Emails Working

Based on your current setup (cPanel hosting with cron jobs already configured), here are ALL the tasks needed:

---

## ✅ TASK 1: Get cPanel Email SMTP Credentials (5 minutes)

**Steps:**
1. Log into **cPanel**
2. Go to **"Email Accounts"**
3. Find email: `noreply@elitecarhire.au`
   - If it doesn't exist, click **"Create"** and create it
   - Set password and remember it
4. Click **"Connect Devices"** or **"Configure Email Client"** next to the email
5. Note down these settings:
   - **Incoming Server** (you need the hostname)
   - **Outgoing Server (SMTP)**:
     - Server: Usually `mail.elitecarhire.au` or `elitecarhire.au` or `cp825575.hostname.com`
     - Port: Usually `587` (or `465`)
     - Username: `noreply@elitecarhire.au`
     - Password: (the password you set)
     - Encryption: TLS (for port 587) or SSL (for port 465)

**Write these down - you'll need them for Task 2!**

---

## ✅ TASK 2: Configure Root .htaccess File (3 minutes)

The root `.htaccess` file (shown in your screenshot 2) needs email SMTP configuration added.

**Location:** `/home/cp825575/public_html/.htaccess` (the ROOT one, not `public/.htaccess`)

**What to do:**
1. Open the **root** `.htaccess` file for editing (via cPanel File Manager)
2. Scroll to the end (after the Stripe configuration section)
3. Add this section at the bottom:

```apache
# ========================================
# Email Configuration
# ========================================

# SMTP Server Settings (cPanel Email)
SetEnv SMTP_HOST "mail.elitecarhire.au"
SetEnv SMTP_PORT "587"
SetEnv SMTP_USER "noreply@elitecarhire.au"
SetEnv SMTP_PASS "YOUR_EMAIL_PASSWORD_HERE"

# Email Addresses
SetEnv ADMIN_EMAIL "admin@elitecarhire.au"
SetEnv BOOKING_EMAIL "bookings@elitecarhire.au"
SetEnv PAYMENT_EMAIL "payments@elitecarhire.au"
SetEnv SUPPORT_EMAIL "support@elitecarhire.au"
SetEnv CONTACT_EMAIL "inquiries@elitecarhire.au"
SetEnv VEHICLE_EMAIL "vehicles@elitecarhire.au"
```

4. **Replace the values** with your actual SMTP credentials from Task 1:
   - Replace `mail.elitecarhire.au` with your actual SMTP server
   - Replace `587` with your actual SMTP port (if different)
   - Replace `YOUR_EMAIL_PASSWORD_HERE` with the actual email password

5. **Save the file**

**IMPORTANT:** Make sure you edit the **ROOT** `.htaccess` (the one with Stripe keys), NOT the `public/.htaccess` file!

---

## ✅ TASK 3: Install PHPMailer Library (5 minutes)

PHPMailer is required for sending emails via SMTP.

**Option A: Using Composer (Recommended if available)**

1. Log into **cPanel Terminal** (or SSH)
2. Run these commands:
```bash
cd /home/cp825575/public_html
composer install
```

**Option B: Manual Installation (if Composer not available)**

1. Download PHPMailer: https://github.com/PHPMailer/PHPMailer/releases/latest
2. Download the `Source code (zip)` file
3. Extract the ZIP file on your computer
4. Using **cPanel File Manager**, create this directory structure:
   ```
   /home/cp825575/public_html/vendor/phpmailer/phpmailer/src/
   ```
5. Upload these files to that directory:
   - `PHPMailer.php`
   - `SMTP.php`
   - `Exception.php`
   - `POP3.php`
   - `OAuth.php`

**Verify Installation:**
Check that this file exists: `/home/cp825575/public_html/vendor/phpmailer/phpmailer/src/PHPMailer.php`

---

## ✅ TASK 4: Create Log Directories (2 minutes)

Email system needs directories to store log files.

**Using cPanel File Manager:**
1. Navigate to `/home/cp825575/public_html/`
2. Create folder: `storage` (if it doesn't exist)
3. Inside `storage`, create folder: `logs`
4. Set permissions on `storage/logs/` to **755**

**Final structure:**
```
/home/cp825575/public_html/storage/logs/
```

---

## ✅ TASK 5: Fix Cron Jobs (5 minutes)

You currently have 5 cron jobs but only need 2. Clean up duplicates and fix paths.

**What to do:**
1. Go to **cPanel → Cron Jobs**
2. **DELETE these cron jobs** (duplicates/broken):
   - Row 2: The one with `/username/` path (broken)
   - Row 4: Duplicate of row 3
   - Row 5: Duplicate of row 1

3. **EDIT Row 1** (Email Queue Processor):
   - **Schedule:** Every minute
     ```
     Minute: *
     Hour: *
     Day: *
     Month: *
     Weekday: *
     ```
   - **Command:**
     ```
     /usr/bin/php /home/cp825575/public_html/scripts/process-email-queue.php >> /home/cp825575/public_html/storage/logs/email-queue.log 2>&1
     ```

4. **EDIT Row 3** (Email Reminders):
   - **Schedule:** Every hour at minute 0
     ```
     Minute: 0
     Hour: *
     Day: *
     Month: *
     Weekday: *
     ```
   - **Command:**
     ```
     /usr/bin/php /home/cp825575/public_html/cron/send_email_reminders.php >> /home/cp825575/public_html/storage/logs/email-reminders.log 2>&1
     ```

**Final Result:** You should have exactly **2 cron jobs** running.

---

## ✅ TASK 6: Test Email System (5 minutes)

After completing all tasks above, test that emails work.

**Test 1: Manual Queue Processing**

1. Go to **cPanel → Terminal** (or SSH)
2. Run this command:
```bash
php /home/cp825575/public_html/scripts/process-email-queue.php
```

3. **Expected output:**
```
[INFO] No pending emails to process.
```
(This is good - means script runs successfully)

**Test 2: Submit Contact Form**

1. Go to your website: `https://elitecarhire.au/contact`
2. Fill out and submit the contact form
3. Wait 1-2 minutes (for cron job to run)
4. Check if admin received the email at `admin@elitecarhire.au`

**Test 3: Check Email Queue Database**

1. Go to **cPanel → phpMyAdmin**
2. Select your database
3. Run this SQL query:
```sql
SELECT id, to_email, subject, status, sent_at, error_message
FROM email_queue
ORDER BY created_at DESC
LIMIT 10;
```

4. **What to look for:**
   - `status = 'sent'` ✅ Email sent successfully
   - `status = 'pending'` ⏳ Email waiting to be sent (cron will process it)
   - `status = 'failed'` ❌ Email failed (check `error_message` column)

**Test 4: Check Log Files**

1. Go to **cPanel → File Manager**
2. Navigate to: `/home/cp825575/public_html/storage/logs/`
3. Open `email-queue.log` file
4. Look for lines like:
   ```
   [SUCCESS] Email ID: 1 sent successfully.
   ```

---

## ✅ TASK 7: Verify All Email Types Work (10 minutes)

Test that all notification types send correctly:

1. **Contact Form** - Submit contact form → Admin should receive email
2. **Booking Request** - Create a booking → Owner should receive email
3. **Vehicle Submission** - Submit vehicle (as owner) → Admin should receive email
4. **Payment Confirmation** - Complete payment → Customer should receive email

For each test, check:
- Email was queued in database
- Email status changed to 'sent'
- Recipient received the email
- Email not in spam folder

---

## 🔍 TROUBLESHOOTING

If emails don't work after completing all tasks:

**Problem: SMTP Connection Failed**
- Check SMTP host and port are correct in `.htaccess`
- Try port `465` instead of `587` (change SMTP port)
- Contact hosting provider to ensure SMTP is enabled

**Problem: Authentication Failed**
- Verify email password is correct in `.htaccess`
- Check username is full email: `noreply@elitecarhire.au`
- Ensure email account exists in cPanel

**Problem: Emails Stuck as 'pending'**
- Check cron jobs are running: `crontab -l`
- Check cron has correct paths (not `/username/`)
- Run manually to see errors: `php scripts/process-email-queue.php`

**Problem: PHPMailer Errors**
- Verify files exist: `vendor/phpmailer/phpmailer/src/PHPMailer.php`
- Check file permissions are readable (644)
- Re-upload PHPMailer files if needed

**Problem: Emails Go to Spam**
- Add SPF record to domain DNS
- Add DKIM record to domain DNS
- Use proper "From" email address (not Gmail)
- Contact hosting provider for email deliverability help

---

## 📋 COMPLETION CHECKLIST

Check off each task as you complete it:

- [ ] Task 1: Got cPanel email SMTP credentials
- [ ] Task 2: Added SMTP config to ROOT `.htaccess` file
- [ ] Task 3: Installed PHPMailer library
- [ ] Task 4: Created `storage/logs/` directory
- [ ] Task 5: Fixed cron jobs (only 2 remaining)
- [ ] Task 6: Tested email system successfully
- [ ] Task 7: Verified all email types work

Once all tasks are checked, your email system should be fully operational!

---

## 📞 SUPPORT

If you get stuck on any task:

1. Check error messages in `/storage/logs/email-queue.log`
2. Run manual test: `php scripts/process-email-queue.php`
3. Check database: `SELECT * FROM email_queue WHERE status = 'failed'`
4. Review `docs/EMAIL_SETUP.md` for detailed troubleshooting

---

## ESTIMATED TIME

- **Total time to complete all tasks:** 25-35 minutes
- **Most critical tasks:** Tasks 1, 2, 3, and 5
- **Can skip for now:** Task 7 (can verify later)

**Priority Order:**
1. Task 1 (Get SMTP credentials) - CRITICAL
2. Task 2 (Configure .htaccess) - CRITICAL
3. Task 3 (Install PHPMailer) - CRITICAL
4. Task 5 (Fix cron jobs) - CRITICAL
5. Task 4 (Create log dirs) - Important
6. Task 6 (Test) - Important
7. Task 7 (Verify) - Optional for now
