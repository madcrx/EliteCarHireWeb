# Elite Car Hire - FTP Deployment Guide for Windows

This guide provides step-by-step instructions for deploying the email notification system updates to your live server using Core FTP on Windows.

## 📋 Overview

This deployment adds comprehensive email notifications for:
- **Booking Creation** - Customer, owner, and admin notifications
- **Booking Confirmation** - Customer payment notifications
- **Booking Cancellation** - Refund processing and notifications
- **Vehicle Approval/Rejection** - Owner notifications with guidance
- **Contact Form Submissions** - Admin and customer notifications
- **Payment Processing** - Enhanced payment confirmations with action buttons

## 🗂️ Files to Upload

### **Required Files** (Upload these to your live server)

#### 1. Configuration Files
```
config/app.php
```
**Purpose:** Adds role-specific email addresses (bookings_confirmations@, payment_confirmations@, etc.)

#### 2. Helper Functions
```
app/helpers.php
```
**Purpose:** Adds token generation, email button helpers, and action URL generators

#### 3. Controller Files
```
app/controllers/AdminController.php
app/controllers/BookingController.php
app/controllers/OwnerController.php
app/controllers/PaymentController.php
app/controllers/PublicController.php
```
**Purpose:** Email notification logic for all major actions

#### 4. Routing File
```
public/index.php
```
**Purpose:** New routes for token-based actions and vehicle rejection

#### 5. Database Migration
```
database/migrations/create_action_tokens_table.sql
```
**Purpose:** Creates the `action_tokens` table for one-click email actions

---

## 🔧 Step-by-Step Deployment Using Core FTP

### Step 1: Download Files from GitHub

1. Open your web browser and go to your GitHub repository
2. Click the green **"Code"** button
3. Select **"Download ZIP"**
4. Extract the ZIP file to a folder on your Windows PC (e.g., `C:\EliteCarHire`)

### Step 2: Connect to Your Server with Core FTP

1. **Open Core FTP**
2. Click **"Site Manager"** (globe icon) or press `F4`
3. Create a new site or select your existing Elite Car Hire connection
4. Enter your FTP credentials:
   - **Host/IP/URL:** Your server address (e.g., `ftp.elitecarhire.au`)
   - **Username:** Your FTP username
   - **Password:** Your FTP password
   - **Port:** Usually `21` (or `22` for SFTP)
5. Click **"Connect"**

### Step 3: Navigate to Your Website Root

In the **Remote** pane (right side):
1. Navigate to your website root directory
   - Usually `/public_html/` or `/ech.cyberlogicit.com.au/`
   - You should see folders like `app/`, `config/`, `public/`, etc.

### Step 4: Upload Configuration File

**Local (Left Pane):** Navigate to `C:\EliteCarHire\config\`
**Remote (Right Pane):** Navigate to `/public_html/config/`

1. Select `app.php` in the left pane
2. Right-click and choose **"Upload"** (or drag and drop to the right pane)
3. If prompted about overwriting, click **"Yes"** or **"Overwrite"**

### Step 5: Upload Helper Functions

**Local:** Navigate to `C:\EliteCarHire\app\`
**Remote:** Navigate to `/public_html/app/`

1. Select `helpers.php`
2. Upload (overwrite existing file)

### Step 6: Upload Controller Files

**Local:** Navigate to `C:\EliteCarHire\app\controllers\`
**Remote:** Navigate to `/public_html/app/controllers/`

Upload the following files (select all 5 at once with Ctrl+Click):
- `AdminController.php`
- `BookingController.php`
- `OwnerController.php`
- `PaymentController.php`
- `PublicController.php`

Right-click and choose **"Upload"**, then **"Overwrite All"**

### Step 7: Upload Routing File

**Local:** Navigate to `C:\EliteCarHire\public\`
**Remote:** Navigate to `/public_html/public/`

1. Select `index.php`
2. Upload (overwrite existing file)

### Step 8: Upload Database Migration File

**Local:** Navigate to `C:\EliteCarHire\database\migrations\`
**Remote:** Navigate to `/public_html/database/migrations/`

1. Create the `migrations` folder if it doesn't exist:
   - Right-click in the remote pane
   - Select **"Make Dir"**
   - Enter `migrations` and click OK
2. Select `create_action_tokens_table.sql`
3. Upload to the migrations folder

---

## 💾 Database Migration

### Option A: Using cPanel phpMyAdmin (Recommended)

1. **Login to cPanel**
   - Go to `https://yourdomain.com/cpanel`
   - Enter your cPanel credentials

2. **Open phpMyAdmin**
   - Find **"Databases"** section
   - Click **"phpMyAdmin"**

3. **Select Your Database**
   - Click on your database name (usually `cyberlog_elitecarhire` or similar)

4. **Run the Migration**
   - Click the **"SQL"** tab at the top
   - Open the file `create_action_tokens_table.sql` in Notepad
   - Copy all the SQL code
   - Paste it into the SQL query box
   - Click **"Go"**

5. **Verify Success**
   - You should see a green checkmark and "MySQL returned an empty result set"
   - Click **"Structure"** tab
   - Scroll down and verify the `action_tokens` table exists

### Option B: Using MySQL Command Line

If you have SSH access:

```bash
ssh your-username@your-server.com
cd /public_html/database/migrations/
mysql -u your-db-user -p your-db-name < create_action_tokens_table.sql
```

---

## ⚙️ Environment Configuration

### Configure Email Addresses

1. **Login to cPanel**
2. Go to **"Email Accounts"**
3. **Create the following email addresses** if they don't exist:
   - `bookings_confirmations@elitecarhire.au`
   - `payment_confirmations@elitecarhire.au`
   - `cancellations@elitecarhire.au`
   - `disputes@elitecarhire.au`
   - `inquiries@elitecarhire.au`
   - `vehicles@elitecarhire.au`
   - `support@elitecarhire.au`
   - `admin@elitecarhire.au`
   - `noreply@elitecarhire.au`

4. **(Optional) Set Up Email Forwarding**
   - You can forward all these addresses to your main admin email
   - Go to **"Forwarders"** in cPanel
   - Create forwarders for each address to forward to your main email

### Verify SMTP Settings (Optional)

If you want to use external SMTP (like Gmail, SendGrid, etc.):

1. Open the uploaded `config/app.php` file via FTP
2. Scroll to the `'email'` section
3. Update these settings if needed:
   ```php
   'smtp_host' => 'your-smtp-host.com',
   'smtp_port' => 587,
   'smtp_username' => 'your-username',
   'smtp_password' => 'your-password',
   'smtp_encryption' => 'tls',
   ```

---

## ✅ Post-Deployment Testing

### Test 1: Contact Form Submission

1. Go to `https://elitecarhire.au/contact`
2. Fill out the contact form and submit
3. Check that:
   - Customer receives auto-reply email
   - Admin receives notification at `inquiries@elitecarhire.au`

### Test 2: Booking Creation

1. Login as a customer
2. Create a new booking
3. Check that:
   - Customer receives "Booking Created" email
   - Owner receives "New Booking Request" email with confirmation button
   - Admin receives notification at `bookings_confirmations@elitecarhire.au`

### Test 3: Booking Confirmation

1. Login as the vehicle owner
2. Confirm the pending booking (or click the email link)
3. Check that:
   - Customer receives "Booking Confirmed" email with payment button

### Test 4: Payment Processing

1. Login as customer
2. Complete payment for a confirmed booking
3. Check that:
   - Customer receives payment confirmation with "View Bookings" button
   - Owner receives payment notification with "View Payouts" button
   - Admin receives notification at `payment_confirmations@elitecarhire.au`

### Test 5: Vehicle Approval

1. Login as admin
2. Go to Vehicles management
3. Approve a pending vehicle
4. Check that:
   - Owner receives approval email
   - Admin receives copy at `vehicles@elitecarhire.au`

### Test 6: Token-Based Actions

1. Check owner's email for "New Booking Request"
2. Click the **"Confirm Booking"** button in the email
3. Verify:
   - You're logged in (or redirected to login)
   - Booking is confirmed automatically
   - Success message appears
   - Customer receives confirmation email

---

## 🔍 Troubleshooting

### Emails Not Sending

**Problem:** No emails are being sent

**Solutions:**
1. Check cPanel error logs:
   - cPanel → **"Errors"** → View error log
   - Look for email-related errors
2. Verify SMTP settings in `config/app.php`
3. Check that `sendmail` is enabled on your server
4. Test with a simple PHP mail script:
   ```php
   <?php
   mail('your@email.com', 'Test', 'Testing email functionality');
   ?>
   ```

### 500 Internal Server Error

**Problem:** Pages showing 500 errors

**Solutions:**
1. Check file permissions:
   - Files should be `644`
   - Folders should be `755`
   - Right-click in Core FTP → **"CHMOD"** → Set permissions
2. Check error logs in cPanel
3. Verify all files uploaded correctly (check file sizes match)

### Token Links Not Working

**Problem:** Email action buttons show "Invalid token" error

**Solutions:**
1. Verify the `action_tokens` table was created:
   - phpMyAdmin → Check database structure
2. Check that `app/helpers.php` uploaded correctly
3. Verify the route exists in `public/index.php`:
   - Should have `$router->get('/owner/bookings/confirm-action', 'OwnerController@confirmBookingAction');`

### Wrong Email Addresses

**Problem:** Emails going to wrong addresses

**Solution:**
1. Edit `config/app.php` via FTP
2. Update the email addresses in the `'email'` array
3. Save and re-upload

---

## 📁 File Checksums (For Verification)

After uploading, you can verify files uploaded correctly by checking their sizes:

| File | Approximate Size |
|------|-----------------|
| `config/app.php` | ~4 KB |
| `app/helpers.php` | ~15 KB |
| `app/controllers/AdminController.php` | ~85 KB |
| `app/controllers/BookingController.php` | ~25 KB |
| `app/controllers/OwnerController.php` | ~48 KB |
| `app/controllers/PaymentController.php` | ~22 KB |
| `app/controllers/PublicController.php` | ~12 KB |
| `public/index.php` | ~12 KB |
| `database/migrations/create_action_tokens_table.sql` | ~1 KB |

If sizes don't match, re-upload that file.

---

## 📞 Support

If you encounter issues during deployment:

1. **Check Error Logs**
   - cPanel → Errors → Error Log
   - Look for PHP errors or warnings

2. **Contact Your Hosting Provider**
   - Verify PHP version (should be 7.4+)
   - Verify mail() function is enabled
   - Check file permission requirements

3. **GitHub Issues**
   - Report issues at your repository's Issues page
   - Include error messages and error log excerpts

---

## 🎉 Deployment Complete!

Once all tests pass, your email notification system is fully deployed and operational.

**What's been added:**
- ✅ Booking creation notifications (3 emails)
- ✅ Booking confirmation notifications (1 email)
- ✅ Payment processing notifications (2 emails)
- ✅ Contact form notifications (2 emails)
- ✅ Booking cancellation notifications (3 emails with refund logic)
- ✅ Vehicle approval/rejection notifications (2 emails per action)
- ✅ One-click email action buttons (secure token-based)
- ✅ Role-specific email addresses (8 addresses)

**Total:** 15+ email templates with professional HTML design and action buttons!

---

*Last Updated: November 26, 2025*
