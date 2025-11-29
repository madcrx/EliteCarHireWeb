# Email Setup Guide - Elite Car Hire

## Problem: Emails Not Sending

The system was **queuing** emails but not actually **sending** them. This has been fixed!

## What Was Changed

### 1. **Created Immediate Email Sender**
- File: `app/helpers/email_sender.php`
- Function: `sendEmailImmediately()` - Sends emails right away using PHP mail()
- Function: `sendEmailEnhanced()` - Sends immediately AND queues for backup

### 2. **Created Email Queue Processor**
- File: `public/process-email-queue.php`
- Processes queued emails (for backup/retry logic)
- Can be run manually or via cron job

### 3. **Updated Booking Confirmation**
- `OwnerController.php` now uses `sendEmailEnhanced()` instead of `sendEmail()`
- Emails are sent **immediately** when owner adds additional charges
- Also queued as backup

---

## How to Test Email Sending

### **Option 1: Test Script (Recommended)**

1. Go to: `http://yoursite.com/test-email.php`
2. Enter your email address
3. Click "Send Test Email"
4. Check your inbox (and spam folder!)
5. **Delete test-email.php after testing!**

### **Option 2: Test Real Booking**

1. Apply the database migration (if not done)
2. As owner: Confirm a booking with additional charges
3. Customer should receive email immediately
4. Check the customer's email inbox

---

## Email Server Requirements

### **Check Your Server**

Your server needs ONE of these:

#### **Option A: PHP mail() Function (Simplest)**
- Most shared hosting has this enabled
- Works automatically with `sendEmailImmediately()`
- No additional configuration needed

#### **Option B: SMTP Configuration (Better)**
- More reliable for deliverability
- Requires SMTP credentials
- Configure in `config/app.php`

### **How to Check if mail() Works**

Run the test script at `http://yoursite.com/test-email.php`

**If it says "mail() function is NOT available":**
- Contact your hosting provider
- OR set up SMTP (see SMTP Setup below)

---

## SMTP Setup (If mail() Doesn't Work)

If PHP mail() is disabled on your server, you'll need SMTP:

### **1. Get SMTP Credentials**

Common options:
- **Gmail:** Use App Password (not regular password)
- **SendGrid:** Free tier available
- **Mailgun:** Free tier available
- **Your hosting provider:** Often provides SMTP

### **2. Update config/app.php**

```php
'email' => [
    'from_address' => 'noreply@elitecarhire.au',
    'from_name' => 'Elite Car Hire',
    'smtp_host' => 'smtp.gmail.com',  // Your SMTP host
    'smtp_port' => 587,                // Usually 587 for TLS
    'smtp_username' => 'your@email.com',
    'smtp_password' => 'your-app-password',
    'smtp_encryption' => 'tls',
],
```

### **3. Install PHPMailer (Optional but Recommended)**

```bash
composer require phpmailer/phpmailer
```

Then update `email_sender.php` to use PHPMailer instead of mail().

---

## Email Queue Processor Setup

The queue processor handles retry logic and backup delivery.

### **Manual Processing**

Run this command to send queued emails:

```bash
php /path/to/public/process-email-queue.php
```

### **Automatic Processing with Cron**

#### **Via cPanel:**

1. Log into cPanel
2. Click "Cron Jobs"
3. Add new cron job:
   - **Minute:** `*/5` (every 5 minutes)
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:** `/usr/bin/php /home/username/public_html/public/process-email-queue.php`

#### **Via Command Line:**

```bash
crontab -e
```

Add:
```
*/5 * * * * /usr/bin/php /path/to/public/process-email-queue.php
```

---

## How It Works Now

### **When Owner Adds Additional Charges:**

1. **Owner confirms booking** with extra charges
2. **System sends email IMMEDIATELY** via `sendEmailEnhanced()`
3. **Email also queued** as backup (marked as 'sent')
4. **Customer receives email** right away
5. **If immediate send fails:** Email stays in queue for retry

### **Email Queue Processor (Backup):**

- Runs every 5 minutes (if cron is set up)
- Processes failed emails
- Retries up to 3 times
- Marks as 'failed' after 3 attempts

---

## Troubleshooting

### **Emails Not Being Received**

1. **Check spam folder** - New sending domains often land in spam
2. **Run test script** - Verify mail() works
3. **Check email queue** - See if emails are stuck
4. **Check server logs** - Look for mail errors

### **Check Email Queue Status**

```php
// Check via test-email.php script
// Or run this SQL:
SELECT status, COUNT(*) as count
FROM email_queue
GROUP BY status;
```

### **Common Issues**

| Problem | Solution |
|---------|----------|
| mail() not available | Set up SMTP or contact hosting provider |
| Emails in spam | Add SPF/DKIM records to your domain |
| Emails stuck in queue | Run process-email-queue.php manually |
| Permission denied | Check file permissions on email script |

---

## Email Templates

Both scenarios now send professional HTML emails:

### **With Additional Charges:**
- Subject: "Booking Update - Approval Required"
- Contains: Price breakdown, reason, approval link
- Styled: Orange warning theme

### **Without Additional Charges:**
- Subject: "Booking Confirmed - Payment Required"
- Contains: Booking details, payment link
- Styled: Green success theme

---

## Security Notes

1. **Delete test files after testing:**
   - `test-email.php`
   - `check-migration.php`

2. **Protect sensitive files:**
   - Email credentials in `config/app.php`
   - Database credentials

3. **Monitor email queue:**
   - Don't let failed emails pile up
   - Review failed emails for issues

---

## Verification Checklist

- [ ] Database migration applied
- [ ] Test email sent successfully
- [ ] Customer receives booking update emails
- [ ] Emails not going to spam
- [ ] Cron job set up (optional)
- [ ] Test files deleted
- [ ] Email queue monitored

---

## Quick Commands

```bash
# Test email sending
php public/test-email.php

# Process email queue manually
php public/process-email-queue.php

# Check queue status
mysql -u user -p -e "SELECT status, COUNT(*) FROM elite_car_hire.email_queue GROUP BY status"

# View failed emails
mysql -u user -p -e "SELECT * FROM elite_car_hire.email_queue WHERE status='failed'"
```

---

**Need Help?**

If emails still aren't working after following this guide:
1. Contact your hosting provider about PHP mail()
2. Consider using a transactional email service (SendGrid, Mailgun, etc.)
3. Check server error logs for specific issues
