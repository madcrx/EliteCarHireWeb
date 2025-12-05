# Files to Upload - Stripe Connect Mandatory Weekly Payouts

## Overview
This list contains all files that need to be uploaded to your production server for the Stripe Connect mandatory weekly payout system.

---

## NEW Configuration: Mandatory Stripe Connect + Weekly Monday Payouts

**What Changed:**
- ❌ NO manual bank transfers
- ❌ NO instant payment splits
- ✅ ALL owners MUST have Stripe Connect
- ✅ Platform holds 100% of funds initially
- ✅ Weekly batch payouts every Monday
- ✅ Minimum 4 days after booking completion

---

## Modified Files (MUST Upload)

### 1. Controllers

**app/controllers/PaymentController.php**
- **Changes:** Removed destination charges (automatic splits)
- **Changes:** Now creates "scheduled" payouts instead of "completed"
- **Changes:** Enforces Stripe Connect verification before processing payment
- **Changes:** Uses `calculateNextPayoutDate()` for Monday scheduling

**app/controllers/AdminController.php**
- **Changes:** Updated `processPayout()` to only process "scheduled" payouts
- **Changes:** Enforces Stripe Connect requirement (no manual fallback)
- **NEW:** Added `processBatchPayouts()` method for weekly Monday batch processing

**app/controllers/OwnerController.php**
- **Changes:** Added Stripe Connect verification to `confirmBooking()` method
- **Changes:** Owners without verified Stripe account CANNOT confirm bookings

### 2. Helpers

**app/helpers.php**
- **NEW:** Added `calculateNextPayoutDate($bookingEndDate)` function
- Calculates next Monday payout date with 4-day minimum waiting period

### 3. Views

**app/views/owner/components/stripe-connect-status.php**
- **Changes:** Updated "Not Connected" warning to show it's MANDATORY
- **Changes:** Lists consequences of not connecting (can't confirm bookings, etc.)
- **Changes:** Updated verified section to show weekly Monday schedule
- **Changes:** Updated "How It Works" section with new payout timeline

### 4. Routes

**public/index.php**
- **NEW:** Added route for batch payout processing:
  - `POST /admin/payouts/batch-process` → `AdminController@processBatchPayouts`

---

## New Files (Previously Created - Verify Uploaded)

These files were created in the previous session. Ensure they exist on server:

**database/add_stripe_connect.sql**
- Database migration for Stripe Connect fields
- **MUST BE RUN via phpMyAdmin before system will work**

**app/helpers/stripe_helper.php**
- Contains all Stripe Connect helper functions
- Includes `createManualTransfer()` for weekly batch payouts

**app/controllers/OwnerController.php**
- Contains Stripe Connect onboarding methods:
  - `stripeConnect()`, `stripeReturn()`, `stripeRefresh()`, `stripeSettings()`

**app/views/owner/components/stripe-connect-status.php**
- Stripe Connect status card component for owner dashboard

**public/webhook/stripe-connect.php**
- Webhook handler for Stripe Connect events

---

## Documentation Files (Upload for Reference)

**STRIPE_CONNECT_DEPLOYMENT_MANDATORY.md**
- **NEW:** Comprehensive deployment guide for mandatory weekly payout system
- Contains step-by-step setup instructions
- Includes cron job setup for Monday batch processing
- Owner notification templates

**STRIPE_CONNECT_DEPLOYMENT.md**
- **OUTDATED:** Previous guide (automatic splits version)
- Keep for reference but use MANDATORY version instead

**STRIPE_CONNECT_FILES.md**
- Original file summary
- Keep for reference

**UPLOAD_FILES_LIST.md**
- This file - checklist for uploads

---

## Upload Checklist

### Step 1: Upload Modified Files

Upload these files via FTP/cPanel File Manager:

- [ ] `app/controllers/PaymentController.php`
- [ ] `app/controllers/AdminController.php`
- [ ] `app/controllers/OwnerController.php`
- [ ] `app/helpers.php`
- [ ] `app/views/owner/components/stripe-connect-status.php`
- [ ] `public/index.php`

### Step 2: Verify Previously Uploaded Files

Check these files exist (from previous session):

- [ ] `database/add_stripe_connect.sql`
- [ ] `app/helpers/stripe_helper.php`
- [ ] `app/views/owner/dashboard.php`
- [ ] `public/webhook/stripe-connect.php`

### Step 3: Upload Documentation

- [ ] `STRIPE_CONNECT_DEPLOYMENT_MANDATORY.md`
- [ ] `UPLOAD_FILES_LIST.md`

### Step 4: Database Migration

- [ ] Run `database/add_stripe_connect.sql` via phpMyAdmin
- [ ] Verify all fields were added (check with DESCRIBE queries)

### Step 5: Configure Settings

Update settings via phpMyAdmin SQL:

```sql
-- Enable Stripe Connect
UPDATE settings SET setting_value = '1' WHERE setting_key = 'stripe_connect_enabled';

-- Add your Connect Client ID
UPDATE settings SET setting_value = 'ca_test_xxxxx' WHERE setting_key = 'stripe_connect_client_id';

-- Set return URLs
UPDATE settings SET setting_value = 'https://elitecarhire.au/owner/stripe/return'
WHERE setting_key = 'stripe_connect_onboarding_return_url';

UPDATE settings SET setting_value = 'https://elitecarhire.au/owner/stripe/refresh'
WHERE setting_key = 'stripe_connect_onboarding_refresh_url';

-- Add webhook secret
INSERT INTO settings (setting_key, setting_value, created_at, updated_at)
VALUES ('stripe_webhook_secret', 'whsec_xxxxx', NOW(), NOW())
ON DUPLICATE KEY UPDATE setting_value = 'whsec_xxxxx';
```

### Step 6: Notify Owners

Send email to ALL existing owners requiring them to connect Stripe:

**Email Template:**
```
Subject: URGENT: Connect Your Bank Account - Required for All Bookings

Hi [Owner Name],

IMPORTANT ACTION REQUIRED

You MUST connect your bank account to continue confirming bookings.

WHAT TO DO:
1. Login to your Owner Dashboard
2. Click "Connect Stripe Account Now"
3. Complete the 5-minute setup

DEADLINE: Within 7 days

NEW PAYOUT SCHEDULE:
✅ 85% of each booking
✅ Weekly payouts every Monday
✅ Direct bank transfers

Questions? Contact support.

Best regards,
Elite Car Hire Team
```

### Step 7: Set Up Cron Job

**For Weekly Monday Batch Processing:**

Option A - Create script:
```bash
# /home/user/scripts/monday-payouts.sh
curl -X POST -H "Content-Type: application/json" \
  -d '{"csrf_token":"YOUR_ADMIN_TOKEN"}' \
  https://elitecarhire.au/admin/payouts/batch-process
```

Option B - Add to crontab:
```
0 9 * * 1 /home/user/scripts/monday-payouts.sh
```

**OR** manually process payouts from Admin dashboard every Monday

---

## File Paths Reference

All file paths are relative to project root `/home/user/EliteCarHireWeb/`

**Controllers:**
- `app/controllers/PaymentController.php`
- `app/controllers/AdminController.php`
- `app/controllers/OwnerController.php`

**Helpers:**
- `app/helpers.php`
- `app/helpers/stripe_helper.php`

**Views:**
- `app/views/owner/dashboard.php`
- `app/views/owner/components/stripe-connect-status.php`

**Public:**
- `public/index.php`
- `public/webhook/stripe-connect.php`

**Database:**
- `database/add_stripe_connect.sql`

**Documentation:**
- `STRIPE_CONNECT_DEPLOYMENT_MANDATORY.md`
- `UPLOAD_FILES_LIST.md`

---

## Key Differences from Previous Version

### BEFORE (Automatic Splits):
❌ Destination charges with instant 85/15 split
❌ Owner receives money immediately (2-7 days)
❌ Manual fallback for owners without Stripe
❌ "completed" payout status right away

### AFTER (Mandatory Weekly):
✅ Platform receives 100% of payment
✅ Owner receives money weekly on Mondays
✅ NO manual fallback - Stripe Connect required
✅ "scheduled" payout status → batch processed Monday
✅ 4-day minimum waiting period

---

## Testing Before Go-Live

1. **Test Owner Onboarding:**
   - Create test owner account
   - Complete Stripe Connect onboarding
   - Verify status shows "Connected & Verified"

2. **Test Booking Enforcement:**
   - Try to confirm booking WITHOUT Stripe Connect → Should fail
   - Complete Stripe Connect
   - Try to confirm booking WITH Stripe Connect → Should succeed

3. **Test Payment Scheduling:**
   - Process payment for booking
   - Check payout status = "scheduled"
   - Verify scheduled_date is correct Monday

4. **Test Batch Processing:**
   - Manually trigger: `POST /admin/payouts/batch-process`
   - Verify payouts update to "completed"
   - Check Stripe Dashboard for transfers

---

## Common Issues

### Issue: "Stripe Connect is not enabled"
**Fix:** Run settings SQL to set `stripe_connect_enabled = '1'`

### Issue: Owner can't confirm bookings
**Fix:** Owner needs to complete Stripe Connect onboarding

### Issue: Payouts not processing on Monday
**Fix:** Check cron job is configured and running

### Issue: Wrong payout date
**Fix:** Verify `calculateNextPayoutDate()` function logic and booking end_date

---

## Support

Follow **STRIPE_CONNECT_DEPLOYMENT_MANDATORY.md** for complete step-by-step instructions.

For technical support, check:
1. Error logs (`/path/to/error_log`)
2. Stripe Dashboard → Events
3. Database queries in deployment guide

---

**Last Updated:** 2025-12-05

**Version:** 2.0 (Mandatory Weekly Payouts)
