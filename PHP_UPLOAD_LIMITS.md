# PHP Upload Size Limits

## Issue
The default PHP configuration has `upload_max_filesize` set to **2MB**, which is too small for high-resolution vehicle photos.

## Solutions

### Option 1: Update .htaccess (Recommended for cPanel/Shared Hosting)
The `.htaccess` file in `/public/` has been configured with:
```apache
php_value upload_max_filesize 10M
php_value post_max_size 20M
```

This works if your server uses `mod_php`. If you see "500 Internal Server Error", this method isn't supported.

### Option 2: Create php.ini in public directory
Create `/public/php.ini` with:
```ini
upload_max_filesize = 10M
post_max_size = 20M
max_file_uploads = 20
max_execution_time = 300
```

### Option 3: Update Global php.ini (Server Access Required)
If you have root access, edit `/etc/php/8.x/apache2/php.ini` and update:
```ini
upload_max_filesize = 10M
post_max_size = 20M
```

Then restart Apache:
```bash
sudo systemctl restart apache2
```

### Option 4: cPanel PHP Settings (Shared Hosting)
1. Log into cPanel
2. Go to "Select PHP Version" or "MultiPHP INI Editor"
3. Update these values:
   - `upload_max_filesize`: 10M
   - `post_max_size`: 20M
4. Save changes

## Current Behavior
The upload form now:
- ✅ Shows **accurate size limits** based on PHP configuration
- ✅ Provides **detailed error messages** with actual file sizes
- ✅ Works with **current 2MB limit** (just use smaller images)
- ✅ Will automatically support **larger files** when limits are increased

## Verifying Settings
Check current limits by accessing (then delete file):
```php
<?php phpinfo(); ?>
```

Or check with:
```bash
php -i | grep upload_max_filesize
```

## Workaround
Until PHP limits are increased, users should:
1. **Compress images** before uploading
2. Use tools like:
   - TinyPNG.com
   - Squoosh.app
   - ImageOptim (Mac)
   - RIOT (Windows)
3. Aim for **under 2MB per image**

Most vehicle photos can be compressed to under 2MB without noticeable quality loss.
