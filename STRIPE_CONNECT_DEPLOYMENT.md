# Stripe Connect Deployment Guide

Complete guide for deploying Stripe Connect automated payouts on Elite Car Hire.

## Overview

This implementation enables:
- **Automatic payment splits** (85% to owner, 15% platform commission)
- **Direct bank deposits** to owners within 2-7 business days
- **Stripe-hosted onboarding** for easy owner verification
- **Real-time account status** updates via webhooks
- **Manual transfer option** for owners without Stripe Connect

---

## Pre-Deployment Checklist

Before starting, ensure you have:
- [x] Access to cPanel/hosting server
- [x] Access to phpMyAdmin or MySQL console
- [x] Stripe Dashboard account (https://dashboard.stripe.com)
- [x] All new files uploaded to production server
- [x] Backup of current database

---

## Step 1: Database Migration

### 1.1 Run the Migration SQL

Execute the migration file to add Stripe Connect fields:

**File:** `database/add_stripe_connect.sql`

**Options to run:**

**Option A: phpMyAdmin**
1. Login to phpMyAdmin
2. Select your database (e.g., `elitecar_db`)
3. Click "SQL" tab
4. Copy and paste contents of `database/add_stripe_connect.sql`
5. Click "Go" to execute

**Option B: MySQL Command Line**
```bash
mysql -u your_username -p your_database < database/add_stripe_connect.sql
```

**Option C: cPanel MySQL Database**
1. Login to cPanel
2. Go to "MySQL Databases"
3. Click "phpMyAdmin"
4. Follow Option A steps

### 1.2 Verify Migration

Run this query to verify all fields were added:

```sql
-- Check users table
DESCRIBE users;
-- Should see: stripe_account_id, stripe_account_status, etc.

-- Check payouts table
DESCRIBE payouts;
-- Should see: stripe_transfer_id, stripe_payout_id, etc.

-- Check settings table
SELECT * FROM settings WHERE setting_key LIKE 'stripe_connect%';
-- Should return 4 rows
```

---

## Step 2: Stripe Dashboard Configuration

### 2.1 Enable Stripe Connect

1. Login to **Stripe Dashboard**: https://dashboard.stripe.com
2. Navigate to **Connect** → **Settings**
3. Click **Get Started** (if not already enabled)
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
4. Save this - you'll need it in Step 3

### 2.3 Configure Connected Account Settings

1. In **Connect** → **Settings** → **Connected accounts**
2. Set **Account type:** Express
3. Set **Country:** Australia
4. Enable **Capabilities:**
   - ✅ Card payments
   - ✅ Transfers
5. Set **Onboarding:**
   - ✅ Collect email
   - ✅ Business type (Individual)
6. Click **Save**

### 2.4 Set Up Webhooks

1. Go to **Developers** → **Webhooks**
2. Click **+ Add endpoint**
3. Enter endpoint URL:
   ```
   https://elitecarhire.au/webhook/stripe-connect.php
   ```
4. Click **Select events** and add:
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
7. Save this - you'll need it in Step 3

---

## Step 3: Configure Application Settings

### 3.1 Update Stripe Connect Settings

Login to **Admin Dashboard** → **Settings** and update:

1. **Enable Stripe Connect:**
   - Navigate to Payment Settings or use phpMyAdmin
   - Update `stripe_connect_enabled` to `1`

2. **Add Connect Client ID:**
   - Use the Client ID from Step 2.2
   - Update `stripe_connect_client_id`

3. **Set Return URLs:**
   ```
   stripe_connect_onboarding_return_url: https://elitecarhire.au/owner/stripe/return
   stripe_connect_onboarding_refresh_url: https://elitecarhire.au/owner/stripe/refresh
   ```

4. **Add Webhook Secret:**
   - Use the Signing Secret from Step 2.4
   - Update `stripe_webhook_secret`

**Using phpMyAdmin:**
```sql
-- Enable Stripe Connect
UPDATE settings SET setting_value = '1' WHERE setting_key = 'stripe_connect_enabled';

-- Add Client ID (replace with your actual ID)
UPDATE settings SET setting_value = 'ca_xxxxx' WHERE setting_key = 'stripe_connect_client_id';

-- Set Return URL
UPDATE settings SET setting_value = 'https://elitecarhire.au/owner/stripe/return'
WHERE setting_key = 'stripe_connect_onboarding_return_url';

-- Set Refresh URL
UPDATE settings SET setting_value = 'https://elitecarhire.au/owner/stripe/refresh'
WHERE setting_key = 'stripe_connect_onboarding_refresh_url';

-- Add Webhook Secret (replace with your actual secret)
UPDATE settings SET setting_value = 'whsec_xxxxx' WHERE setting_key = 'stripe_webhook_secret';
```

### 3.2 Verify Configuration

Create test file: `public/test-stripe-connect-config.php`

```php
<?php
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/helpers/stripe_helper.php';

echo "<pre>";
echo "=== Stripe Connect Configuration Test ===\n\n";

$config = getStripeConnectConfig();
echo "Connect Enabled: " . ($config['enabled'] ? 'YES' : 'NO') . "\n";
echo "Client ID: " . ($config['client_id'] ?: 'NOT SET') . "\n";
echo "Return URL: " . ($config['return_url'] ?: 'NOT SET') . "\n";
echo "Refresh URL: " . ($config['refresh_url'] ?: 'NOT SET') . "\n\n";

echo "Status: " . (isStripeConnectEnabled() ? '✓ READY' : '✗ NOT CONFIGURED') . "\n";
echo "</pre>";
```

Visit: `https://elitecarhire.au/test-stripe-connect-config.php`

Expected output:
```
Connect Enabled: YES
Client ID: ca_xxxxx
Return URL: https://elitecarhire.au/owner/stripe/return
Refresh URL: https://elitecarhire.au/owner/stripe/refresh
Status: ✓ READY
```

**Delete test file after verification!**

---

## Step 4: Test the Integration

### 4.1 Test Owner Onboarding

1. Login as an **Owner** account
2. Go to **Owner Dashboard**
3. You should see the **Payment Settings** card
4. Click **Connect Stripe Account**
5. Complete Stripe onboarding:
   - Enter business details
   - Add bank account
   - Submit verification documents
6. After completion, you should see:
   - Status: "Verification Pending" or "Connected & Verified"
   - Green checkmarks if verified

### 4.2 Test Payment with Automatic Split

**Prerequisites:**
- Owner has connected and verified Stripe account
- Owner has at least one vehicle listed
- Customer has an account

**Steps:**
1. Login as **Customer**
2. Create a booking for a vehicle from the connected owner
3. Wait for owner to confirm booking
4. Process payment with test card: `4242 4242 4242 4242`
   - Expiry: Any future date
   - CVC: Any 3 digits
5. Check results:
   - Payment should succeed
   - Check Admin → Payouts page
   - Payout should be marked as "Completed" immediately
   - `stripe_transfer_id` should be populated

### 4.3 Test Manual Payout (Legacy)

For owners without Stripe Connect:

1. Create booking with owner who hasn't connected Stripe
2. Complete payment
3. Check Admin → Payouts
4. Payout should be "Pending"
5. Click **Process** button
6. Should get message: "Please process the bank transfer manually"
7. Payout marked as "Completed"

### 4.4 Test Webhook

1. In Stripe Dashboard, go to **Developers** → **Webhooks**
2. Click on your webhook endpoint
3. Click **Send test webhook**
4. Select `account.updated`
5. Check webhook logs - should see "200 OK"

---

## Step 5: Monitor and Verify

### 5.1 Check Stripe Dashboard

Monitor these sections:
- **Connect** → **Accounts** - See connected owners
- **Payments** - All customer payments
- **Transfers** - Payouts to owners
- **Events** - Webhook events

### 5.2 Check Application Logs

Monitor for errors:
```bash
tail -f /path/to/error_log
```

Look for:
- "Stripe Connect" messages
- "Transfer" events
- Any webhook failures

### 5.3 Database Verification

```sql
-- Check connected owners
SELECT id, email, stripe_account_id, stripe_account_status
FROM users
WHERE stripe_account_id IS NOT NULL;

-- Check automatic payouts
SELECT p.*, b.booking_reference
FROM payouts p
LEFT JOIN bookings b ON p.booking_id = b.id
WHERE stripe_transfer_id IS NOT NULL;

-- Check webhook logs (if logging enabled)
SELECT * FROM audit_log
WHERE action LIKE '%stripe%'
ORDER BY created_at DESC
LIMIT 20;
```

---

## Step 6: Go Live (Production Mode)

### 6.1 Switch to Live Mode

1. In Stripe Dashboard, toggle to **Live mode** (top right)
2. Get Live mode keys:
   - **Publishable Key** (starts with `pk_live_`)
   - **Secret Key** (starts with `sk_live_`)
   - **Connect Client ID** (starts with `ca_` without test)
3. Update settings in Admin Dashboard or database:
   ```sql
   UPDATE settings SET setting_value = 'live' WHERE setting_key = 'stripe_mode';
   UPDATE settings SET setting_value = 'pk_live_xxxxx' WHERE setting_key = 'stripe_live_publishable_key';
   UPDATE settings SET setting_value = 'sk_live_xxxxx' WHERE setting_key = 'stripe_live_secret_key';
   UPDATE settings SET setting_value = 'ca_xxxxx' WHERE setting_key = 'stripe_connect_client_id';
   ```

### 6.2 Update Live Webhook

1. In **Live mode**, go to **Developers** → **Webhooks**
2. Add endpoint (same URL): `https://elitecarhire.au/webhook/stripe-connect.php`
3. Add same events as test mode
4. Copy new **Signing secret**
5. Update webhook secret in database:
   ```sql
   UPDATE settings SET setting_value = 'whsec_live_xxxxx' WHERE setting_key = 'stripe_webhook_secret';
   ```

### 6.3 Notify Existing Owners

Send email/notification to all existing owners:

**Subject:** Connect Your Bank Account for Automatic Payouts

**Message:**
```
Hi [Owner Name],

We've launched automatic payouts! You can now receive your earnings
directly to your bank account within 2-7 business days.

To get started:
1. Login to your Owner Dashboard
2. Click "Connect Stripe Account" in Payment Settings
3. Complete the quick onboarding process

Benefits:
- Automatic payouts (no more waiting for manual transfers)
- Secure and PCI compliant
- Transparent tracking
- Receive 85% of every booking directly

Connect your account today!

Best regards,
Elite Car Hire Team
```

---

## Common Issues & Troubleshooting

### Issue 1: "Stripe Connect is not enabled"

**Cause:** Settings not configured properly

**Solution:**
```sql
SELECT * FROM settings WHERE setting_key LIKE 'stripe%';
-- Verify all Connect settings are present
-- stripe_connect_enabled should be '1'
-- stripe_connect_client_id should have value
```

### Issue 2: Onboarding link doesn't work

**Cause:** Return/Refresh URLs not properly formatted

**Solution:**
- URLs must include protocol: `https://`
- URLs must be fully qualified domain names
- URLs must be accessible (not localhost)

### Issue 3: Webhook events failing

**Cause:** Signature verification failing

**Solution:**
1. Verify webhook secret is correct
2. Check webhook endpoint is accessible: `curl https://elitecarhire.au/webhook/stripe-connect.php`
3. View error logs for details
4. Test webhook in Stripe Dashboard

### Issue 4: Transfers not appearing in Stripe Dashboard

**Cause:** Insufficient balance or Connect not properly configured

**Solution:**
1. In Stripe Dashboard, check **Balance**
2. Verify platform has sufficient balance for transfers
3. Check **Connect** → **Settings** → ensure capabilities are enabled
4. View transfer in **Connect** → **Transfers**

### Issue 5: Owner account stuck in "Pending"

**Cause:** Owner didn't complete onboarding or requires additional verification

**Solution:**
1. Owner should revisit onboarding link
2. Check **Connect** → **Accounts** in Stripe Dashboard
3. View account details to see what's required
4. Owner may need to provide additional documents

---

## Commission Rate Changes

Current commission: **15% platform, 85% owner**

To change commission rate:

1. Update `app/helpers/stripe_helper.php`:
   ```php
   // Line 307 in createDestinationCharge()
   $commissionRate = 0.15; // Change to your rate (e.g., 0.20 for 20%)
   ```

2. Update UI in `app/views/owner/components/stripe-connect-status.php`:
   ```php
   // Line 156 - Update display text
   <li>You automatically receive <strong>85%</strong> of each booking...</li>
   ```

3. Restart PHP if using OPCache

---

## Security Best Practices

1. **Webhook Secret:** Never commit webhook secret to Git
2. **API Keys:** Store Stripe keys securely (not in public directories)
3. **HTTPS:** Always use HTTPS for webhook endpoints
4. **Signature Verification:** Always verify webhook signatures
5. **Rate Limiting:** Consider rate limiting webhook endpoint
6. **Logging:** Monitor and log all Stripe operations
7. **Testing:** Test thoroughly in test mode before going live

---

## Support Resources

- **Stripe Connect Docs:** https://stripe.com/docs/connect
- **Stripe Dashboard:** https://dashboard.stripe.com
- **Webhook Testing:** https://stripe.com/docs/webhooks/test
- **Express Accounts:** https://stripe.com/docs/connect/express-accounts
- **Transfers:** https://stripe.com/docs/connect/charges-transfers

---

## Rollback Plan

If you need to rollback:

1. **Disable Stripe Connect:**
   ```sql
   UPDATE settings SET setting_value = '0' WHERE setting_key = 'stripe_connect_enabled';
   ```

2. **All payments will continue working** (falls back to manual payouts)

3. **Existing transfers are not affected** (already processed)

4. **Database changes are backward compatible** (new fields are NULL-safe)

---

## Success Metrics

After deployment, monitor:

- **Owner adoption rate:** % of owners with connected accounts
- **Automatic vs manual payouts:** Track ratio
- **Transfer success rate:** Should be >99%
- **Average payout time:** Should be 2-7 days
- **Webhook success rate:** Should be >95%
- **Owner satisfaction:** Survey owners on payout experience

---

## Post-Deployment Checklist

- [ ] Database migration completed successfully
- [ ] Stripe Connect enabled in dashboard
- [ ] Connect Client ID configured
- [ ] Webhook endpoint added and verified
- [ ] Test owner onboarding completed
- [ ] Test payment with automatic split completed
- [ ] Test manual payout completed
- [ ] Webhook events processing correctly
- [ ] Live mode keys configured (when ready)
- [ ] Existing owners notified
- [ ] Error monitoring in place
- [ ] Test file `test-stripe-connect-config.php` deleted
- [ ] Backup of production database created

---

## Questions?

If you encounter issues not covered in this guide:

1. Check error logs first
2. Review Stripe Dashboard → Events for error details
3. Test in Stripe's test mode
4. Consult Stripe documentation
5. Contact support with specific error messages

---

**Deployment Date:** _______________

**Deployed By:** _______________

**Notes:**
_____________________________________________________________________________
_____________________________________________________________________________
_____________________________________________________________________________
