# Elite Car Hire - Installation & Setup Guide

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3+
- Apache 2.4+ with mod_rewrite enabled
- Minimum 512MB RAM
- 1GB disk space

## Installation Steps

### 1. Upload Files

Upload all files to your web server. The recommended structure is:

```
/public_html/
  └── elite-car-hire/
      ├── public/          # Document root - point Apache here
      ├── app/
      ├── config/
      ├── database/
      ├── assets/
      └── storage/
```

### 2. Configure Apache

#### Option A: Point Document Root to /public

Configure your Apache VirtualHost to point to the `public` directory:

```apache
<VirtualHost *:80>
    ServerName elitecarhire.local
    DocumentRoot /path/to/elite-car-hire/public
    
    <Directory /path/to/elite-car-hire/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Option B: Use Subdirectory

If installing in a subdirectory, ensure the `.htaccess` file in the root redirects to `/public`.

### 3. Set File Permissions

```bash
cd /path/to/elite-car-hire

# Set storage directories to writable
chmod -R 775 storage/
chmod -R 775 storage/logs/
chmod -R 775 storage/uploads/

# Set owner to web server user (adjust as needed)
chown -R www-data:www-data storage/
```

### 4. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE elite_car_hire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'elite_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON elite_car_hire.* TO 'elite_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Import Database Schema

```bash
mysql -u elite_user -p elite_car_hire < database/complete_schema.sql
```

This will create all tables and insert:
- Default admin user (admin@elitecarhire.au / Admin123!)
- System settings
- CMS pages with Terms of Service and Privacy Policy

### 6. Configure Environment

Edit `.env` file in the root directory:

```env
DB_HOST=localhost
DB_NAME=elite_car_hire
DB_USER=elite_user
DB_PASS=your_secure_password

APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

SMTP_HOST=localhost
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
```

**IMPORTANT:** Set `APP_DEBUG=false` in production!

### 7. Security Configuration

#### Change Default Admin Password

1. Login as admin (admin@elitecarhire.au / Admin123!)
2. Change password immediately via profile settings

#### Generate New Password Hash

To generate a new password hash for direct database update:

```php
<?php
echo password_hash('YourNewPassword', PASSWORD_DEFAULT);
?>
```

Then update the database:

```sql
UPDATE users SET password = 'new_hash_here' WHERE id = 1;
```

### 8. File Upload Configuration

Verify PHP upload limits in `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### 9. Email Configuration

For production email sending, configure SMTP settings in `.env`:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
```

### 10. SSL Certificate (Recommended)

Install an SSL certificate for HTTPS:

```bash
# Using Let's Encrypt (Certbot)
sudo certbot --apache -d yourdomain.com
```

## Default Login Credentials

**Admin Account:**
- Email: admin@elitecarhire.au
- Password: Admin123!

**IMPORTANT:** Change this password immediately after first login!

## Post-Installation Configuration

### 1. Admin Dashboard

Login and configure:

- Site settings (Settings menu)
- Upload company logo
- Customize CMS pages (Content Management)
- Set commission rates
- Configure auto-approval settings

### 2. Email Templates

Configure email notification templates for:
- Booking confirmations
- Payment receipts
- User approvals
- Payout notifications

### 3. Test Bookings

1. Create a test Owner account
2. Add a test vehicle
3. Approve vehicle as Admin
4. Create a Customer account
5. Make a test booking
6. Process test payment
7. Verify email notifications

## Directory Structure

```
elite-car-hire/
├── public/                    # Web root (publicly accessible)
│   ├── index.php             # Application entry point
│   ├── .htaccess             # Apache configuration
│   └── assets/               # Symlinked to /assets
├── app/
│   ├── controllers/          # Application controllers
│   ├── models/               # Database models
│   ├── views/                # View templates
│   ├── middleware/           # Middleware classes
│   ├── Database.php          # Database connection
│   ├── Router.php            # URL routing
│   └── helpers.php           # Helper functions
├── config/
│   ├── app.php               # Application configuration
│   └── database.php          # Database configuration
├── database/
│   └── complete_schema.sql   # Database schema
├── assets/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── images/               # Static images
├── storage/
│   ├── logs/                 # Application logs
│   └── uploads/              # User uploads
│       └── vehicles/         # Vehicle images
└── .env                      # Environment variables
```

## Troubleshooting

### Common Issues

**1. 404 Errors / Routing Not Working**
- Verify mod_rewrite is enabled: `sudo a2enmod rewrite`
- Check .htaccess files are being read
- Verify AllowOverride All in Apache config
- Restart Apache: `sudo service apache2 restart`

**2. Database Connection Failed**
- Check credentials in .env
- Verify MySQL is running
- Test connection: `mysql -u elite_user -p`
- Check firewall settings

**3. Permission Denied Errors**
- Verify storage/ directories are writable
- Check file ownership: `ls -la storage/`
- Adjust permissions: `chmod -R 775 storage/`

**4. Blank Page / White Screen**
- Enable error display temporarily:
  - Set `APP_DEBUG=true` in .env
  - Check `storage/logs/error.log`
- Verify PHP error_log location

**5. Images Not Uploading**
- Check PHP upload_max_filesize
- Verify storage/uploads/ is writable
- Check disk space: `df -h`

## Security Checklist

- [ ] Changed default admin password
- [ ] Set APP_DEBUG=false in production
- [ ] Configured HTTPS/SSL
- [ ] Restricted database user privileges
- [ ] Set strong database password
- [ ] Protected .env file (not web-accessible)
- [ ] Enabled security headers in .htaccess
- [ ] Regular backups configured
- [ ] Updated PHP to latest version
- [ ] Reviewed audit logs regularly

## Backup Procedures

### Database Backup

```bash
# Daily backup
mysqldump -u elite_user -p elite_car_hire > backup_$(date +%Y%m%d).sql

# Automated backup (add to crontab)
0 2 * * * mysqldump -u elite_user -p'password' elite_car_hire > /backups/db_$(date +\%Y\%m\%d).sql
```

### File Backup

```bash
# Backup uploads directory
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz storage/uploads/
```

## Performance Optimization

### Enable OPcache

In php.ini:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Database Optimization

```sql
-- Run monthly
OPTIMIZE TABLE bookings, users, vehicles, payments;

-- Add indexes if needed
CREATE INDEX idx_booking_date ON bookings(booking_date);
```

### Caching

Consider implementing:
- Browser caching (set in .htaccess)
- Database query caching
- Static file compression (gzip)

## Maintenance

### Regular Tasks

**Weekly:**
- Review security alerts
- Check error logs
- Monitor disk space

**Monthly:**
- Database optimization
- Backup verification
- Security updates

**Quarterly:**
- Review audit logs
- Update documentation
- Performance review

## Support

For technical support or questions:
- Email: support@elitecarhire.au
- Phone: 1300 324 473
- Documentation: See README.md

## Version Information

- **Version:** 1.0.0
- **Release Date:** November 2025
- **PHP Version:** 7.4+
- **Database:** MySQL 5.7+

## License

Proprietary software for Elite Car Hire.
All rights reserved © 2025 Elite Car Hire.
