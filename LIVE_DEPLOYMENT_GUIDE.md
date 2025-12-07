# Elite Car Hire - Live Deployment Guide
## Stripe Connect Owner Onboarding Configuration

**Date:** December 7, 2025
**Environment:** Production/Live
**Stripe Mode:** Live

---

## 🔍 Current Situation

You have Stripe Connect set up for **owner onboarding** (vehicle owners connecting their bank accounts to receive payouts). The implementation is ready but needs proper configuration on your live server.

### What Works
✅ **Stripe Connect Controller** - Uses cURL (no SDK needed)
✅ **Owner Dashboard** - Shows onboarding prompts
✅ **Database Tables** - All migrations completed
✅ **OAuth Flow** - Return/refresh URLs configured

### What Needs Configuration
⚠️ **Database Settings** - Need to be updated with live keys
⚠️ **Environment Variables** - Need to be set in .htaccess
⚠️ **Stripe PHP Library** - Required for customer payments (optional for owner onboarding)

---

## 📋 Deployment Steps

### **STEP 1: Configure .htaccess File**

1. **Get your Stripe API keys:**
   - Go to: https://dashboard.stripe.com (ensure you're in **Live mode**)
   - Navigate to: **Developers → API Keys**
   - Copy your **Secret Key** (starts with `sk_live_`)
   - Copy your **Publishable Key** (starts with `pk_live_`)
   - Navigate to: **Settings → Connect**
   - Copy your **Client ID** (starts with `ca_`)

2. **Edit the .htaccess file:**
   - Copy the file `public/.htaccess.example` to `public/.htaccess` (or edit existing)
   - Replace `YOUR_STRIPE_SECRET_KEY_HERE` with your actual Secret Key
   - Replace `YOUR_STRIPE_PUBLISHABLE_KEY_HERE` with your actual Publishable Key
   - Replace `YOUR_STRIPE_CONNECT_CLIENT_ID_HERE` with your actual Client ID
   - Replace `YOUR_STRIPE_WEBHOOK_SECRET_HERE` with your actual Webhook Secret (get this in Step 3)
   - Save the file

3. **Upload to your live server:**
   - Upload the edited `public/.htaccess` file to your server
   - **IMPORTANT:** Ensure file permissions are set correctly (typically 644)

### **STEP 1B: Upload Other Updated Files**

Upload these files to your live web hosting:

#### Required Files (Must Upload):
```
1. app/views/admin/sidebar.php
   └─ Simplified admin navigation (from previous update)

2. app/controllers/StripeConnectController.php
   └─ Handles owner Stripe Connect onboarding

3. app/controllers/OwnerController.php
   └─ Updated dashboard with Stripe Connect checks

4. app/views/owner/dashboard.php
   └─ Shows Stripe Connect warnings and prompts
```

---

### **STEP 2: Configure Database Settings**

1. **Get your Stripe keys from Stripe Dashboard:**
   - Go to: https://dashboard.stripe.com (ensure you're in **Live mode**)
   - Navigate to: **Settings → Connect**
   - Copy your **Client ID** (starts with `ca_`)
   - Navigate to: **Developers → Webhooks**
   - Copy your **Webhook Secret** (starts with `whsec_`)

2. **Edit the SQL file:**
   - Open `database/migrations/LIVE_STRIPE_SETTINGS.sql` in a text editor
   - Replace `YOUR_STRIPE_CONNECT_CLIENT_ID` with your actual Client ID
   - Replace `YOUR_STRIPE_WEBHOOK_SECRET` with your actual Webhook Secret
   - Save the file

3. **Log into phpMyAdmin** on your hosting

4. **Select your database** (the Elite Car Hire database)

5. **Click the SQL tab**

6. **Copy and paste** the edited contents of `LIVE_STRIPE_SETTINGS.sql`

7. **Click Go** to execute

**Verify settings:**
```sql
SELECT setting_key, setting_value
FROM settings
WHERE setting_key LIKE 'stripe%';
```

You should see:
- `stripe_connect_enabled` = 1
- `stripe_connect_client_id` = ca_XXXXXX (your Client ID)
- `stripe_webhook_secret` = whsec_XXXXXX (your Webhook Secret)
- `stripe_connect_onboarding_return_url` = https://elitecarhire.au/owner/stripe/return
- `stripe_connect_onboarding_refresh_url` = https://elitecarhire.au/owner/stripe/refresh

---

### **STEP 3: Configure Stripe Webhook (Live Mode)**

Webhooks allow Stripe to notify your site about payment events.

1. **Go to Stripe Dashboard (Live Mode):**
   https://dashboard.stripe.com/webhooks

2. **Click "Add endpoint"**

3. **Enter Endpoint URL:**
   ```
   https://elitecarhire.au/webhooks/stripe
   ```

4. **Select Events to Listen To:**
   - `account.updated`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
   - `charge.dispute.created`

5. **Click "Add endpoint"**

6. **Get Webhook Signing Secret:**
   - Click on the newly created endpoint
   - Click "Reveal" next to "Signing secret"
   - Verify it matches: `whsec_KNR3jgmOUN0JyOiyzhQCL0M9uRgxykvg`

---

### **STEP 4: Test Owner Onboarding Flow**

Now test the Stripe Connect owner onboarding:

1. **Log in as a Vehicle Owner** on your live site

2. **Go to Owner Dashboard**
   URL: `https://elitecarhire.au/owner/dashboard`

3. **You should see a warning banner:**
   > "ACTION REQUIRED: Connect Your Bank Account"

4. **Click "Connect Stripe Account Now"**

5. **You will be redirected to Stripe Connect:**
   - URL should be: `https://connect.stripe.com/express/oauth/authorize?...`
   - Should include your client ID (starts with `ca_`)

6. **Complete the Stripe Onboarding:**
   - Enter business/personal details
   - Add bank account (BSB + Account Number)
   - Verify identity (Driver's license or passport)

7. **After completion:**
   - You'll be redirected back to: `https://elitecarhire.au/owner/stripe/return`
   - Should see success message: "Your Stripe account has been connected successfully!"
   - Dashboard should show: "Account Connected & Verified"

---

## 🔧 Troubleshooting

### Issue: "Stripe Connect is not properly configured"

**Cause:** Database settings not updated

**Fix:**
1. Run the SQL migration: `LIVE_STRIPE_SETTINGS.sql`
2. Verify settings in database:
   ```sql
   SELECT * FROM settings WHERE setting_key LIKE 'stripe%';
   ```

---

### Issue: "Invalid security token" when returning from Stripe

**Cause:** Session issue or CSRF token mismatch

**Fix:**
1. Clear browser cookies
2. Try again in incognito/private mode
3. Check that sessions are working on your server

---

### Issue: "Cannot onboard via express oauth due to gated access"

**Cause:** Stripe Connect platform not fully activated in live mode

**Fix:**
1. Go to: https://dashboard.stripe.com/settings/connect
2. Ensure "Express" account type is enabled
3. Complete platform verification (may take 1-2 business days)
4. Ensure your Stripe account is fully verified (business details, identity verification)

---

### Issue: HTTP 500 Error when clicking "Connect Stripe Account Now"

**Possible causes:**
1. `.htaccess` not uploaded (missing environment variables)
2. PHP error (check error logs)

**Fix:**
1. Verify `public/.htaccess` is uploaded with Stripe keys
2. Check server error logs in cPanel
3. Enable error logging in PHP to see details

---

### Issue: Owner redirected back but stripe_account_id not saved

**Cause:** Missing STRIPE_SECRET_KEY environment variable

**Fix:**
1. Verify `public/.htaccess` contains:
   ```apache
   SetEnv STRIPE_SECRET_KEY "sk_live_51STNKiFqTLtGW908..."
   ```
2. Restart web server (if you have access) or wait a few minutes for .htaccess to reload

---

## 📝 Verification Checklist

After deployment, verify each item:

### Database Configuration
- [ ] SQL migration `LIVE_STRIPE_SETTINGS.sql` edited with actual keys
- [ ] SQL migration executed successfully in phpMyAdmin
- [ ] `stripe_connect_client_id` set to your Client ID (starts with `ca_`)
- [ ] `stripe_connect_enabled` = `1`
- [ ] `stripe_webhook_secret` configured

### File Configuration & Uploads
- [ ] Stripe API keys retrieved from Stripe Dashboard
- [ ] `public/.htaccess.example` copied and edited with actual keys
- [ ] `public/.htaccess` uploaded to server with environment variables
- [ ] `database/migrations/LIVE_STRIPE_SETTINGS.sql` edited with actual keys
- [ ] `app/controllers/StripeConnectController.php` uploaded
- [ ] `app/controllers/OwnerController.php` uploaded
- [ ] `app/views/owner/dashboard.php` uploaded
- [ ] `app/views/admin/sidebar.php` uploaded (simplified version)

### Stripe Dashboard Configuration
- [ ] Webhook endpoint created: `https://elitecarhire.au/webhooks/stripe`
- [ ] Webhook events selected (account.updated, payment_intent.*, etc.)
- [ ] Stripe Connect platform enabled for Express accounts
- [ ] Platform settings completed (business info, support details)

### Testing
- [ ] Owner dashboard loads without errors
- [ ] "Connect Stripe Account Now" button visible for unconnected owners
- [ ] Clicking button redirects to Stripe Connect OAuth
- [ ] Client ID in URL matches your Client ID (starts with `ca_`)
- [ ] After onboarding, owner redirected back to dashboard
- [ ] Success message displayed
- [ ] `stripe_account_id` saved in database (check users table)

---

## 🚨 Important Notes

### About Customer Payments

**Owner onboarding (Stripe Connect)** works independently and **does NOT require the Stripe PHP SDK**.

However, **customer payment processing** (when customers pay for bookings) **DOES require** the Stripe PHP library.

#### If you want to accept customer payments:

You'll need to manually install the Stripe PHP library since you don't have SSH access:

1. **Download Stripe PHP Library:**
   - URL: https://github.com/stripe/stripe-php/releases/latest
   - Download the source code ZIP

2. **Extract and Upload:**
   - Extract the ZIP file
   - Upload to: `vendor/stripe/stripe-php/` on your server

3. **Verify Structure:**
   ```
   your-site-root/
   ├── public/
   ├── app/
   └── vendor/
       └── stripe/
           └── stripe-php/
               ├── init.php
               ├── lib/
               └── ...
   ```

**For now:** Owner onboarding will work without the SDK. Customer payments can be added later.

---

## 🎯 Expected Behavior After Deployment

1. **Vehicle Owner Logs In:**
   - Sees warning banner: "Connect Your Bank Account"
   - Clicks "Connect Stripe Account Now"

2. **Redirect to Stripe:**
   - URL: `https://connect.stripe.com/express/oauth/authorize?client_id=ca_TYgipd9Qjnq26uWcXw9V1pTVmOBdZKYg&...`
   - Owner completes onboarding (5-10 minutes)

3. **Return to Dashboard:**
   - URL: `https://elitecarhire.au/owner/stripe/return?code=ac_XXXXX&state=XXXXX`
   - System exchanges code for `stripe_account_id`
   - Saves to database: `users.stripe_account_id = acct_XXXXX`

4. **Dashboard Updated:**
   - Green banner: "Account Connected & Verified"
   - Owner can now confirm bookings
   - Owner will receive payouts directly to their bank account

---

## 📞 Support

If you encounter issues after following this guide:

1. **Check server error logs** (cPanel → Error Logs)
2. **Check database** for saved settings
3. **Test in Stripe Dashboard** (test mode first if needed)
4. **Verify all files uploaded** correctly

**Stripe Support:**
- Dashboard: https://dashboard.stripe.com/support
- Email: support@stripe.com
- Phone: 1800 829 395 (Australia)

---

## ✅ Quick Reference

### Your Live Stripe Keys:
Get your keys from Stripe Dashboard:
- **Client ID:** Dashboard → Settings → Connect → Client ID (starts with `ca_`)
- **Publishable Key:** Dashboard → Developers → API Keys (starts with `pk_live_`)
- **Secret Key:** Dashboard → Developers → API Keys (starts with `sk_live_`)
- **Webhook Secret:** Dashboard → Developers → Webhooks → [Your endpoint] (starts with `whsec_`)

### Key URLs:
- **Owner Dashboard:** `https://elitecarhire.au/owner/dashboard`
- **Connect URL:** `https://elitecarhire.au/owner/stripe/connect`
- **Return URL:** `https://elitecarhire.au/owner/stripe/return`
- **Webhook Endpoint:** `https://elitecarhire.au/webhooks/stripe`

### Database Check:
```sql
-- Check if owner connected
SELECT id, email, stripe_account_id FROM users WHERE role = 'owner';

-- Check Stripe settings
SELECT * FROM settings WHERE setting_key LIKE 'stripe%';
```

---

**Last Updated:** December 7, 2025
**Version:** 2.0 (Live Environment)
