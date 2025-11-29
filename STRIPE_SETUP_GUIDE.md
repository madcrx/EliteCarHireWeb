# Stripe Payment Integration Guide
## Elite Car Hire - Complete Setup Documentation

This guide provides comprehensive instructions for integrating Stripe payment processing into Elite Car Hire.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Stripe Account Setup](#stripe-account-setup)
4. [Database Setup](#database-setup)
5. [Install Stripe PHP Library](#install-stripe-php-library)
6. [Configure Environment Variables](#configure-environment-variables)
7. [Test Payment Flow](#test-payment-flow)
8. [Configure Webhooks](#configure-webhooks)
9. [Go Live Checklist](#go-live-checklist)
10. [Stripe Connect for Owner Payouts](#stripe-connect-for-owner-payouts)
11. [Testing](#testing)
12. [Troubleshooting](#troubleshooting)

---

## Overview

### What's Included

The Stripe integration provides:
- ✅ **Real payment processing** via Stripe Payment Intents API
- ✅ **3D Secure (SCA) support** for European cards
- ✅ **Automatic receipt emails** to customers
- ✅ **Owner payout tracking** with 15% commission
- ✅ **Refund processing** for admins
- ✅ **Webhook handlers** for payment confirmations
- ✅ **Dispute/chargeback** notifications
- ✅ **Stripe Connect** for automated owner payouts (optional)

### Payment Flow

```
Customer Books Vehicle
    ↓
Owner Confirms Booking
    ↓
Customer Pays with Card (Stripe)
    ↓
Stripe Processes Payment
    ↓
Money Held by Stripe
    ↓
Owner Gets Payout (Total - 15% Commission)
```

### Commission Structure

- **Customer pays:** $100.00
- **Platform commission (15%):** $15.00
- **Owner receives:** $85.00

---

## Prerequisites

Before you begin, ensure you have:
- ✅ Access to your server (cPanel or SSH)
- ✅ Database access
- ✅ Australian Business Number (ABN) for Stripe account
- ✅ Bank account for receiving payments
- ✅ Email address for Stripe communications
- ✅ Composer installed (or manual PHP library installation capability)

---

## Stripe Account Setup

### Step 1: Create Stripe Account

1. **Visit:** https://dashboard.stripe.com/register
2. **Enter:**
   - Email address
   - Full name
   - Country: **Australia**
   - Password

3. **Verify email address**

### Step 2: Complete Business Profile

1. **Go to:** Stripe Dashboard → Settings → Business settings
2. **Provide:**
   - **Business name:** Elite Car Hire
   - **Business type:** Company
   - **Industry:** Transportation/Car rental
   - **Website:** https://ech.cyberlogicit.com.au
   - **Support email:** support@elitecarhire.au
   - **Support phone:** 0406 907 849

3. **Business details:**
   - **ABN** (Australian Business Number)
   - **Business address**
   - **GST registered:** Yes/No

4. **Bank account details:**
   - **BSB:** Your 6-digit BSB
   - **Account number:** Your account number
   - **Account holder name**

5. **Personal details** (for verification):
   - **Director/owner name**
   - **Date of birth**
   - **Address**
   - **ID verification** (Driver's license or passport)

**⏱️ Verification Time:** 1-3 business days

---

## Database Setup

### Step 1: Run Migration

Execute the SQL migration to add Stripe-related tables:

```bash
mysql -u your_db_user -p your_db_name < database/migrations/stripe_integration.sql
```

Or via phpMyAdmin:
1. Open phpMyAdmin
2. Select your database
3. Go to **SQL** tab
4. Copy and paste contents of `database/migrations/stripe_integration.sql`
5. Click **Go**

This adds:
- `stripe_webhook_events` table
- `payment_failures` table
- `payment_disputes` table
- `stripe_account_id` column to `users` table

---

## Install Stripe PHP Library

### Option A: Via Composer (Recommended)

```bash
cd /home/cyberlog/public_html/ech.cyberlogicit.com.au
composer require stripe/stripe-php
```

### Option B: Manual Installation

1. **Download:** https://github.com/stripe/stripe-php/archive/refs/heads/master.zip
2. **Extract to:** `/path/to/your/site/vendor/stripe/stripe-php/`
3. **Verify structure:**
   ```
   vendor/
   └── stripe/
       └── stripe-php/
           ├── init.php
           ├── lib/
           └── ...
   ```

---

## Configure Environment Variables

### Method 1: Using .htaccess (Recommended for cPanel)

Edit `/public_html/public/.htaccess` and add:

```apache
<IfModule mod_env.c>
    # Email Configuration
    SetEnv SMTP_HOST "mail.elitecarhire.au"
    SetEnv SMTP_PORT "587"
    SetEnv SMTP_USER "support@elitecarhire.au"
    SetEnv SMTP_PASS "your_email_password"

    # Stripe Test Keys (for development)
    SetEnv STRIPE_SECRET_KEY "sk_test_..."
    SetEnv STRIPE_PUBLISHABLE_KEY "pk_test_..."
    SetEnv STRIPE_WEBHOOK_SECRET "whsec_..."

    # When ready to go live, replace with:
    # SetEnv STRIPE_SECRET_KEY "sk_live_..."
    # SetEnv STRIPE_PUBLISHABLE_KEY "pk_live_..."
    # SetEnv STRIPE_WEBHOOK_SECRET "whsec_..."
</IfModule>
```

### Method 2: Using .env File

Create `/public_html/.env`:

```env
# Email Configuration
SMTP_HOST=mail.elitecarhire.au
SMTP_PORT=587
SMTP_USER=support@elitecarhire.au
SMTP_PASS=your_email_password

# Stripe Configuration (Test Mode)
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Production (uncomment when going live)
# STRIPE_SECRET_KEY=sk_live_...
# STRIPE_PUBLISHABLE_KEY=pk_live_...
# STRIPE_WEBHOOK_SECRET=whsec_...
```

**Security:** Ensure `.env` has proper permissions:
```bash
chmod 600 .env
```

### Getting Your Stripe API Keys

1. **Go to:** https://dashboard.stripe.com/test/apikeys
2. **Copy:**
   - **Publishable key:** `pk_test_...` (starts with pk_test)
   - **Secret key:** `sk_test_...` (starts with sk_test)
3. **Click "Reveal test key"** to see the secret key
4. **Store securely**

⚠️ **IMPORTANT:** Never commit API keys to version control!

---

## Test Payment Flow

### Step 1: Test with Stripe Test Cards

**Test Card Numbers:**

| Card | Number | CVC | Expiry | Result |
|------|--------|-----|--------|--------|
| **Success** | `4242 4242 4242 4242` | Any 3 digits | Any future date | Payment succeeds |
| **Decline** | `4000 0000 0000 0002` | Any 3 digits | Any future date | Card declined |
| **3D Secure** | `4000 0027 6000 3184` | Any 3 digits | Any future date | Requires authentication |
| **Insufficient Funds** | `4000 0000 0000 9995` | Any 3 digits | Any future date | Insufficient funds |

### Step 2: Make Test Booking

1. **Log in as customer**
2. **Browse vehicles** and select one
3. **Create booking** with:
   - Future date
   - 4+ hours duration
   - Valid pickup location
4. **Wait for owner to confirm** (log in as owner and confirm)
5. **Pay for booking:**
   - Use test card: `4242 4242 4242 4242`
   - Expiry: `12/25`
   - CVC: `123`
6. **Verify payment success**

### Step 3: Check Database

```sql
-- Check payment was recorded
SELECT * FROM payments WHERE booking_id = YOUR_BOOKING_ID;

-- Check booking status updated
SELECT payment_status FROM bookings WHERE id = YOUR_BOOKING_ID;
-- Should show: 'paid'

-- Check payout created
SELECT * FROM payouts WHERE booking_id = YOUR_BOOKING_ID;
```

---

## Configure Webhooks

Webhooks allow Stripe to notify your application about payment events.

### Step 1: Add Webhook Endpoint in Stripe

1. **Go to:** https://dashboard.stripe.com/test/webhooks
2. **Click:** "Add endpoint"
3. **Enter endpoint URL:**
   ```
   https://ech.cyberlogicit.com.au/webhooks/stripe
   ```
4. **Select events to listen to:**
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
   - `charge.dispute.created`
   - `payout.paid`
   - `payout.failed`

5. **Click:** "Add endpoint"

### Step 2: Get Webhook Secret

1. After creating the endpoint, click on it
2. **Click:** "Reveal" next to "Signing secret"
3. **Copy:** `whsec_...`
4. **Add to environment variables** (see Configure Environment Variables section)

### Step 3: Test Webhook

1. In Stripe Dashboard, go to your webhook endpoint
2. Click **"Send test webhook"**
3. Select `payment_intent.succeeded`
4. Click **"Send test webhook"**
5. **Check:** Database table `stripe_webhook_events` for the event

```sql
SELECT * FROM stripe_webhook_events ORDER BY processed_at DESC LIMIT 5;
```

---

## Go Live Checklist

Before accepting real payments:

### Business Verification
- [ ] Stripe account fully verified
- [ ] Bank account connected and verified
- [ ] Business details complete (ABN, address, etc.)
- [ ] Identity verification completed

### Technical Setup
- [ ] Stripe PHP library installed
- [ ] Database migrations run
- [ ] Test payments successful
- [ ] Webhooks configured and tested
- [ ] Email notifications tested
- [ ] **Switch to LIVE API keys**

### Switching to Live Mode

1. **Get Live API Keys:**
   - Go to: https://dashboard.stripe.com/apikeys (remove /test/)
   - Toggle to **"Live"** mode (top right)
   - Copy **Live Secret Key** (`sk_live_...`)
   - Copy **Live Publishable Key** (`pk_live_...`)

2. **Update Environment Variables:**
   - Replace `sk_test_...` with `sk_live_...`
   - Replace `pk_test_...` with `pk_live_...`

3. **Create Live Webhook:**
   - Go to: https://dashboard.stripe.com/webhooks (live mode)
   - Add endpoint: `https://ech.cyberlogicit.com.au/webhooks/stripe`
   - Select same events as test webhook
   - Get new webhook secret (`whsec_...`) and update environment variables

### Final Checks
- [ ] Make test payment with real card (small amount like $1.00)
- [ ] Verify payment appears in Stripe Dashboard
- [ ] Verify payment recorded in database
- [ ] Verify email notifications sent
- [ ] Test refund process (if needed)

### Legal & Compliance
- [ ] Terms of Service updated (already done ✓)
- [ ] Privacy Policy updated (already done ✓)
- [ ] GST compliance configured
- [ ] Receipts include ABN/GST information

---

## Stripe Connect for Owner Payouts

**Optional:** Automate payouts to vehicle owners using Stripe Connect.

### What is Stripe Connect?

Stripe Connect allows you to:
- Automatically split payments between platform and owners
- Pay owners directly to their bank accounts
- Handle tax reporting (1099s in US, equivalent in AU)
- Manage multiple seller accounts

### Setup Process

#### 1. Enable Connect in Stripe

1. Go to: https://dashboard.stripe.com/settings/connect
2. Click **"Get started"**
3. Select **"Platform or marketplace"**
4. Configure:
   - **Platform name:** Elite Car Hire
   - **Type:** Standard Connect
   - **Onboarding:** Express accounts

5. Get **Connect Client ID** (starts with `ca_`)

#### 2. Add Connect Client ID

Add to environment variables:
```apache
SetEnv STRIPE_CONNECT_CLIENT_ID "ca_..."
```

#### 3. Create Onboarding Flow for Owners

When a vehicle owner registers, they'll need to:
1. Connect their Stripe account
2. Provide bank details
3. Verify identity

**Implementation** (already in code):
- Owner clicks "Connect Stripe" in dashboard
- Redirected to Stripe onboarding
- Returns with `stripe_account_id`
- Stored in `users.stripe_account_id`

#### 4. Automatic Payouts

When payment is processed:
- Stripe automatically deducts 15% commission
- Remaining 85% goes directly to owner's bank account
- No manual payout processing needed!

---

## Testing

### Test Scenarios

#### 1. Successful Payment
- **Card:** `4242 4242 4242 4242`
- **Expected:** Payment succeeds, booking marked as paid

#### 2. Declined Card
- **Card:** `4000 0000 0000 0002`
- **Expected:** Error message shown, booking remains unpaid

#### 3. 3D Secure Authentication
- **Card:** `4000 0027 6000 3184`
- **Expected:** Redirected to authentication page, then payment succeeds

#### 4. Insufficient Funds
- **Card:** `4000 0000 0000 9995`
- **Expected:** "Insufficient funds" error

#### 5. Refund (Admin)
- Make successful payment
- Log in as admin
- Go to Payments → Select payment → Refund
- **Expected:** Refund processed, money returned

### Monitoring Payments

**View in Stripe Dashboard:**
- Payments: https://dashboard.stripe.com/test/payments
- Customers: https://dashboard.stripe.com/test/customers
- Disputes: https://dashboard.stripe.com/test/disputes
- Webhooks: https://dashboard.stripe.com/test/webhooks

**Database Queries:**

```sql
-- Recent payments
SELECT p.*, b.booking_reference, b.total_amount
FROM payments p
JOIN bookings b ON p.booking_id = b.id
ORDER BY p.created_at DESC
LIMIT 20;

-- Failed payments
SELECT * FROM payment_failures
ORDER BY failure_date DESC;

-- Webhook events
SELECT * FROM stripe_webhook_events
ORDER BY processed_at DESC
LIMIT 50;
```

---

## Troubleshooting

### Issue: "Payment system configuration error"

**Cause:** Stripe API keys not set or invalid

**Solution:**
1. Verify environment variables are set correctly
2. Check `.htaccess` or `.env` file
3. Ensure keys start with `sk_test_` or `sk_live_`
4. Test keys are correct:
   ```bash
   curl https://api.stripe.com/v1/charges \
     -u sk_test_YOUR_KEY: \
     -d amount=100 \
     -d currency=aud
   ```

### Issue: "Stripe PHP library not found"

**Cause:** Stripe library not installed

**Solution:**
```bash
composer require stripe/stripe-php
```

Or manually download and place in `vendor/stripe/stripe-php/`

### Issue: Webhook signature verification failed

**Cause:** Incorrect webhook secret

**Solution:**
1. Go to Stripe Dashboard → Webhooks
2. Click on your webhook endpoint
3. Reveal signing secret
4. Update `STRIPE_WEBHOOK_SECRET` in environment variables
5. Restart web server (if needed)

### Issue: "Card declined"

**Causes:**
- Insufficient funds
- Card expired
- Incorrect CVC
- Bank declined (fraud prevention)

**Solution:**
- For testing: Use valid test cards (see Test Payment Flow)
- For production: Customer should contact their bank or try different card

### Issue: Payment succeeded but not recorded in database

**Cause:** Synchronous processing failed, webhook not triggered

**Solution:**
1. Check `stripe_webhook_events` table
2. Manually process webhook:
   ```sql
   SELECT * FROM stripe_webhook_events
   WHERE event_type = 'payment_intent.succeeded'
   ORDER BY processed_at DESC;
   ```
3. Webhook handler will create payment record

### Issue: Refund fails

**Causes:**
- Payment not captured yet
- Already refunded
- Stripe account has insufficient balance

**Solution:**
- Wait 24 hours after payment
- Check Stripe Dashboard for actual refund status
- Contact Stripe support if issue persists

---

## Security Best Practices

### 1. Protect API Keys
- ✅ Never commit to version control
- ✅ Use environment variables
- ✅ Restrict file permissions (chmod 600)
- ✅ Use different keys for test/live

### 2. Webhook Security
- ✅ Always verify webhook signatures
- ✅ Use HTTPS for webhook endpoint
- ✅ Log all webhook events

### 3. PCI Compliance
- ✅ Never store full card numbers
- ✅ Use Stripe.js for card collection (frontend)
- ✅ Never log sensitive data
- ✅ Use HTTPS everywhere

### 4. Monitoring
- ✅ Set up Stripe email notifications
- ✅ Monitor for unusual activity
- ✅ Review failed payments regularly
- ✅ Set up dispute alerts

---

## Stripe Fees (Australia)

**Domestic Cards:**
- 1.75% + $0.30 AUD per transaction

**International Cards:**
- 2.9% + $0.30 AUD per transaction

**Disputes:**
- $25 AUD per dispute (refunded if you win)

**Example:** Customer pays $100
- Stripe fee: $1.75 + $0.30 = $2.05
- Your commission (15%): $15.00
- Owner receives: $100 - $2.05 - $15.00 = $82.95

**Note:** You can absorb Stripe fees or pass them to customers/owners.

---

## Support & Resources

### Stripe Documentation
- **Main Docs:** https://stripe.com/docs
- **API Reference:** https://stripe.com/docs/api
- **Payment Intents:** https://stripe.com/docs/payments/payment-intents
- **Webhooks:** https://stripe.com/docs/webhooks
- **Connect:** https://stripe.com/docs/connect

### Stripe Support
- **Email:** support@stripe.com
- **Phone:** 1800 829 395 (Australia)
- **Dashboard:** https://dashboard.stripe.com/support

### Elite Car Hire Support
- **Email:** support@elitecarhire.au
- **Phone:** 0406 907 849

---

## Quick Reference

### API Keys Location
Stripe Dashboard → Developers → API keys

### Test Mode Toggle
Top right of Stripe Dashboard

### Webhook Endpoint
```
https://ech.cyberlogicit.com.au/webhooks/stripe
```

### Test Cards Quick Access
```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
3D Secure: 4000 0027 6000 3184
```

### Important Database Tables
- `payments` - Payment records
- `payouts` - Owner payouts
- `stripe_webhook_events` - Webhook log
- `payment_failures` - Failed payments
- `payment_disputes` - Chargebacks

---

## Deployment Checklist

- [ ] Install Stripe PHP library
- [ ] Run database migrations
- [ ] Configure environment variables
- [ ] Test with test cards
- [ ] Configure webhooks (test mode)
- [ ] Verify email notifications
- [ ] Complete Stripe business verification
- [ ] Get live API keys
- [ ] Update to live API keys
- [ ] Configure live webhooks
- [ ] Make $1 test payment
- [ ] Monitor first real payments closely
- [ ] Set up Stripe Connect (optional)
- [ ] Document payout schedule for owners

---

**Last Updated:** November 2025
**Version:** 1.0.0
**Integration:** Stripe Payment Intents API v2023-10-16
