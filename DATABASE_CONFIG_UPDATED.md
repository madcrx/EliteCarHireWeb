# Database Configuration Updated

## New Database Credentials

Your database configuration has been updated with the following credentials:

- **Database Name:** `cp825575_elite_car_hire`
- **Username:** `cp825575_ECHadmin`
- **Password:** `Turbo2973!`
- **Host:** `localhost`
- **Charset:** `utf8mb4`

## File Updated

**File:** `config/database.php`

This file has been created with your new credentials and is **automatically excluded from Git** (listed in `.gitignore`), so your database password will never be committed to the repository.

## Security Notes

### ✅ What's Protected:
- `config/database.php` is in `.gitignore` - will not be committed
- Template file `config/database.example.php` contains no real credentials
- Database password is only stored locally on the server

### ⚠️ Important Security Practices:

1. **Never Commit Real Credentials**
   - The real `database.php` file should only exist on your server
   - Always use `database.example.php` as a template in the repository

2. **File Permissions**
   - Set restrictive permissions on the database config:
     ```bash
     chmod 600 config/database.php
     ```
   - This ensures only the file owner can read it

3. **Backup Securely**
   - If you back up your site, ensure backups are encrypted
   - Don't email database credentials in plain text
   - Use a password manager to store credentials

4. **Change Passwords Regularly**
   - Update database password periodically
   - Use strong passwords (12+ characters, mixed case, numbers, symbols)

## Deployment Instructions

### For Your Live Server:

1. **Upload the database.php file via FTP/SFTP:**
   - Source: `/config/database.php`
   - Destination: `/home/cp825575/EliteCarHireWeb/public/config/database.php`
   - Or if document root is correctly configured: `/home/cp825575/EliteCarHireWeb/config/database.php`

2. **Set File Permissions (via SSH or cPanel File Manager):**
   ```bash
   chmod 600 config/database.php
   ```

3. **Verify Connection:**
   - Visit your website: https://elitecarhire.au
   - If the site loads without database connection errors, configuration is correct
   - Check error logs if issues occur: `storage/logs/error.log`

### For Other Developers:

If other developers need to work on the project:

1. They should copy `config/database.example.php` to `config/database.php`
2. Update with their local database credentials
3. Never commit the real `database.php` file

## Testing Database Connection

### Quick Test:

Create a test file to verify the connection:

```php
<?php
// test-db-connection.php (delete after testing)

require __DIR__ . '/config/database.php';
$config = require __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connection successful!<br>";
    echo "Database: " . $config['database'] . "<br>";
    echo "Host: " . $config['host'] . "<br>";

    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Users table accessible. Found {$result['count']} users.<br>";

} catch (PDOException $e) {
    echo "❌ Database connection failed:<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
```

**Steps:**
1. Upload `test-db-connection.php` to your server root
2. Visit: `https://elitecarhire.au/test-db-connection.php`
3. Should see "✅ Database connection successful!"
4. **Delete the test file immediately after testing** (security risk)

## Common Database Connection Issues

### Issue: "Access denied for user"

**Causes:**
- Username or password incorrect
- User doesn't have access to the database
- User not created in cPanel

**Solution:**
1. Log into **cPanel → MySQL Databases**
2. Verify user exists: `cp825575_ECHadmin`
3. Verify user has privileges on database: `cp825575_elite_car_hire`
4. If user doesn't exist, create it and assign privileges

### Issue: "Unknown database"

**Causes:**
- Database name is incorrect
- Database doesn't exist
- Database not created in cPanel

**Solution:**
1. Log into **cPanel → MySQL Databases**
2. Check if database exists: `cp825575_elite_car_hire`
3. If not, create it using the SQL schema files:
   - `database/complete_schema.sql` or
   - `database/cpanel_schema.sql`

### Issue: "Can't connect to MySQL server"

**Causes:**
- Wrong host (should be `localhost` for most shared hosting)
- MySQL service is down
- Firewall blocking connection

**Solution:**
1. Verify host is `localhost` (not an IP address)
2. Contact hosting support if MySQL service is down
3. Check cPanel → MySQL Databases to ensure service is running

## Database Schema Status

### Required Tables:

Your database should contain these tables:
- `users` - User accounts (admin, owners, customers)
- `vehicles` - Vehicle listings
- `bookings` - Booking records
- `payments` - Payment transactions
- `reviews` - Vehicle reviews
- `settings` - System configuration
- `cms_pages` - Terms, FAQ, etc.
- `audit_log` - Activity logging
- And 15+ other tables...

### Check if Database Needs Importing:

If your database is empty or missing tables:

1. **Via cPanel phpMyAdmin:**
   - cPanel → phpMyAdmin
   - Select database: `cp825575_elite_car_hire`
   - Click "Import" tab
   - Choose file: `database/cpanel_schema.sql`
   - Click "Go"

2. **Via SSH (if available):**
   ```bash
   mysql -u cp825575_ECHadmin -p cp825575_elite_car_hire < database/cpanel_schema.sql
   ```
   Enter password: `Turbo2973!`

## Configuration Complete ✅

Your database configuration is now properly set up with:
- ✅ Correct credentials
- ✅ File created: `config/database.php`
- ✅ Excluded from Git (secure)
- ✅ Ready for deployment

**Next Steps:**
1. Upload `config/database.php` to your server
2. Fix document root (see DOCUMENT_ROOT_FIX.md)
3. Test website loads correctly
4. Verify admin login works

---

**Last Updated:** December 4, 2025
**Database:** cp825575_elite_car_hire
**User:** cp825575_ECHadmin
