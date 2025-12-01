# Stripe Integration - Complete Deployment Guide

## Overview

This guide provides complete instructions for deploying the Stripe payment integration to your live Elite Car Hire website.

## Files to Upload via FTP

Upload the following files to your live website using FTP:

### 1. Core Application Files

```
SOURCE PATH → DESTINATION PATH

composer.json → /composer.json
app/helpers/stripe_helper.php → /app/helpers/stripe_helper.php
app/controllers/PaymentController.php → /app/controllers/PaymentController.php
app/controllers/AdminStripeController.php → /app/controllers/AdminStripeController.php
app/views/customer/booking-detail.php → /app/views/customer/booking-detail.php
app/views/admin/settings/stripe.php → /app/views/admin/settings/stripe.php
public/webhook/stripe.php → /public/webhook/stripe.php
```

### 2. Database Migration Files

```
database/add_stripe_settings.sql → /database/add_stripe_settings.sql
database/update_payments_for_stripe.sql → /database/update_payments_for_stripe.sql
```

### 3. Credentials File (IMPORTANT - For Reference Only)

```
STRIPE_CREDENTIALS.txt → /STRIPE_CREDENTIALS.txt (Upload temporarily, DELETE after configuration)
```

**⚠️ SECURITY WARNING**: This file contains your Stripe API keys. Upload it temporarily to reference during admin configuration, then DELETE it immediately after use.

### 4. Documentation Files (Optional)

```
STRIPE_ROUTES_TO_ADD.php → /STRIPE_ROUTES_TO_ADD.php
STRIPE_DEPLOYMENT_GUIDE.md → /STRIPE_DEPLOYMENT_GUIDE.md
```

## Step-by-Step Deployment Instructions

### STEP 1: Backup Your Website

**CRITICAL: Create backups before proceeding!**

1. Backup your database via cPanel → phpMyAdmin → Export
2. Backup your files via FTP or cPanel File Manager
3. Save backups to your local computer

### STEP 2: Upload Files via FTP

1. Connect to your website using FTP (FileZilla, WinSCP, or cPanel File Manager)
2. Navigate to your website root directory (usually `public_html` or `htdocs`)
3. Upload all files listed above to their respective directories
4. Ensure file permissions are set correctly (644 for files, 755 for directories)

### STEP 3: Install Stripe PHP SDK

**Via SSH (Recommended):**

```bash
cd /path/to/your/website
composer install
```

**Via cPanel Terminal:**

1. Log into cPanel
2. Go to Terminal
3. Navigate to your website directory:
   ```bash
   cd public_html/ech.cyberlogicit.com.au
   ```
4. Run composer:
   ```bash
   composer install
   ```

**Manual Installation (If composer not available):**

1. Download Stripe PHP library from: https://github.com/stripe/stripe-php/releases
2. Extract to `/vendor/stripe/stripe-php/`
3. Ensure autoload.php exists at `/vendor/autoload.php`

### STEP 4: Run Database Migrations

**Option A: Via cPanel phpMyAdmin (Recommended)**

1. Log into cPanel → phpMyAdmin
2. Select your database (elite_car_hire or similar)
3. Click "SQL" tab
4. Copy contents of `database/add_stripe_settings.sql`
5. Paste and click "Go"
6. Repeat for `database/update_payments_for_stripe.sql`

**Option B: Via MySQL Command Line**

```bash
mysql -u username -p database_name < database/add_stripe_settings.sql
mysql -u username -p database_name < database/update_payments_for_stripe.sql
```

### STEP 5: Add Routes to index.php

1. Open `public/index.php` in your editor
2. Find the Admin routes section
3. Add these routes:

```php
// Stripe Settings (Add to Admin routes section)
$router->get('/admin/settings/stripe', 'AdminStripeController@index');
$router->post('/admin/settings/stripe/update', 'AdminStripeController@update');

// Payment Intent API (Add to API routes section)
$router->post('/api/payment/create-intent', 'PaymentController@createPaymentIntent');
```

4. Save and upload `public/index.php`

### STEP 6: Verify Stripe Settings in Database

Log into phpMyAdmin and verify these settings exist:

```sql
SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'stripe%';
```

You should see:
- stripe_mode = 'test'
- stripe_test_publishable_key = '' (empty - will configure via admin UI)
- stripe_test_secret_key = '' (empty - will configure via admin UI)
- stripe_live_publishable_key = '' (empty initially)
- stripe_live_secret_key = '' (empty initially)
- stripe_webhook_secret = '' (empty - will configure via admin UI)
- payment_gateway = 'stripe'

### STEP 7: Configure Stripe API Keys via Admin Panel

**IMPORTANT: For security, Stripe keys must be configured through the admin interface.**

Your Stripe API credentials are stored in a separate file: `STRIPE_CREDENTIALS.txt`

1. Open `STRIPE_CREDENTIALS.txt` and keep it handy (contains your API keys)
2. Log into your admin panel: `https://elitecarhire.au/admin`
3. Navigate to: **Settings → Stripe Settings** (`/admin/settings/stripe`)
4. Enter your Stripe credentials from STRIPE_CREDENTIALS.txt:
   - **Payment Gateway**: Select "Stripe"
   - **Stripe Mode**: Select "Test Mode"
   - **Test Publishable Key**: Paste `pk_test_51SYys978GKapTv1k...`
   - **Test Secret Key**: Paste `sk_test_51SYys978GKapTv1k...`
   - **Webhook Secret**: Paste `whsec_v2hNe5yr5obXL29...`
5. Click **"Save Settings"**
6. Verify success message appears

**SECURITY NOTE**: After configuration is complete, delete `STRIPE_CREDENTIALS.txt` from your server for security.

### STEP 8: Configure Stripe Webhook

1. Log into Stripe Dashboard: https://dashboard.stripe.com
2. Go to Developers → Webhooks
3. Click "+ Add Endpoint"
4. Enter webhook URL: `https://elitecarhire.au/webhook/stripe.php`
5. Select events to listen to:
   - payment_intent.succeeded
   - payment_intent.payment_failed
   - charge.refunded
6. Click "Add endpoint"
7. Copy the webhook signing secret
8. Go to Admin → Settings → Stripe Settings
9. Paste webhook secret
10. Save settings

### STEP 9: Test Payment Integration

**Test in Test Mode:**

1. Create a test booking as a customer
2. Have owner confirm the booking
3. Go to payment page
4. Use test card: `4242 4242 4242 4242`
5. Use any future expiry date
6. Use any 3-digit CVV
7. Submit payment
8. Verify payment success
9. Check email notifications sent
10. Verify booking marked as paid

**Test Cards:**

| Card Number | Purpose |
|-------------|---------|
| 4242 4242 4242 4242 | Successful payment |
| 4000 0025 0000 3155 | Requires 3D Secure authentication |
| 4000 0000 0000 9995 | Declined payment |

### STEP 10: Go Live Checklist

**Before switching to Live Mode:**

✅ All test payments working correctly
✅ Email notifications sending properly
✅ Webhook configured and tested
✅ Live Stripe keys obtained from Stripe Dashboard
✅ SSL certificate installed (HTTPS required)
✅ Backup completed

**Switching to Live Mode:**

1. Log into Stripe Dashboard: https://dashboard.stripe.com
2. Switch to Live mode (toggle in top left)
3. Go to Developers → API keys
4. Copy Live Publishable Key (pk_live_...)
5. Copy Live Secret Key (sk_live_...)
6. Log into your Admin Panel
7. Go to Settings → Stripe Settings
8. Paste Live keys in appropriate fields
9. Change "Operating Mode" to "Live Mode"
10. Click "Save Settings"
11. **Test with a real small payment**
12. Monitor for 24 hours before full launch

## Troubleshooting

### Issue: "Stripe class not found"

**Solution:**
```bash
composer install
# OR manually download Stripe PHP SDK
```

### Issue: "Invalid API key provided"

**Solution:**
1. Check keys don't have extra spaces
2. Verify using test keys in test mode
3. Verify using live keys in live mode
4. Check keys are valid in Stripe Dashboard

### Issue: "Webhook signature verification failed"

**Solution:**
1. Verify webhook secret matches Stripe Dashboard
2. Check webhook URL is accessible publicly
3. Ensure PHP can read request body
4. Check for mod_security blocking webhooks

### Issue: Payment succeeds but booking not updated

**Solution:**
1. Check webhook is configured correctly
2. View webhook logs in Stripe Dashboard
3. Check PHP error logs
4. Verify database connectivity

### Issue: "Network error" on payment

**Solution:**
1. Verify vendor/autoload.php exists
2. Check Stripe SDK installed correctly
3. Verify server can connect to Stripe API
4. Check firewall rules

## Security Checklist

✅ Live secret keys never exposed in frontend code
✅ HTTPS enabled on entire website
✅ Webhook signature verification enabled
✅ CSRF protection enabled on all payment forms
✅ Database credentials secure
✅ File permissions correct (644 files, 755 directories)
✅ Error logging enabled but errors hidden from users

## File Permissions

Set correct permissions via FTP or SSH:

```bash
# Files
chmod 644 composer.json
chmod 644 app/helpers/stripe_helper.php
chmod 644 app/controllers/PaymentController.php
chmod 644 app/controllers/AdminStripeController.php
chmod 644 app/views/customer/booking-detail.php
chmod 644 app/views/admin/settings/stripe.php
chmod 644 public/webhook/stripe.php

# Directories
chmod 755 public/webhook
chmod 755 app/helpers
chmod 755 app/controllers
chmod 755 app/views/admin/settings
```

## Monitoring

### Check Payment Logs

```sql
SELECT * FROM payments ORDER BY payment_date DESC LIMIT 20;
```

### Check Audit Logs

```sql
SELECT * FROM audit_log WHERE action LIKE '%payment%' ORDER BY created_at DESC LIMIT 20;
```

### View Stripe Dashboard

- Payments: https://dashboard.stripe.com/payments
- Webhooks: https://dashboard.stripe.com/webhooks
- Logs: https://dashboard.stripe.com/logs

## Support Resources

- **Stripe Documentation:** https://stripe.com/docs
- **Stripe Support:** https://support.stripe.com
- **Stripe Status:** https://status.stripe.com
- **Test Cards:** https://stripe.com/docs/testing

## Rollback Plan

If issues occur after deployment:

1. Switch back to Test Mode in Admin Settings
2. Restore database from backup
3. Restore files from backup
4. Investigate logs and fix issues
5. Test thoroughly before re-deploying

## Post-Deployment Verification

Within 24-48 hours of going live:

✅ Monitor first 10-20 live transactions
✅ Verify email notifications sending
✅ Check webhook events processing
✅ Monitor error logs
✅ Verify payouts scheduled correctly
✅ Test refund process
✅ Confirm commission calculations correct

## Currency Configuration

Current settings: **Australian Dollars (AUD)**

To change currency, edit `PaymentController.php` line 48:

```php
'currency' => 'aud', // Change to 'usd', 'gbp', 'eur', etc.
```

## Summary of All Files Created/Modified

### New Files (Upload via FTP):

1. `/composer.json` - Composer dependencies
2. `/app/helpers/stripe_helper.php` - Stripe configuration helper
3. `/app/controllers/AdminStripeController.php` - Admin Stripe settings
4. `/app/views/admin/settings/stripe.php` - Stripe settings UI
5. `/public/webhook/stripe.php` - Webhook handler
6. `/database/add_stripe_settings.sql` - Settings migration
7. `/database/update_payments_for_stripe.sql` - Payments table update

### Modified Files (Upload via FTP):

1. `/app/controllers/PaymentController.php` - Stripe payment processing
2. `/app/views/customer/booking-detail.php` - Stripe Elements integration
3. `/public/index.php` - Routes (manual update required)

### Files for Reference (Do NOT upload to production):

1. `/STRIPE_ROUTES_TO_ADD.php` - Routes reference
2. `/STRIPE_DEPLOYMENT_GUIDE.md` - This documentation

## Quick Deployment Checklist

- [ ] Backup database
- [ ] Backup files
- [ ] Upload all files via FTP
- [ ] Run `composer install`
- [ ] Apply database migrations
- [ ] Add routes to index.php
- [ ] Verify settings in database
- [ ] Configure admin panel
- [ ] Configure Stripe webhook
- [ ] Test with test cards
- [ ] Verify emails sending
- [ ] Check error logs
- [ ] Add live keys when ready
- [ ] Switch to live mode
- [ ] Test with real payment
- [ ] Monitor for 24 hours

## Need Help?

If you encounter issues during deployment:

1. Check PHP error logs
2. Check Stripe Dashboard logs
3. Verify database migrations applied
4. Ensure Composer dependencies installed
5. Verify file permissions correct
6. Check webhook configuration
7. Test with Stripe test cards first

---

**🎉 Congratulations!** Your Stripe integration is now deployed and ready to process payments securely.
