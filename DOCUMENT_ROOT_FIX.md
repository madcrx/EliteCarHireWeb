# Document Root Configuration Fix Guide

## Problem: Directory Listing Instead of Website

If you see a directory listing showing folders like `app/`, `config/`, `database/`, `public/`, etc., your web server document root is misconfigured.

---

## Understanding the Structure

### Correct Elite Car Hire Directory Structure:
```
EliteCarHireWeb/                    ← Repository root (NOT web-accessible)
├── app/                            ← Application code (PRIVATE)
├── config/                         ← Configuration files (PRIVATE)
├── database/                       ← SQL migrations (PRIVATE)
├── storage/                        ← Logs, uploads (PRIVATE)
├── vendor/                         ← Dependencies (PRIVATE)
├── public/                         ← WEB ROOT (ONLY this should be accessible)
│   ├── index.php                   ← Entry point
│   ├── .htaccess                   ← URL rewriting rules
│   ├── assets/                     ← CSS, JS, images
│   └── webhook/                    ← Webhook handlers
├── composer.json
└── .htaccess                       ← Root redirect (fallback)
```

**CRITICAL:** Only the `public/` directory should be web-accessible. Everything else contains sensitive code and configuration.

---

## Solution 1: Fix Document Root in cPanel (RECOMMENDED)

### For Addon Domain:

1. **Log into cPanel**

2. **Navigate to Domains**
   - cPanel Home → Domains section → "Domains"

3. **Manage the Domain**
   - Find `elitecarhire.au` in the list
   - Click **"Manage"** button next to it

4. **Update Document Root**
   - Find field labeled **"Document Root"** or **"Root Directory"**
   - Current (wrong): `/home/username/EliteCarHireWeb`
   - Change to (correct): `/home/username/EliteCarHireWeb/public`
   - Replace `username` with your actual cPanel username

5. **Save Changes**
   - Click **"Save"** or **"Submit"**

6. **Wait 1-2 Minutes**
   - Allow web server to reload configuration

7. **Test**
   - Visit https://elitecarhire.au
   - Should now show the website, not directory listing

### For Primary Domain:

1. **Log into cPanel**

2. **Navigate to Domains or Account Settings**
   - Look for **"Change Primary Domain"** or **"Primary Domain Settings"**
   - Or try: cPanel → Advanced → **"MultiPHP Manager"** → Note your domain details

3. **Find Document Root Setting**
   - May be under: cPanel → Advanced → **"Modify an Account"** (if you have access)
   - Some hosts: Use **"Domain Manager"** or contact support

4. **Alternative - Create Subdomain**
   - If you can't change primary domain document root:
   - Create subdomain: `www.elitecarhire.au`
   - Set its document root to: `/home/username/EliteCarHireWeb/public`
   - Set up redirect from main domain to www

---

## Solution 2: Use Root .htaccess Redirect (Fallback)

If you cannot change the document root through cPanel, I've created a root `.htaccess` file that redirects all traffic to the `public/` subdirectory.

### Deploy the Root .htaccess:

1. **Upload the File**
   - Upload `.htaccess` from repository root to your server's root
   - Location: `/home/username/EliteCarHireWeb/.htaccess`

2. **Verify Contents**
   The file should contain:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On

       # Redirect all requests to public/ subdirectory
       RewriteCond %{REQUEST_URI} !^/public/
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule ^(.*)$ public/$1 [L]

       # Redirect /public/something to /something (clean URLs)
       RewriteCond %{REQUEST_URI} ^/public/(.*)$
       RewriteRule ^public/(.*)$ /$1 [R=301,L]
   </IfModule>

   # Prevent access to sensitive directories
   <IfModule mod_rewrite.c>
       RewriteRule ^(app|config|database|storage|vendor)/.*$ - [F,L]
   </IfModule>

   # Disable directory browsing
   Options -Indexes
   ```

3. **Test**
   - Visit https://elitecarhire.au
   - Should redirect to public/ and display website

### Limitations of This Approach:
- URLs will internally reference `/public/` (users won't see it)
- Less efficient than proper document root configuration
- Still exposes directory structure (though access is denied)
- **Not as secure** as proper document root

---

## Solution 3: Move Files to Correct Location

If your host requires files in a specific location:

### Common Hosting Structures:

**Shared Hosting (most common):**
```
/home/username/
├── public_html/          ← This is the document root
│   ├── index.php         ← Move public/* here
│   ├── .htaccess
│   ├── assets/
│   └── webhook/
├── app/                  ← Move outside public_html
├── config/
├── database/
├── storage/
└── vendor/
```

**Steps:**
1. Move contents of `public/` to `public_html/`
2. Move other directories (`app/`, `config/`, etc.) outside `public_html/`
3. Update paths in `public_html/index.php`:
   ```php
   // Change from:
   require __DIR__ . '/../config/app.php';

   // To (if files are one level up):
   require __DIR__ . '/../config/app.php';

   // Or (if files are in home directory):
   require '/home/username/config/app.php';
   ```

---

## Verification Checklist

After fixing, verify:

- [ ] **Website Loads:** https://elitecarhire.au shows the Elite Car Hire homepage
- [ ] **No Directory Listing:** Don't see folder list anymore
- [ ] **Assets Load:** CSS, images, and JavaScript work correctly
- [ ] **Internal Pages Work:** /vehicles, /contact, /about all load
- [ ] **Admin Panel Works:** /admin login page accessible
- [ ] **Sensitive Directories Blocked:**
  - Try accessing: https://elitecarhire.au/config/database.php (should be 403 Forbidden)
  - Try accessing: https://elitecarhire.au/app/ (should be 403 Forbidden)
  - Try accessing: https://elitecarhire.au/database/ (should be 403 Forbidden)

---

## Security Impact (WHY THIS IS CRITICAL)

### Current Exposure (if not fixed):
- ❌ **Database credentials visible:** `config/database.php`
- ❌ **Application code exposed:** All PHP source code readable
- ❌ **SQL files exposed:** Database structure and migrations
- ❌ **Email credentials visible:** SMTP settings
- ❌ **Stripe API keys visible:** If hardcoded anywhere
- ❌ **User data at risk:** Storage directory may be accessible

### After Fix:
- ✅ Only `public/` directory is web-accessible
- ✅ All sensitive files protected
- ✅ Application code hidden
- ✅ Configuration files private

---

## Common cPanel Paths

Different hosts use different paths. Here are common patterns:

| Host Type | Repository Location | Public Directory |
|-----------|-------------------|------------------|
| **Standard cPanel** | `/home/username/EliteCarHireWeb/` | `/home/username/EliteCarHireWeb/public/` |
| **Subdomain** | `/home/username/domains/elitecarhire.au/` | `/home/username/domains/elitecarhire.au/public_html/` |
| **Addon Domain** | `/home/username/public_html/elitecarhire.au/` | `/home/username/public_html/elitecarhire.au/public/` |
| **Primary** | `/home/username/` | `/home/username/public_html/` |

---

## Troubleshooting

### Still Seeing Directory Listing?

**Check .htaccess is uploaded:**
```bash
# Via SSH or terminal:
ls -la /path/to/EliteCarHireWeb/.htaccess
ls -la /path/to/EliteCarHireWeb/public/.htaccess
```

**Check mod_rewrite is enabled:**
- cPanel → Software → Select PHP Version → Check if `mod_rewrite` is enabled
- Or contact hosting support to enable it

**Check .htaccess is being read:**
Add test line to `.htaccess`:
```apache
# Test line
Deny from all
```
If you get "403 Forbidden", .htaccess is working. Remove test line.

### Getting 500 Internal Server Error?

**Check error logs:**
- cPanel → Metrics → Errors
- Look for `.htaccess` syntax errors

**Try simplified .htaccess:**
```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
Options -Indexes
```

### Assets (CSS/JS/Images) Not Loading?

**Check paths in public/index.php:**
```php
// Should use relative paths:
<link rel="stylesheet" href="/assets/css/style.css">

// Not absolute server paths:
<link rel="stylesheet" href="/home/username/public/assets/css/style.css">
```

---

## Getting Help

If you're still stuck after trying these solutions:

1. **Check with your hosting provider:**
   - Ask: "How do I change the document root for elitecarhire.au to point to a subdirectory?"
   - Provide: "I need the document root to point to the `public/` subdirectory"

2. **Provide them this structure:**
   ```
   /home/username/EliteCarHireWeb/public/   ← Make this the document root
   ```

3. **Common hosting provider documentation:**
   - **cPanel:** https://docs.cpanel.net/cpanel/domains/addon-domains/
   - **Plesk:** https://docs.plesk.com/en-US/obsidian/customer-guide/
   - **WHM:** Contact your hosting provider

---

**IMMEDIATE ACTION REQUIRED:**
Use Solution 1 (change document root) if possible. It's the most secure and correct approach. Solution 2 (root .htaccess) is a fallback but not ideal for security.

The root `.htaccess` file has been created and committed to the repository as a fallback solution.
