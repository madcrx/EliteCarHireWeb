# Deployment Instructions for Latest Changes

## Recent Updates (3 commits)
1. Fixed admin sidebar navigation issues
2. Added missing admin routes and controller methods  
3. Added placeholder view files for new admin pages

## Deploy to Live Server

### Step 1: Pull Latest Changes
```bash
cd /path/to/EliteCarHireWeb
git fetch origin
git pull origin claude/add-website-images-01PjbLv42vZF21LMMfYKsBW1
```

### Step 2: Clear Server Cache
After pulling, clear PHP's opcode cache:

**If you have access to PHP CLI:**
```bash
# Clear OPcache via CLI
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared'; } else { echo 'OPcache not available'; }"
```

**Or restart your web server:**
```bash
# For Apache
sudo systemctl restart apache2
# OR
sudo systemctl restart httpd

# For PHP-FPM
sudo systemctl restart php-fpm
# OR
sudo systemctl restart php7.4-fpm  # adjust version as needed
```

### Step 3: Verify Files Were Updated
Check that the new files exist:
```bash
ls -la app/views/admin/email-settings.php
ls -la app/views/admin/analytics-revenue.php
```

### Step 4: Test the Pages
Visit these URLs to confirm they're working:
- http://ech.cyberlogicit.com.au/admin/email-settings
- http://ech.cyberlogicit.com.au/admin/analytics/revenue
- http://ech.cyberlogicit.com.au/admin/logs/payment

## Files Added in Latest Updates

### Routes (public/index.php)
- 32 new admin routes added

### Controller Methods (app/controllers/AdminController.php)  
- emailSettings(), emailQueue()
- analyticsRevenue(), analyticsBookings(), analyticsVehicles(), analyticsUsers()
- settingsPayment(), settingsEmail(), settingsCommission(), settingsBooking(), settingsNotifications()
- logsPayment(), logsEmail(), logsLogin()
- clearCache()

### View Files (app/views/admin/)
- email-settings.php, email-queue.php
- analytics-revenue.php, analytics-bookings.php, analytics-vehicles.php, analytics-users.php
- settings-payment.php, settings-email.php, settings-commission.php, settings-booking.php, settings-notifications.php
- logs-payment.php, logs-email.php, logs-login.php

## Troubleshooting

### Still Getting 404 Errors?
1. Verify you're on the correct branch
2. Check file permissions: `chmod 644 app/views/admin/*.php`
3. Clear browser cache (Ctrl+F5)
4. Check web server error logs

### Clear Cache Button Still Showing "An Error Occurred"?
1. Check browser console for JavaScript errors (F12)
2. Verify the route exists: `grep "clear-cache" public/index.php`
3. Check if request reaches server: `tail -f /var/log/apache2/access.log`
4. Ensure storage/cache directory exists: `mkdir -p storage/cache`
