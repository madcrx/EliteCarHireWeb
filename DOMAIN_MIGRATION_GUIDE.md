# Domain Migration Guide: elitecarhire.com.au → elitecarhire.au

## Overview
This guide covers all necessary changes to migrate Elite Car Hire from `elitecarhire.com.au` to the new domain `elitecarhire.au`.

---

## 📋 Complete Migration Checklist

### Phase 1: Code & Configuration Updates

#### ✅ Files Already Correct (No Changes Needed)
These files already use `elitecarhire.au`:

- ✅ `config/app.php` - Email: `support@elitecarhire.au`
- ✅ `app/views/layout.php` - Footer email
- ✅ `app/views/public/about.php` - Contact email
- ✅ `app/views/public/contact.php` - Contact email
- ✅ `app/controllers/AdminController.php` - Email notifications
- ✅ `public/.htaccess` - No domain-specific configuration

#### 🔧 Files Requiring Updates

**1. Database Content**
- **File:** `database/insert_terms_and_faq_content.sql`
- **Changes:** Update 4 instances of `support@elitecarhire.com.au` → `support@elitecarhire.au`
- **Status:** ⚠️ NEEDS UPDATE

**2. Admin Settings Template**
- **File:** `app/views/admin/settings/system.php`
- **Line 32:** Change placeholder from `https://elitecarhire.com.au` → `https://elitecarhire.au`
- **Status:** ⚠️ NEEDS UPDATE

**3. Documentation Files**
- **File:** `STRIPE_DEPLOYMENT_GUIDE.md`
- **Changes:** Update webhook URL examples
- **Status:** ⚠️ NEEDS UPDATE

- **File:** `TERMS_FAQ_DEPLOYMENT.md`
- **Changes:** Update domain references
- **Status:** ⚠️ NEEDS UPDATE

---

## 🗄️ Database Updates Required

### Option A: For Fresh Installations
If deploying to a new database, simply run the updated SQL files after making code changes.

### Option B: For Existing Databases
Run these SQL queries to update existing data:

```sql
-- Update site URL in settings table (if exists)
UPDATE settings
SET setting_value = 'https://elitecarhire.au'
WHERE setting_key = 'site_url';

-- Update from email in settings table (if exists)
UPDATE settings
SET setting_value = 'support@elitecarhire.au'
WHERE setting_key = 'from_email' OR setting_key = 'email_from_address';

-- Update Terms of Service content
UPDATE cms_pages
SET content = REPLACE(content, 'support@elitecarhire.com.au', 'support@elitecarhire.au')
WHERE page_key = 'terms';

-- Update FAQ content
UPDATE cms_pages
SET content = REPLACE(content, 'support@elitecarhire.com.au', 'support@elitecarhire.au')
WHERE page_key = 'faq';
```

---

## 🌐 Domain & Hosting Configuration

### 1. Domain DNS Setup

**For elitecarhire.au:**

| Record Type | Name | Value | TTL |
|-------------|------|-------|-----|
| A | @ | [Your Server IP] | 3600 |
| A | www | [Your Server IP] | 3600 |
| CNAME | www | elitecarhire.au | 3600 |
| MX | @ | [Your Mail Server] | 3600 |
| TXT | @ | "v=spf1 include:[your-mail-provider] ~all" | 3600 |

**Nameservers:** Point to your hosting provider's nameservers

### 2. cPanel Configuration

**2.1 Add New Domain**
1. Log into cPanel
2. Go to **Domains** → **Addon Domains** (or **Primary Domain** if changing primary)
3. Add `elitecarhire.au`
4. Document Root: `/public_html/elitecarhire.au` (or existing path)

**2.2 SSL Certificate**
1. cPanel → **SSL/TLS**
2. Install SSL certificate for `elitecarhire.au` and `www.elitecarhire.au`
3. Use **AutoSSL** (free) or upload commercial certificate

**2.3 Force HTTPS**
Enable HTTPS redirect in `/public/.htaccess` (lines 4-6):
```apache
# Uncomment these lines:
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

### 3. Email Account Setup

**Create Email Accounts in cPanel:**

1. **support@elitecarhire.au** - Main support email
   - Quota: Unlimited
   - Forward to team members if needed

2. **noreply@elitecarhire.au** - Automated notifications (optional)

3. **admin@elitecarhire.au** - Admin notifications (optional)

**Update SMTP Settings in Admin Panel:**
1. Log into Admin → Settings → Email Configuration
2. Update SMTP credentials for new domain
3. Test email sending

---

## 🔧 Application Configuration

### 1. Update Admin System Settings

**Via Admin Dashboard:**
1. Log into admin panel at `/admin`
2. Navigate to **Settings** → **System Configuration**
3. Update **Site URL** to `https://elitecarhire.au`
4. Click **Save**

**Via Database (Alternative):**
```sql
UPDATE settings SET setting_value = 'https://elitecarhire.au' WHERE setting_key = 'site_url';
UPDATE settings SET setting_value = 'Elite Car Hire' WHERE setting_key = 'site_name';
```

### 2. Update Email Configuration

**Via Admin Dashboard:**
1. Admin → Settings → Email Configuration
2. Update **From Address** to `support@elitecarhire.au`
3. Update **SMTP Host** (if changed)
4. Update **SMTP Username** to match new domain
5. Test email sending

### 3. Environment Variables (if using .env file)

If you have a `.env` file, update:
```env
APP_URL=https://elitecarhire.au
APP_NAME="Elite Car Hire"

MAIL_FROM_ADDRESS=support@elitecarhire.au
MAIL_FROM_NAME="Elite Car Hire"

SMTP_HOST=mail.elitecarhire.au
SMTP_USER=support@elitecarhire.au
```

---

## 💳 Third-Party Service Updates

### 1. Stripe Configuration

**Update Webhook URL:**
1. Log into **Stripe Dashboard**: https://dashboard.stripe.com
2. Go to **Developers** → **Webhooks**
3. Find existing webhook for old domain
4. Click **"Update details"**
5. Change URL to: `https://elitecarhire.au/webhook/stripe.php`
6. Save changes

**Or create new webhook:**
1. Click **"+ Add endpoint"**
2. Endpoint URL: `https://elitecarhire.au/webhook/stripe.php`
3. Events to send:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
4. Copy new webhook signing secret
5. Update in Admin → Settings → Stripe Settings

### 2. Google Services (if applicable)

**Google Analytics:**
- Add new property for `elitecarhire.au`
- Update tracking code in `app/views/layout.php`

**Google Search Console:**
- Add and verify `elitecarhire.au`
- Submit new sitemap: `https://elitecarhire.au/sitemap.xml`

**Google Business Profile:**
- Update website URL to `elitecarhire.au`

### 3. Social Media & Marketing

- **Facebook:** Update page website URL
- **Instagram:** Update bio link
- **LinkedIn:** Update company website
- **Google Ads:** Update final URLs
- **Email Signatures:** Update to new domain

---

## 🔄 Redirect Old Domain to New Domain

### Option 1: Via .htaccess (Recommended)

Add to `/public/.htaccess` **at the very top**, before existing rules:

```apache
# Redirect old domain to new domain
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect elitecarhire.com.au to elitecarhire.au
    RewriteCond %{HTTP_HOST} ^(www\.)?elitecarhire\.com\.au$ [NC]
    RewriteRule ^(.*)$ https://elitecarhire.au/$1 [R=301,L]
</IfModule>
```

This creates a **permanent 301 redirect** preserving:
- SEO rankings
- All URLs (e.g., `/vehicles`, `/contact`)
- HTTPS status

### Option 2: Via cPanel Redirects

1. cPanel → **Domains** → **Redirects**
2. Type: **Permanent (301)**
3. Domain: `elitecarhire.com.au`
4. Redirects to: `https://elitecarhire.au`
5. Redirect with or without www: **Choose appropriate option**
6. Wild Card Redirect: **Yes** (preserves paths)

### Testing Redirects

```bash
# Test redirect from old to new domain
curl -I https://elitecarhire.com.au
# Should show: Location: https://elitecarhire.au/

curl -I https://www.elitecarhire.com.au/vehicles
# Should show: Location: https://elitecarhire.au/vehicles
```

---

## 🧪 Testing Checklist

### After Code Deployment

- [ ] Homepage loads at `https://elitecarhire.au`
- [ ] All pages accessible (vehicles, contact, about, terms, faq)
- [ ] Admin login works at `https://elitecarhire.au/admin`
- [ ] Customer/Owner dashboards load correctly
- [ ] Static assets load (CSS, JS, images)

### Email Testing

- [ ] Test registration email
- [ ] Test booking confirmation email
- [ ] Test password reset email
- [ ] Check "From" address shows `support@elitecarhire.au`
- [ ] Check email links point to new domain

### Payment Testing

- [ ] Test Stripe checkout with test card
- [ ] Verify webhook receives events at new URL
- [ ] Check payment confirmation emails
- [ ] Verify payment records in database

### Redirect Testing

- [ ] `elitecarhire.com.au` → `elitecarhire.au` (301)
- [ ] `www.elitecarhire.com.au` → `elitecarhire.au` (301)
- [ ] Deep links redirect (e.g., `/vehicles` → new domain `/vehicles`)
- [ ] HTTPS enforced on new domain

### SEO & Search

- [ ] Submit new sitemap to Google Search Console
- [ ] Update robots.txt if needed
- [ ] Check Google Analytics tracking
- [ ] Verify structured data still valid

---

## 📤 Deployment Steps (Summary)

### Step 1: Update Code Files (Development)
```bash
# Make code changes locally
# Update files listed in "Files Requiring Updates" section
git add -A
git commit -m "Update domain from elitecarhire.com.au to elitecarhire.au"
git push
```

### Step 2: Upload to Server
- Upload updated files via FTP/SFTP
- Or pull from Git repository

### Step 3: Update Database
- Run SQL update queries (see Database Updates section)
- Or re-run updated SQL migration files

### Step 4: Configure Domain
- Set up DNS records
- Add domain in cPanel
- Install SSL certificate
- Enable HTTPS redirect

### Step 5: Update Admin Settings
- Log into admin panel
- Update Site URL setting
- Update email settings
- Test email sending

### Step 6: Update Third-Party Services
- Update Stripe webhook URL
- Update Google Analytics
- Update social media links

### Step 7: Set Up Redirects
- Add 301 redirects from old domain
- Test redirects thoroughly

### Step 8: Test Everything
- Complete testing checklist
- Fix any issues found

### Step 9: Monitor
- Check error logs
- Monitor email delivery
- Watch for 404 errors
- Monitor payment processing

---

## 🔍 Common Issues & Solutions

### Issue: Old domain still showing

**Check:**
- Browser cache - Clear cache or use Incognito mode
- DNS propagation - Can take up to 48 hours
- .htaccess redirect - Verify syntax is correct

### Issue: Emails not sending from new domain

**Check:**
- SMTP credentials updated for new domain
- SPF/DKIM records configured
- Email account exists in cPanel
- Test with admin email settings page

### Issue: SSL certificate errors

**Check:**
- SSL installed for both `elitecarhire.au` and `www.elitecarhire.au`
- HTTPS redirect enabled in .htaccess
- Mixed content warnings - update hardcoded HTTP URLs

### Issue: Stripe webhooks failing

**Check:**
- Webhook URL updated in Stripe dashboard
- Webhook secret matches admin settings
- New domain publicly accessible (not behind firewall)
- Check Stripe dashboard webhook logs

### Issue: 404 errors on old links

**Check:**
- Wild card redirect enabled
- .htaccess redirect preserves path: `$1` variable
- Test specific URLs: `/vehicles`, `/contact`, etc.

---

## 📞 Support Contacts

**Domain Registrar:** (Your domain registrar for elitecarhire.au)
**Hosting Provider:** (Your cPanel hosting provider)
**Email:** support@elitecarhire.au
**Stripe Support:** https://support.stripe.com

---

## 📅 Timeline Recommendation

**2 Weeks Before:**
- Purchase `elitecarhire.au` domain
- Plan DNS configuration
- Prepare code updates

**1 Week Before:**
- Configure DNS records
- Set up domain in cPanel
- Install SSL certificate
- Test in staging environment

**Launch Day:**
- Deploy code updates
- Update database
- Update admin settings
- Configure redirects
- Update Stripe webhook
- Announce domain change

**1 Week After:**
- Monitor analytics
- Check for 404 errors
- Verify email delivery
- Monitor payment processing
- Update any missed social media links

**1 Month After:**
- Review analytics for redirect issues
- Consider keeping old domain for another 6-12 months
- Eventually let old domain expire (after sufficient redirect period)

---

## 🎯 SEO Preservation

**Why 301 Redirects Matter:**
- Preserves 90-99% of link equity (SEO value)
- Tells search engines permanent move
- Redirects users automatically
- Maintains traffic during transition

**Best Practices:**
- Keep old domain active for at least 6-12 months
- Maintain 301 redirects indefinitely if possible
- Update external backlinks where you have control
- Submit change of address in Google Search Console

---

**Last Updated:** November 2025
**Version:** 1.0

This comprehensive guide ensures a smooth migration from `elitecarhire.com.au` to `elitecarhire.au` with minimal disruption to users and SEO.
