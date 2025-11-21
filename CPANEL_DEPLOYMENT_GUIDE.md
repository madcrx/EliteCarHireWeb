# Elite Car Hire - cPanel Deployment Guide (Windows PC)

This guide provides step-by-step instructions for deploying Elite Car Hire to a web hosting service using cPanel from a Windows PC.

## Prerequisites

- Windows PC with internet connection
- cPanel hosting account
- FTP client (FileZilla recommended) or cPanel File Manager access
- MySQL database access through cPanel
- This application package

## Step 1: Prepare Your Files

1. **Download FileZilla** (if not installed):
   - Visit: https://filezilla-project.org/download.php?type=client
   - Download and install FileZilla Client for Windows

2. **Extract the application files** to a folder on your Windows PC

## Step 2: Access cPanel

1. Open your web browser (Chrome, Firefox, or Edge)
2. Navigate to your cPanel URL (usually: `yourdomain.com/cpanel` or `yourdomain.com:2083`)
3. Log in with your cPanel username and password

## Step 3: Create MySQL Database

1. In cPanel, locate and click **MySQL® Databases**
2. Under "Create New Database":
   - Enter database name: `elite_car_hire`
   - Click **Create Database**
3. Under "Add New User":
   - Enter username (e.g., `eliteadmin`)
   - Generate a strong password (click **Generate Password** button)
   - **IMPORTANT**: Copy and save the password securely
   - Click **Create User**
4. Under "Add User To Database":
   - Select the user you just created
   - Select the database you just created
   - Click **Add**
   - Check **ALL PRIVILEGES**
   - Click **Make Changes**

## Step 4: Import Database Schema

**IMPORTANT**: Make sure your database is created in Step 3 before proceeding.

1. In cPanel, click **phpMyAdmin**
2. **CRITICAL**: In the left sidebar, click on your database name (e.g., `yourusername_elite_car_hire`)
   - The database name should be highlighted/selected
   - You should see "No tables found in database" in the main panel
3. Click the **Import** tab at the top of the page
4. Click **Choose File** button
5. Navigate to your application folder and select:
   - **File**: `database/cpanel_schema.sql`
   - **⚠️ IMPORTANT**: Use `cpanel_schema.sql` NOT `complete_schema.sql`
   - The cpanel version is specifically designed for shared hosting
6. Leave all other settings at default
7. Scroll down and click the **Go** button at the bottom
8. Wait for the success message: "Import has been successfully finished, XX queries executed"
9. Verify: You should now see 20+ tables in the left sidebar (users, vehicles, bookings, etc.)

## Step 5: Configure Database Connection

**IMPORTANT**: This step must be completed BEFORE uploading files.

1. On your Windows PC, open the application folder
2. Navigate to the `config` folder
3. Find the file `database.example.php`
4. **Copy** this file and rename the copy to `database.php`
   - You should now have both files: `database.example.php` and `database.php`
5. Open `database.php` with Notepad or Notepad++
6. Update the following values with YOUR database details from Step 3:
   ```php
   return [
       'host' => 'localhost',
       'database' => 'yourusername_elite_car_hire',  // Replace with YOUR database name
       'username' => 'yourusername_eliteadmin',      // Replace with YOUR database user
       'password' => 'YOUR_DATABASE_PASSWORD',       // Replace with YOUR password from Step 3
       'charset' => 'utf8mb4',
   ];
   ```

   **Example with actual values**:
   ```php
   return [
       'host' => 'localhost',
       'database' => 'cyberlog_elite_car_hire',
       'username' => 'cyberlog_ECHadmin',
       'password' => 'MySecurePass123!',
       'charset' => 'utf8mb4',
   ];
   ```
7. **Save the file** - Make sure you save as `database.php`
8. **Verify**: The `config` folder should now contain:
   - `app.php` (don't modify)
   - `database.example.php` (template - don't modify)
   - `database.php` (your configured file - upload this)

## Step 6: Upload Files via FileZilla

### Method A: Using FileZilla (Recommended)

1. **Open FileZilla**
2. **Get FTP credentials from cPanel**:
   - In cPanel, go to **FTP Accounts**
   - Use your main cPanel account or create a new FTP account
   - Note: Host, Username, Password, and Port
3. **Connect to your server**:
   - Host: `ftp.yourdomain.com` or your server IP
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: `21`
   - Click **Quickconnect**
4. **Navigate to public_html** (or your domain's root folder) in the right panel
5. **Upload application files**:
   - In the left panel (Local site), navigate to your application folder
   - Select ALL folders and files
   - Right-click and select **Upload**
   - Wait for upload to complete (may take 5-15 minutes depending on connection speed)

### Method B: Using cPanel File Manager (Alternative)

1. In cPanel, click **File Manager**
2. Navigate to `public_html`
3. Click **Upload** button
4. Select and upload all application files
5. Once uploaded, right-click the ZIP file (if you uploaded a ZIP)
6. Select **Extract**

## Step 7: Set Correct Permissions

1. In **cPanel File Manager**, navigate to `public_html`
2. Right-click on `storage` folder → **Change Permissions**
   - Set to: `755`
   - Check **Recurse into subdirectories**
   - Click **Change Permissions**
3. Right-click on `storage/logs` folder → **Change Permissions**
   - Set to: `755`
4. Right-click on `storage/uploads` folder → **Change Permissions**
   - Set to: `755`

## Step 8: Configure .htaccess (Important for Routing)

1. In File Manager, check if `.htaccess` exists in `public` folder
2. If not, create it: Click **+ File** → Name: `.htaccess`
3. Right-click → **Edit**
4. Add the following content:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /

       # Redirect all requests to index.php
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule ^(.*)$ index.php [QSA,L]
   </IfModule>
   ```
5. Click **Save Changes**

## Step 9: Update Document Root (Critical)

**IMPORTANT**: Your web server must point to the `/public` directory, NOT the root.

1. In cPanel, go to **Domains** or **Addon Domains**
2. Find your domain
3. Click **Manage**
4. Change **Document Root** from `/public_html` to `/public_html/public`
5. Click **Change**

## Step 10: Create Admin Account (First-Time Setup)

1. Open **phpMyAdmin** in cPanel
2. Select your database
3. Click **SQL** tab
4. Run this query to create an admin account:
   ```sql
   INSERT INTO users (email, password, first_name, last_name, role, status, created_at, updated_at)
   VALUES (
       'admin@elitecarhire.au',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       'Admin',
       'User',
       'admin',
       'active',
       NOW(),
       NOW()
   );
   ```
   Default password: `password` (Change immediately after first login!)

## Step 11: Test Your Installation

1. Open your web browser
2. Navigate to: `https://yourdomain.com`
3. You should see the Elite Car Hire homepage
4. Click **Login** and use:
   - Email: `admin@elitecarhire.au`
   - Password: `password`
5. **Immediately change your password** in the admin panel

## Step 12: Configure Email Settings (Optional but Recommended)

1. In cPanel, create an email account: `support@yourdomain.com`
2. On your Windows PC, edit `config/app.php`
3. Update email configuration:
   ```php
   'email' => [
       'from_address' => 'support@yourdomain.com',
       'from_name' => 'Elite Car Hire',
       'smtp_host' => 'mail.yourdomain.com',
       'smtp_port' => 587,
       'smtp_username' => 'support@yourdomain.com',
       'smtp_password' => 'your-email-password',
       'smtp_encryption' => 'tls',
   ],
   ```
4. Re-upload `config/app.php` via FileZilla

## Step 13: Add Content via CMS

1. Log in as admin
2. Navigate to **Admin Dashboard** → **CMS**
3. Add content for:
   - About Us page
   - Services page
   - Support page
   - Terms of Service
   - Privacy Policy
   - FAQ

## Troubleshooting

### Database Import Error: "Access denied to database"
**Error**: `#1044 - Access denied for user 'xxx' to database 'elite_car_hire'`

**Solution**:
1. Make sure you're using `cpanel_schema.sql` NOT `complete_schema.sql`
2. Ensure you selected your database in the left sidebar before clicking Import
3. The database must already exist (created in Step 3)
4. Verify your database user has ALL PRIVILEGES on the database

**If you already tried importing `complete_schema.sql`**:
1. In phpMyAdmin, select your database from the left sidebar
2. Click on all tables (if any were created) and drop them
3. Go back to Import tab
4. This time select `database/cpanel_schema.sql`
5. Click Go

### "500 Internal Server Error"
- Check `.htaccess` file is correctly configured
- Verify folder permissions (755 for folders, 644 for files)
- Check error logs in cPanel → **Error Log**

### "Database Connection Failed"
**Common causes**:
1. **Missing database.php file**
   - Check if `config/database.php` exists
   - If not, copy `config/database.example.php` to `config/database.php`
   - Update with your actual database credentials
2. **Wrong credentials**
   - Verify database credentials in `config/database.php` match Step 3
   - Ensure database user has ALL PRIVILEGES
   - Check that cPanel prefix is included in database name and username
3. **Database not selected in phpMyAdmin**
   - Make sure you imported the schema into the correct database

### White/Blank Screen
- Enable error display temporarily: Edit `public/index.php`
- Change `ini_set('display_errors', 0);` to `ini_set('display_errors', 1);`
- Refresh page to see error messages
- Fix the error, then change back to `0`

### Images Not Loading
- Check `storage/uploads` folder exists and has 755 permissions
- Verify image paths in database are correct

### Login Not Working
- Clear browser cache and cookies
- Ensure sessions are enabled on your hosting
- Check `storage/logs/error.log` for errors

## Security Recommendations

1. **Change default admin password immediately**
2. **Delete or restrict access to** `database/complete_schema.sql` after import
3. **Set strong passwords** for all accounts
4. **Enable SSL/HTTPS** in cPanel (Let's Encrypt is free)
5. **Keep backups**: Use cPanel Backup feature regularly
6. **Monitor error logs** regularly in cPanel

## Contact Support

For technical support:
- Email: support@elitecarhire.au
- Phone: 0406 907 849

## Version Information

- Application: Elite Car Hire v1.0
- PHP Required: 7.4 or higher
- MySQL Required: 5.7 or higher
- Last Updated: 2025

---

**Note**: This guide assumes standard cPanel hosting. Interface may vary slightly depending on your hosting provider.
