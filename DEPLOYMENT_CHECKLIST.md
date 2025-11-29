# Deployment Checklist - Vehicle Image Upload Feature

## 📦 Files to Upload to Production Server

### **New Files Created** (Must Upload)
```
public/assets/images/hero-bg.svg
public/assets/images/limousine.svg
public/assets/images/luxury-sedan.svg
public/assets/images/luxury-suv.svg
public/assets/images/muscle-car.svg
public/assets/images/placeholder.svg
public/assets/images/sports-car.svg
public/assets/images/wedding-car.svg
public/assets/images/README.md
public/assets/images/DISPLAY-GUIDE.md
public/assets/images/assign-placeholder-images.php
storage/vehicles/.gitignore
PHP_UPLOAD_LIMITS.md
STORAGE_SYMLINK.md
```

### **Modified Files** (Must Upload - Overwrite Existing)
```
app/controllers/AdminController.php
app/views/admin/edit-vehicle.php
app/views/public/home.php
app/views/public/vehicles.php
app/views/public/vehicle-detail.php
config/app.php
public/.htaccess
public/assets/css/style.css
public/index.php
```

## 🔧 Post-Upload Configuration

### 1. Create Storage Symlink
After uploading files, SSH into your server and run:

```bash
cd /path/to/your/website/public
ln -sfn ../storage storage
```

Verify it worked:
```bash
ls -la public/storage
# Should show: storage -> /path/to/storage
```

### 2. Set Directory Permissions
```bash
chmod -R 755 storage
chmod -R 775 storage/vehicles
```

### 3. Increase PHP Upload Limits

**Option A: If .htaccess works** (already configured)
- The uploaded `.htaccess` file should automatically set limits to 10MB
- Test by uploading an image

**Option B: If .htaccess doesn't work** (common on shared hosting)
1. Go to cPanel → "MultiPHP INI Editor" or "Select PHP Version"
2. Set these values:
   - `upload_max_filesize`: 10M
   - `post_max_size`: 20M
   - `max_execution_time`: 300

**Option C: Using php.ini**
Create `public/php.ini` with:
```ini
upload_max_filesize = 10M
post_max_size = 20M
max_file_uploads = 20
```

### 4. Test the Installation

1. **Test Placeholder Images**: Visit `https://yoursite.com/vehicles`
   - Should see SVG placeholder images for vehicles without photos

2. **Test Admin Upload**:
   - Login to admin dashboard
   - Go to Vehicles → Edit any vehicle
   - Scroll to "Vehicle Images" section
   - Upload a test image (under 2MB initially)
   - Verify it appears in "Current Images" grid

3. **Test Image Display**:
   - After upload, visit the vehicle detail page
   - Confirm image displays correctly

## 📋 Quick Upload Checklist

- [ ] Upload all new SVG image files to `public/assets/images/`
- [ ] Upload modified PHP files (controllers, views)
- [ ] Upload updated `config/app.php`
- [ ] Upload updated `public/.htaccess`
- [ ] Upload updated `public/assets/css/style.css`
- [ ] Upload updated `public/index.php`
- [ ] Create storage symlink via SSH
- [ ] Set storage directory permissions (755/775)
- [ ] Configure PHP upload limits (via cPanel or php.ini)
- [ ] Test: Visit /vehicles page (see placeholders)
- [ ] Test: Upload vehicle image via admin
- [ ] Test: View vehicle detail page with uploaded image

## 🚨 If Symlink Doesn't Work

Some shared hosts disable symlinks. If you get "500 Internal Server Error":

1. Delete the symlink: `rm public/storage`
2. Edit `app/helpers.php`, line 192:
   ```php
   // Change from:
   $destination = __DIR__ . "/../storage/$directory/" . $filename;
   // To:
   $destination = __DIR__ . "/../public/uploads/$directory/" . $filename;

   // And line 195 from:
   return "storage/$directory/$filename";
   // To:
   return "uploads/$directory/$filename";
   ```
3. Create directory: `mkdir -p public/uploads/vehicles && chmod 775 public/uploads/vehicles`

## 📞 Support

If you encounter issues:
- Check PHP error logs in cPanel
- Verify file permissions (755 for directories, 644 for files)
- Confirm storage symlink exists: `ls -la public/storage`
- Test PHP limits: Create `test.php` with `<?php phpinfo(); ?>`

## ✅ Success Indicators

You'll know everything is working when:
- ✓ Vehicle listing page shows SVG placeholders
- ✓ Admin can upload images successfully
- ✓ Uploaded images appear in "Current Images" grid with primary badge
- ✓ Images display on public vehicle pages
- ✓ No 404 errors in browser console for image files
