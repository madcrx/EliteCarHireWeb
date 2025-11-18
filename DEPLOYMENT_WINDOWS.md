# Elite Car Hire - Windows Deployment Guide

## For Windows PC Users

This guide will help you deploy Elite Car Hire from your Windows PC to GitHub and then to your Dreamscape Networks cPanel hosting.

## Prerequisites

### Required Software
1. **Git for Windows** - Download from https://git-scm.com/download/win
2. **Text Editor** - Notepad++, VS Code, or Sublime Text
3. **FTP Client** - FileZilla (https://filezilla-project.org/)
4. **Web Browser** - Chrome, Firefox, or Edge

### You Will Need
- GitHub account
- Dreamscape Networks hosting credentials
- cPanel login details
- FTP access details

## Part 1: Uploading to GitHub

### Step 1: Install Git for Windows
1. Download Git from https://git-scm.com/download/win
2. Run the installer
3. Use recommended settings
4. Verify installation by opening Command Prompt and typing: `git --version`

### Step 2: Extract the Application
1. Extract `elite-car-hire.tar.gz` using 7-Zip or WinRAR
2. Place the `elite-car-hire` folder on your desktop or documents folder

### Step 3: Initialize Git Repository

Open **Git Bash** (installed with Git for Windows) and run:

```bash
cd C:/Users/YourUsername/Desktop/elite-car-hire
git init
git add .
git commit -m "Initial commit - Elite Car Hire application"
```

### Step 4: Create GitHub Repository
1. Go to https://github.com
2. Click "New repository"
3. Name it: `elite-car-hire`
4. Description: `Luxury vehicle hire management platform`
5. Choose **Private** (recommended for production code)
6. **DO NOT** initialize with README (we already have one)
7. Click "Create repository"

### Step 5: Push to GitHub

Copy the commands GitHub provides, or use these (replace YOUR-USERNAME):

```bash
git remote add origin https://github.com/YOUR-USERNAME/elite-car-hire.git
git branch -M main
git push -u origin main
```

You'll be prompted for your GitHub credentials.

### Step 6: Protect Sensitive Files

Create `.gitignore` file (already included) to protect:
- `.env` (database credentials)
- `storage/logs/` (log files)
- `storage/uploads/` (user uploads)

**IMPORTANT**: Never commit your `.env` file with real credentials to GitHub!

## Part 2: Deploying to Dreamscape Networks cPanel

### Method A: Using File Manager (Easier)

1. **Login to cPanel**
   - Go to: https://your-domain.com.au:2083
   - Or: https://server.dreamscapehosting.com.au:2083
   - Enter your cPanel username and password

2. **Navigate to File Manager**
   - Click "File Manager" icon
   - Navigate to `public_html` folder
   - If installing in subdirectory, create folder first

3. **Upload Files**
   - Click "Upload" button
   - Create ZIP file of elite-car-hire folder on Windows (right-click > Send to > Compressed folder)
   - Upload the ZIP file
   - Right-click the uploaded ZIP and select "Extract"
   - Move contents from `elite-car-hire` folder to `public_html` (or subdirectory)

4. **Set File Permissions**
   - Select `storage` folder
   - Click "Permissions"
   - Set to 755 for folders, 644 for files
   - Check "Recurse into subdirectories"
   - Set `storage/uploads` and `storage/logs` to 775

### Method B: Using FileZilla (FTP)

1. **Install FileZilla**
   - Download from https://filezilla-project.org/
   - Install with default settings

2. **Get FTP Credentials from cPanel**
   - Login to cPanel
   - Look for "FTP Accounts" icon
   - Or use main cPanel credentials

3. **Connect with FileZilla**
   ```
   Host: ftp.yourdomain.com.au
   Username: your-cpanel-username
   Password: your-cpanel-password
   Port: 21 (or 22 for SFTP)
   ```
   
4. **Upload Files**
   - Left panel: Navigate to extracted `elite-car-hire` folder on your PC
   - Right panel: Navigate to `/public_html/` on server
   - Select all files from left panel
   - Right-click > Upload
   - Wait for transfer to complete (may take 5-10 minutes)

5. **Set Permissions via FileZilla**
   - Right-click `storage` folder on server
   - Choose "File permissions"
   - Set to 755 and check "Recurse into subdirectories"

### Step 3: Configure Database

1. **Create Database in cPanel**
   - Login to cPanel
   - Find "MySQL Databases" icon
   - Create new database: `youruser_elitecar`
   - Create database user: `youruser_admin`
   - Set strong password (save it!)
   - Add user to database with ALL PRIVILEGES

2. **Import Database Schema**
   - Find "phpMyAdmin" in cPanel
   - Select your new database
   - Click "Import" tab
   - Choose file: `database/complete_schema.sql` from your PC
   - Click "Go"
   - Wait for success message

### Step 4: Configure Application

1. **Edit .env File**
   - In File Manager, navigate to root folder
   - Right-click `.env` file
   - Choose "Edit"
   - Update with your details:

   ```env
   DB_HOST=localhost
   DB_NAME=youruser_elitecar
   DB_USER=youruser_admin
   DB_PASS=your-database-password

   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com.au

   SMTP_HOST=mail.yourdomain.com.au
   SMTP_PORT=587
   SMTP_USER=info@yourdomain.com.au
   SMTP_PASS=your-email-password
   ```

   - Save changes

### Step 5: Configure Apache/PHP

1. **Create or Edit .htaccess**
   - Already included in package
   - Ensure mod_rewrite is enabled (usually is on Dreamscape)

2. **Check PHP Version**
   - In cPanel, find "Select PHP Version"
   - Ensure PHP 7.4 or higher is selected
   - Enable extensions: mysqli, pdo, pdo_mysql

### Step 6: Set Document Root (If using subdomain)

If you want site at subdomain (e.g., bookings.yourdomain.com.au):

1. In cPanel, find "Subdomains"
2. Create subdomain: `bookings`
3. Set Document Root to: `/public_html/elite-car-hire/public`

For main domain:
1. In cPanel, find "Domains"
2. Edit domain
3. Set Document Root to: `/public_html/elite-car-hire/public`

## Part 3: Post-Deployment Setup

### Step 1: Test the Installation

1. Visit: https://yourdomain.com.au
2. You should see the Elite Car Hire homepage

### Step 2: First Login

1. Go to: https://yourdomain.com.au/login
2. Login with:
   - Email: `admin@elitecarhire.au`
   - Password: `Admin123!`

### Step 3: CRITICAL - Change Admin Password

1. Immediately change the default password
2. Go to Admin Dashboard > Settings
3. Update admin email to your actual email

### Step 4: Configure Settings

1. Navigate to Admin > Settings
2. Update:
   - Site URL
   - Company email
   - Commission rates
   - Auto-approval settings

### Step 5: Update CMS Content

1. Go to Admin > Content Management
2. Edit pages as needed
3. Ensure all contact details are correct

## Part 4: Email Configuration (Dreamscape)

### Option A: Use Dreamscape SMTP

1. In cPanel, create email account: info@yourdomain.com.au
2. Update .env file:
   ```env
   SMTP_HOST=mail.yourdomain.com.au
   SMTP_PORT=587
   SMTP_USER=info@yourdomain.com.au
   SMTP_PASS=your-email-password
   ```

### Option B: Use Gmail SMTP

1. Create Gmail account for business
2. Enable "Less secure app access" or use App Password
3. Update .env:
   ```env
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=your-email@gmail.com
   SMTP_PASS=your-app-password
   ```

## Part 5: SSL Certificate (HTTPS)

### Using Let's Encrypt (Free) via cPanel

1. In cPanel, find "SSL/TLS Status"
2. Select your domain
3. Click "Run AutoSSL"
4. Wait for certificate installation
5. Update .env: `APP_URL=https://yourdomain.com.au`

## Troubleshooting

### Common Issues on Dreamscape/cPanel

**1. 500 Internal Server Error**
- Check file permissions (folders: 755, files: 644)
- Check .htaccess syntax
- Review error logs in cPanel

**2. Database Connection Failed**
- Verify database credentials in .env
- Ensure database user has privileges
- Check database name includes cPanel prefix

**3. White Screen / Blank Page**
- Temporarily set `APP_DEBUG=true` in .env
- Check PHP error logs in cPanel
- Verify PHP version is 7.4+

**4. File Upload Errors**
- Set storage/uploads to 775 permissions
- Check PHP upload limits in cPanel
- Increase memory_limit if needed

**5. Email Not Sending**
- Verify SMTP credentials
- Check cPanel email logs
- Test with different SMTP settings

**6. URL Rewriting Not Working**
- Verify mod_rewrite is enabled
- Check .htaccess is uploaded
- Ensure AllowOverride is set in Apache config

### Dreamscape-Specific Notes

- **Control Panel**: https://your-domain.com.au:2083
- **FTP Host**: Usually ftp.your-domain.com.au
- **PHP Version**: Selectable via cPanel
- **Default Path**: /public_html/
- **Database Prefix**: Your cPanel username + underscore

## Security Checklist

- [ ] Changed default admin password
- [ ] Updated .env with production settings
- [ ] Set APP_DEBUG=false
- [ ] Installed SSL certificate
- [ ] Set proper file permissions
- [ ] Updated admin email address
- [ ] Tested all functionality
- [ ] Set up regular backups
- [ ] Reviewed audit logs

## Backup Procedures

### Automatic Backups via cPanel

1. In cPanel, find "Backup"
2. Enable "Full Backup"
3. Set schedule (daily/weekly)
4. Configure backup destination

### Manual Backups

1. **Database**: Use phpMyAdmin > Export
2. **Files**: Use File Manager > Compress > Download
3. Store locally on your Windows PC

## Maintenance

### Regular Tasks

**Weekly:**
- Review error logs in cPanel
- Check security alerts in admin panel
- Monitor disk space usage

**Monthly:**
- Full backup of database and files
- Review user accounts
- Update any necessary settings

## Windows-Specific Tips

### Using PowerShell (Alternative to Git Bash)

Open PowerShell and navigate:
```powershell
cd C:\Users\YourUsername\Desktop\elite-car-hire
```

### Creating ZIP from Windows

Right-click folder > Send to > Compressed (zipped) folder

### Editing Files on Windows

Use Notepad++ for proper line endings (LF not CRLF)

## Support Resources

**Dreamscape Support:**
- Phone: 1300 324 336
- Email: support@dreamscape.com.au
- Knowledge Base: https://help.dreamscape.com.au/

**cPanel Documentation:**
- https://docs.cpanel.net/

**Elite Car Hire Documentation:**
- README.md (in package)
- INSTALLATION.md (detailed guide)

## Quick Command Reference

### Git Commands (Windows)
```bash
git status                  # Check status
git add .                   # Add all files
git commit -m "message"     # Commit changes
git push                    # Push to GitHub
git pull                    # Pull from GitHub
```

### FTP via Windows Command Line
```cmd
ftp ftp.yourdomain.com.au
```

## Next Steps After Deployment

1. Test all features thoroughly
2. Create test bookings
3. Verify email notifications
4. Check payment processing
5. Add real vehicle listings
6. Invite owners to register
7. Configure any custom settings
8. Set up Google Analytics (optional)

---

**Version**: 1.0.0  
**Platform**: Windows to cPanel  
**Hosting**: Dreamscape Networks  
**Last Updated**: November 2025

© 2025 Elite Car Hire. All rights reserved.
