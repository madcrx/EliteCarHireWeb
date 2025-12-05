# Stripe Connect Deployment Guide - Mandatory Weekly Payouts

**UPDATED:** This guide reflects the mandatory Stripe Connect requirement with weekly Monday batch payouts.

## Overview

This implementation enables:
- **MANDATORY Stripe Connect** - All owners MUST have verified accounts to operate
- **Platform holds funds** - 100% of payment goes to platform initially
- **Weekly Monday payouts** - Batch processing every Monday
- **4-day waiting period** - Minimum 4 days after booking completion
- **85/15 commission split** - Owners receive 85%, platform keeps 15%
- **No manual bank transfers** - All payouts via Stripe transfers only

---

## Critical Requirements

⚠️ **IMPORTANT CHANGES FROM PREVIOUS VERSION:**

1. **NO Manual Fallback** - Owners without Stripe Connect CANNOT operate
2. **NO Instant Splits** - Platform receives 100% of payment upfront
3. **Weekly Schedule** - Payouts only on Mondays (not immediate)
4. **Minimum 4 Days** - Booking must be completed for 4+ days before payout
5. **Booking Restriction** - Owners cannot confirm bookings without verified Stripe account

---

## Pre-Deployment Checklist

Before starting, ensure you have:
- [x] Access to cPanel/hosting server
- [x] Access to phpMyAdmin or MySQL console
- [x] Stripe Dashboard account (https://dashboard.stripe.com)
- [x] All new files uploaded to production server
- [x] Backup of current database
- [x] **Understanding that ALL existing owners must connect Stripe to continue operating**

---

## Step 1: Database Migration

### 1.1 Run the Migration SQL

Execute the migration file to add Stripe Connect fields:

**File:** `database/add_stripe_connect.sql`

**Using phpMyAdmin:**
1. Login to phpMyAdmin
2. Select your database (e.g., `elitecar_db`)
3. Click "SQL" tab
4. Copy and paste contents of `database/add_stripe_connect.sql`
5. Click "Go" to execute

### 1.2 Verify Migration

```sql
-- Check users table
DESCRIBE users;
-- Should see: stripe_account_id, stripe_account_status, etc.

-- Check payouts table
DESCRIBE payouts;
-- Should see: stripe_transfer_id, stripe_payout_id, etc.

-- Verify payout status ENUM includes 'scheduled'
SHOW COLUMNS FROM payouts LIKE 'status';
-- Should show: pending, scheduled, processing, completed, failed
```

---

## Step 2: Stripe Dashboard Configuration

### 2.1 Enable Stripe Connect

1. Login to **Stripe Dashboard**: https://dashboard.stripe.com
2. Navigate to **Connect** → **Settings**
3. Click **Get Started**
4. Select **Platform or Marketplace** as your integration type
5. Complete the platform profile:
   - **Platform Name:** Elite Car Hire
   - **Business Type:** Marketplace
   - **Industry:** Transportation/Rental
   - **Website:** https://elitecarhire.au

### 2.2 Get Your Connect Client ID

1. In Stripe Dashboard, go to **Connect** → **Settings**
2. Scroll to **Integration** section
3. Copy your **Connect Client ID**
   - Test mode: `ca_test_xxxxx`
   - Live mode: `ca_xxxxx`

### 2.3 Configure Connected Account Settings

1. In **Connect** → **Settings** → **Connected accounts**
2. Set **Account type:** Express
3. Set **Country:** Australia
4. Enable **Capabilities:**
   - ✅ Card payments
   - ✅ Transfers
5. Click **Save**

### 2.4 Set Up Webhooks

1. Go to **Developers** → **Webhooks**
2. Click **+ Add endpoint**
3. Enter endpoint URL:
   ```
   https://elitecarhire.au/webhook/stripe-connect.php
   ```
4. Add these events:
   - `account.updated`
   - `account.external_account.created`
   - `account.external_account.updated`
   - `capability.updated`
   - `transfer.created`
   - `transfer.updated`
   - `transfer.failed`
   - `transfer.reversed`
5. Click **Add endpoint**
6. Copy the **Signing secret** (starts with `whsec_`)

---

## Step 3: Configure Application Settings

### 3.1 Update Stripe Connect Settings

**Using phpMyAdmin:**
```sql
-- Enable Stripe Connect (MANDATORY)
UPDATE settings SET setting_value = '1' WHERE setting_key = 'stripe_connect_enabled';

-- Add Client ID (replace with your actual ID)
UPDATE settings SET setting_value = 'ca_test_xxxxx' WHERE setting_key = 'stripe_connect_client_id';

-- Set Return URL
UPDATE settings SET setting_value = 'https://elitecarhire.au/owner/stripe/return'
WHERE setting_key = 'stripe_connect_onboarding_return_url';

-- Set Refresh URL
UPDATE settings SET setting_value = 'https://elitecarhire.au/owner/stripe/refresh'
WHERE setting_key = 'stripe_connect_onboarding_refresh_url';

-- Add Webhook Secret (replace with your actual secret)
INSERT INTO settings (setting_key, setting_value, created_at, updated_at)
VALUES ('stripe_webhook_secret', 'whsec_xxxxx', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = 'whsec_xxxxx', updated_at = NOW();
```

---

## Step 4: Notify All Existing Owners

**CRITICAL:** All existing owners MUST complete Stripe Connect before they can operate.

### 4.1 Send Email to All Owners

**Subject:** URGENT: Connect Your Bank Account - Required for All Bookings

**Message Template:**
```
Hi [Owner Name],

IMPORTANT ACTION REQUIRED

We've upgraded our payment system to provide faster, more secure payouts.
As part of this upgrade, you MUST connect your bank account to continue operating.

WHAT YOU NEED TO DO:
1. Login to your Owner Dashboard
2. Click "Connect Stripe Account Now" (orange button)
3. Complete the quick 5-minute onboarding process
4. Add your bank account details

WHAT HAPPENS IF YOU DON'T:
❌ You will NOT be able to confirm new bookings
❌ Customers will NOT be able to pay for your vehicles
❌ You will NOT receive any payouts

NEW PAYOUT SCHEDULE:
✅ Receive 85% of each booking
✅ Weekly payouts every Monday
✅ Direct bank transfers (2-7 business days after Monday)
✅ Bookings must be completed for at least 4 days before payout

DEADLINE: Please complete this within 7 days to avoid service interruption.

If you have any questions, please contact our support team.

Best regards,
Elite Car Hire Team
```

### 4.2 Create Notification in System

```sql
-- Create notification for all owners
INSERT INTO notifications (user_id, type, title, message, link, created_at)
SELECT id, 'stripe_connect_required', 'ACTION REQUIRED: Connect Bank Account',
       'You must connect your Stripe account to continue confirming bookings and receiving payouts. Click to get started.',
       '/owner/dashboard', NOW()
FROM users
WHERE role = 'owner' AND (stripe_account_id IS NULL OR stripe_account_status != 'verified');
```

---

## Step 5: Testing

### 5.1 Test Owner Onboarding

1. Login as an **Owner** account without Stripe Connect
2. Go to **Owner Dashboard**
3. Should see orange warning: "ACTION REQUIRED: Connect Your Bank Account"
4. Click **Connect Stripe Account Now**
5. Complete Stripe onboarding (test mode uses fake data)
6. Return to dashboard → Should see green "Account Connected & Verified"

### 5.2 Test Booking Confirmation Enforcement

1. Have a customer create a booking
2. Try to confirm as owner **WITHOUT** Stripe Connect:
   - Should redirect to dashboard with error
   - Error: "You must connect and verify your Stripe account..."
3. Complete Stripe Connect
4. Try to confirm booking again → Should succeed

### 5.3 Test Payment with Scheduled Payout

**Prerequisites:**
- Owner has verified Stripe account
- Booking has been confirmed

**Steps:**
1. Login as Customer
2. Process payment for booking (test card: `4242 4242 4242 4242`)
3. Check Admin → Payouts page
4. Payout should show:
   - Status: **"scheduled"** (not "completed")
   - Scheduled Date: **Next Monday** (min 4 days after booking end_date)
5. Check Stripe Dashboard → Balance
   - 100% of payment should be in platform balance
   - NO automatic transfer to owner

### 5.4 Test Monday Batch Processing

**Manual Test (for admins):**
1. Login as Admin
2. Go to Admin → Payouts
3. Click **Process Weekly Payouts** button (if today is Monday)
4. System processes all payouts with `scheduled_date <= today`
5. Each successful payout:
   - Creates Stripe transfer
   - Updates status to "completed"
   - Stores `stripe_transfer_id`
   - Sends notification to owner

**Automated Test (set up cron job):**
```bash
# Add to crontab - runs every Monday at 9 AM
0 9 * * 1 curl -X POST -H "Content-Type: application/json" -d '{"csrf_token":"admin_token"}' https://elitecarhire.au/admin/payouts/batch-process
```

---

## Step 6: Set Up Automated Monday Processing

### 6.1 Create Cron Job Script

Create file: `/home/user/scripts/process-monday-payouts.sh`

```bash
#!/bin/bash
# Process weekly payouts every Monday at 9 AM

# Get CSRF token from admin session
CSRF_TOKEN="your_admin_csrf_token"

# Call batch processing endpoint
curl -X POST \
  -H "Content-Type: application/json" \
  -d "{\"csrf_token\":\"${CSRF_TOKEN}\"}" \
  https://elitecarhire.au/admin/payouts/batch-process

# Log results
echo "$(date): Monday batch payout processing completed" >> /var/log/elite-car-hire-payouts.log
```

### 6.2 Add to Crontab

```bash
# Edit crontab
crontab -e

# Add this line (runs every Monday at 9 AM)
0 9 * * 1 /home/user/scripts/process-monday-payouts.sh
```

**Alternative:** Create admin page with button to trigger batch processing manually.

---

## Step 7: Go Live (Production Mode)

### 7.1 Switch to Live Mode

1. In Stripe Dashboard, toggle to **Live mode**
2. Get Live mode credentials:
   - Publishable Key: `pk_live_xxxxx`
   - Secret Key: `sk_live_xxxxx`
   - Connect Client ID: `ca_xxxxx` (no "test")
3. Update settings:

```sql
UPDATE settings SET setting_value = 'live' WHERE setting_key = 'stripe_mode';
UPDATE settings SET setting_value = 'pk_live_xxxxx' WHERE setting_key = 'stripe_live_publishable_key';
UPDATE settings SET setting_value = 'sk_live_xxxxx' WHERE setting_key = 'stripe_live_secret_key';
UPDATE settings SET setting_value = 'ca_xxxxx' WHERE setting_key = 'stripe_connect_client_id';
```

### 7.2 Update Live Webhook

1. In Live mode → Developers → Webhooks
2. Add endpoint: `https://elitecarhire.au/webhook/stripe-connect.php`
3. Add same events as test mode
4. Copy new signing secret
5. Update:
```sql
UPDATE settings SET setting_value = 'whsec_live_xxxxx' WHERE setting_key = 'stripe_webhook_secret';
```

---

## How The New System Works

### Payment Flow

1. **Customer Books Vehicle**
   - Customer selects vehicle and dates
   - Creates booking (status: "pending")

2. **Owner Confirms Booking**
   - System checks: Does owner have verified Stripe Connect?
   - ✅ Yes → Booking confirmed
   - ❌ No → Rejected with error message

3. **Customer Pays**
   - 100% of payment goes to **platform Stripe account**
   - Payment status: "paid"
   - Payout created with status: **"scheduled"**
   - Scheduled date: Next Monday (minimum 4 days after `end_date`)

4. **Monday Batch Processing**
   - Cron job or admin triggers batch processing
   - System finds all payouts where `status = 'scheduled'` AND `scheduled_date <= today`
   - For each payout:
     - Verifies owner has Stripe Connect
     - Creates Stripe transfer (85% of booking amount)
     - Updates payout status to "completed"
     - Stores `stripe_transfer_id`
     - Sends notification to owner

5. **Owner Receives Funds**
   - Stripe processes transfer to owner's bank
   - Arrives within 2-7 business days
   - Owner sees in bank statement

### Payout Schedule Example

**Example Booking:**
- Booking dates: Dec 1-5, 2025
- End date: Dec 5, 2025
- Payment made: Dec 1, 2025

**Calculation:**
- Earliest payout date: Dec 5 + 4 days = **Dec 9**
- Next Monday from Dec 9:
  - If Dec 9 is Monday → Payout scheduled for Dec 9
  - If Dec 9 is Tuesday → Payout scheduled for Dec 16 (next Monday)
  - If Dec 9 is Saturday → Payout scheduled for Dec 16 (next Monday)

**Processing:**
- System processes on Monday Dec 16 (or whichever Monday >= Dec 9)
- Owner receives in bank by Dec 23 (Dec 16 + up to 7 days)

---

## Commission Rate Changes

Current: **15% platform, 85% owner**

To change:

**No changes needed!** The commission is calculated from `bookings.commission_amount` field, which is set when booking is created based on the commission rate setting.

The payout amount is always: `total_amount - commission_amount`

---

## Monitoring & Maintenance

### Daily Checks

- Check error logs for failed payouts
- Monitor Stripe Dashboard → Transfers
- Review pending Stripe Connect accounts

### Weekly Checks (Monday Morning)

- Verify batch processing ran successfully
- Check for failed transfers
- Review owner complaints about missing payouts

### Monthly Checks

- Reconcile Stripe balance with database
- Audit completed payouts vs transfers
- Review owner adoption rate (% with Stripe Connect)

### Key Queries

```sql
-- Owners without Stripe Connect
SELECT id, email, first_name, last_name, created_at
FROM users
WHERE role = 'owner'
AND (stripe_account_id IS NULL OR stripe_account_status != 'verified')
ORDER BY created_at DESC;

-- Scheduled payouts for next Monday
SELECT p.*, b.booking_reference, u.email as owner_email
FROM payouts p
JOIN users u ON p.owner_id = u.id
LEFT JOIN bookings b ON p.booking_id = b.id
WHERE p.status = 'scheduled'
AND p.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY p.scheduled_date, p.id;

-- Failed payouts
SELECT p.*, u.email, p.failure_message
FROM payouts p
JOIN users u ON p.owner_id = u.id
WHERE p.status = 'failed'
ORDER BY p.updated_at DESC
LIMIT 20;

-- Payouts processed this week
SELECT COUNT(*) as count, SUM(amount) as total_amount
FROM payouts
WHERE status = 'completed'
AND transfer_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);
```

---

## Troubleshooting

### Issue 1: Owner can't confirm bookings

**Symptom:** Error "You must connect and verify your Stripe account..."

**Solutions:**
1. Check owner's Stripe Connect status:
```sql
SELECT stripe_account_id, stripe_account_status, stripe_payouts_enabled
FROM users WHERE id = [owner_id];
```
2. If NULL or not verified → Owner must complete onboarding
3. Check Stripe Dashboard → Connect → Accounts for details
4. Owner may need to resubmit documents

### Issue 2: Payouts not being processed

**Symptom:** Payouts stuck in "scheduled" status

**Solutions:**
1. Check if cron job is running
2. Manually trigger batch processing from Admin dashboard
3. Check error logs for failures
4. Verify owners have verified Stripe accounts

### Issue 3: Transfer failed

**Symptom:** Payout status = "failed", has failure_message

**Solutions:**
1. Check `failure_message` in payouts table
2. Common causes:
   - Insufficient Stripe balance (platform needs funds for transfer)
   - Owner's Stripe account disabled
   - Owner's bank account invalid
3. Check Stripe Dashboard → Transfers for details
4. May need to retry transfer manually

### Issue 4: Wrong payout date calculated

**Symptom:** Payout scheduled for wrong Monday

**Solutions:**
1. Verify booking end_date is correct
2. Test `calculateNextPayoutDate()` function:
```php
echo calculateNextPayoutDate('2025-12-05'); // Should return next Monday >= Dec 9
```
3. Check timezone settings (should be Australia/Sydney)

---

## Rollback Plan

If critical issues arise:

### Emergency Rollback (Disable Stripe Connect Requirement)

**DO NOT DO THIS unless absolutely necessary!**

```sql
-- This will allow bookings without Stripe Connect (emergency only)
-- You'll need to modify OwnerController@confirmBooking to remove the check
```

**Better Option:** Fix the issue rather than rollback, as manual payouts are no longer supported in this version.

---

## Post-Deployment Checklist

- [ ] Database migration completed
- [ ] Stripe Connect enabled in dashboard
- [ ] Connect Client ID configured
- [ ] Webhook endpoint added and verified
- [ ] All existing owners notified via email
- [ ] System notifications created for owners
- [ ] Test owner onboarding completed
- [ ] Test booking confirmation enforcement works
- [ ] Test payment creates scheduled payout
- [ ] Test batch payout processing works
- [ ] Cron job configured for Monday processing
- [ ] Live mode keys configured (when ready)
- [ ] Monitoring dashboard set up
- [ ] Error alerting configured

---

## Files Modified/Created

**Modified:**
- `app/controllers/PaymentController.php` - Scheduled payouts instead of instant splits
- `app/controllers/AdminController.php` - Batch payout processing
- `app/controllers/OwnerController.php` - Stripe Connect requirement enforcement
- `app/helpers.php` - Added `calculateNextPayoutDate()` function
- `app/views/owner/components/stripe-connect-status.php` - Updated warnings and schedules
- `public/index.php` - Added batch processing route

**Created:**
- `STRIPE_CONNECT_DEPLOYMENT_MANDATORY.md` - This file

**From Previous Implementation:**
- `database/add_stripe_connect.sql`
- `app/helpers/stripe_helper.php`
- `public/webhook/stripe-connect.php`

---

## Support

For issues or questions:
1. Check error logs first
2. Review Stripe Dashboard events
3. Consult this guide
4. Check Stripe documentation: https://stripe.com/docs/connect

---

**Deployment Date:** _______________

**Deployed By:** _______________

**All Owners Notified:** [ ] Yes [ ] No

**Cron Job Configured:** [ ] Yes [ ] No
