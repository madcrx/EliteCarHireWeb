# Images Not Showing - Diagnosis and Fix Guide

## Problem Identified

Your website loads but images aren't displaying. This is due to:

1. ✅ **Website working** - Login successful
2. ❌ **Missing images** - No logo or vehicle photos uploaded
3. ❌ **Empty upload directory** - `storage/uploads/` is empty
4. ⚠️ **Possible document root issue** - Assets may not be accessible

---

## Quick Diagnosis

### What I Found:

**Public Assets Directory:**
```
public/assets/
├── css/          ✅ Exists
├── js/           ✅ Exists
└── images/       ⚠️ Only contains placeholder.txt (no actual logo)
```

**Storage Uploads Directory:**
```
storage/uploads/  ⚠️ Empty (only .gitkeep file)
```

**Vehicle Images:**
- Stored in database table: `vehicle_images`
- Files should be in: `storage/uploads/vehicles/`
- Currently: **No images uploaded**

---

## Root Cause Analysis

### Issue 1: Document Root Still Wrong (CRITICAL)

If you're still seeing directory listings OR if CSS isn't loading properly, your document root is **still misconfigured**.

**Test this now:**
1. Open https://elitecarhire.au in browser
2. Open Developer Tools (F12)
3. Go to "Network" tab
4. Refresh the page
5. Look for `/assets/css/style.css` or `/assets/js/app.min.js`

**If you see 404 errors on assets:**
- Document root is wrong
- Follow: `DOCUMENT_ROOT_FIX.md`

**If assets load fine (200 status):**
- Document root is correct
- Issue is just missing image files

### Issue 2: No Logo Uploaded

Your logo file hasn't been uploaded to the server.

**Expected location:**
```
public/assets/images/logo.png  (or logo.jpg, logo.svg)
```

**Current state:**
```
public/assets/images/placeholder.txt  (empty)
```

### Issue 3: No Vehicle Images

Vehicle photos haven't been migrated from the old server.

**Expected location:**
```
storage/uploads/vehicles/
├── vehicle-1-photo-1.jpg
├── vehicle-1-photo-2.jpg
├── vehicle-2-photo-1.jpg
└── ...
```

**Current state:**
```
storage/uploads/  (empty)
```

---

## Solution 1: Fix Document Root (If Not Done)

### Step 1: Verify Document Root

**Via cPanel:**
1. cPanel → Domains → Manage `elitecarhire.au`
2. Check "Document Root" field
3. Should be: `/home/cp825575/EliteCarHireWeb/public`
4. If wrong, update it and save

### Step 2: Test Assets Loading

Visit these URLs directly (should NOT give 404):
- https://elitecarhire.au/assets/css/style.css
- https://elitecarhire.au/assets/js/app.min.js

If you get **404 errors**, document root is still wrong.

---

## Solution 2: Upload Logo Image

### Option A: Upload via cPanel File Manager

1. **Log into cPanel**

2. **Navigate to File Manager**

3. **Go to directory:**
   ```
   EliteCarHireWeb/public/assets/images/
   ```

4. **Upload your logo file:**
   - Click "Upload" button
   - Choose your logo file (logo.png, logo.jpg, or logo.svg)
   - Upload it

5. **Set correct permissions:**
   - Right-click logo file → Permissions
   - Set to: `644` (rw-r--r--)

### Option B: Upload via FTP

1. **Connect to server via FTP** (FileZilla, WinSCP, etc.)

2. **Navigate to:**
   ```
   /home/cp825575/EliteCarHireWeb/public/assets/images/
   ```

3. **Upload logo file:**
   - Drag logo file to this directory
   - Rename to: `logo.png` (or appropriate extension)

4. **Set permissions to 644**

### Option C: Use a Temporary Logo (Quick Fix)

If you don't have a logo ready, create a text-based logo temporarily:

**Create file:** `public/assets/images/logo.svg`

```svg
<svg width="200" height="50" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="50" fill="#D4AF37"/>
  <text x="100" y="30" font-family="Arial, sans-serif" font-size="20" fill="#FFFFFF" text-anchor="middle" dominant-baseline="middle">Elite Car Hire</text>
</svg>
```

Upload this as a temporary logo until you have your real logo ready.

---

## Solution 3: Upload Vehicle Images

### Step 1: Get Images from Old Server

If you had vehicle images on the old server:

1. **Connect to old server via FTP**
2. **Download all images from:**
   - `/storage/uploads/`
   - `/public/uploads/` (if exists)
   - Or wherever vehicle images were stored

3. **Save to your local computer**

### Step 2: Upload to New Server

1. **Connect to new server via FTP**

2. **Navigate to:**
   ```
   /home/cp825575/EliteCarHireWeb/storage/uploads/
   ```

3. **Create subdirectory if needed:**
   ```
   storage/uploads/vehicles/
   ```

4. **Upload all vehicle images**

5. **Set permissions:**
   - Directory: `755` (rwxr-xr-x)
   - Files: `644` (rw-r--r--)

### Step 3: Verify Image Paths in Database

Vehicle image paths are stored in the `vehicle_images` table.

**Check paths via phpMyAdmin:**

```sql
SELECT id, vehicle_id, image_path, is_primary
FROM vehicle_images
LIMIT 10;
```

**Expected path format:**
```
/storage/uploads/vehicles/car-photo-1.jpg
```

**If paths are wrong, update them:**

```sql
-- If old paths had full URLs or wrong paths
UPDATE vehicle_images
SET image_path = REPLACE(image_path, 'old-domain.com', '')
WHERE image_path LIKE '%old-domain%';

-- If paths are missing /storage/uploads/ prefix
UPDATE vehicle_images
SET image_path = CONCAT('/storage/uploads/vehicles/', image_path)
WHERE image_path NOT LIKE '/storage%';
```

---

## Solution 4: Verify File Permissions

Incorrect permissions can prevent images from loading.

### Via SSH (if available):

```bash
# Navigate to project
cd /home/cp825575/EliteCarHireWeb

# Set directory permissions
chmod 755 public/assets
chmod 755 public/assets/images
chmod 755 storage/uploads

# Set file permissions
chmod 644 public/assets/images/*
chmod 644 storage/uploads/vehicles/*

# Make sure storage/uploads is writable
chmod 775 storage/uploads
```

### Via cPanel File Manager:

1. Right-click on `public/assets/images/` folder
2. Select "Change Permissions"
3. Set to `755` (check: Owner Read/Write/Execute, Group Read/Execute, World Read/Execute)
4. Repeat for image files: `644`

---

## Solution 5: Check .htaccess Rules

If document root is at repository root (not /public/), ensure root `.htaccess` allows asset access.

**Verify root `.htaccess` contains:**

```apache
# Allow access to public assets
RewriteCond %{REQUEST_URI} ^/public/
RewriteRule ^public/(.*)$ /public/$1 [L]
```

Or better yet, **fix document root to point to `/public/`** (see DOCUMENT_ROOT_FIX.md).

---

## Quick Testing Checklist

After uploading images, test these URLs directly:

### Logo:
- ✅ https://elitecarhire.au/assets/images/logo.png (should show logo)
- ✅ https://elitecarhire.au/assets/images/logo.svg (if SVG)

### CSS/JS:
- ✅ https://elitecarhire.au/assets/css/style.css (should show CSS code)
- ✅ https://elitecarhire.au/assets/js/app.min.js (should show JS code)

### Vehicle Images (if uploaded):
- ✅ https://elitecarhire.au/storage/uploads/vehicles/car-1.jpg

If any return **404 Not Found**, document root configuration is still wrong.

---

## Advanced: Storage Symlink (Alternative)

If you can't get storage images to load, create a symlink from public to storage:

### Via SSH:

```bash
cd /home/cp825575/EliteCarHireWeb/public
ln -s ../storage/uploads storage
```

Then update image paths in database to use:
```
/storage/vehicle-photo.jpg  (instead of /storage/uploads/...)
```

---

## Browser Cache Issue

Sometimes images are cached as "broken".

**Clear browser cache:**
1. Open Developer Tools (F12)
2. Right-click refresh button
3. Select "Empty Cache and Hard Reload"
4. Or use Ctrl+Shift+R (Cmd+Shift+R on Mac)

---

## Summary of Required Actions

### Immediate Actions:

1. **✅ Fix Document Root** (if not already done)
   - Change to: `/home/cp825575/EliteCarHireWeb/public`
   - See: `DOCUMENT_ROOT_FIX.md`

2. **📤 Upload Logo**
   - To: `public/assets/images/logo.png`
   - Set permissions: `644`

3. **📤 Upload Vehicle Images** (if applicable)
   - To: `storage/uploads/vehicles/`
   - Set directory permissions: `755`
   - Set file permissions: `644`

4. **🔍 Verify Database Image Paths**
   - Check `vehicle_images` table
   - Update paths if needed

5. **🧪 Test**
   - Visit homepage
   - Check if logo displays
   - Browse vehicles (if images uploaded)

---

## Common Scenarios

### Scenario 1: "CSS/JS loads but no images"
**Cause:** Images not uploaded
**Fix:** Upload logo and vehicle images

### Scenario 2: "Nothing loads, still see directory listing"
**Cause:** Document root still wrong
**Fix:** Change document root to `/public/` subdirectory

### Scenario 3: "Homepage works but vehicle pages broken"
**Cause:** Vehicle images not uploaded or paths wrong
**Fix:** Upload images and verify database paths

### Scenario 4: "Images were fine on old server"
**Cause:** Images not migrated during move
**Fix:** Download from old server, upload to new server

---

## Need Help?

If images still don't show after following this guide:

1. **Check browser console** (F12 → Console tab)
   - Look for 404 errors
   - Note which image URLs are failing

2. **Check error logs**
   - cPanel → Errors
   - Look for permission denied or file not found errors

3. **Verify paths**
   - Check `vehicle_images` table in database
   - Verify actual files exist at those paths

4. **Contact hosting support**
   - Ask them to verify document root points to `/public/`
   - Ask them to check file permissions on assets and storage

---

**Priority:** Fix document root FIRST, then upload images. Document root misconfiguration will prevent all assets from loading.

See: `DOCUMENT_ROOT_FIX.md` for complete document root fix instructions.
