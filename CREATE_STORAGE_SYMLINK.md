# Fix Images Not Showing - Create Storage Symlink

## Problem

Your images are stored in:
- **Logos:** `/storage/uploads/site-images/`
- **Vehicles:** `/storage/vehicles/`

But the `/storage/` directory is **outside** the `/public/` directory, so it's not web-accessible.

The code references images like:
- `/storage/uploads/site-images/logo.png`
- `/storage/vehicles/car-photo.jpg`

But browsers get **404 errors** because `/storage/` doesn't exist in the web root.

---

## Solution: Create Symlink

Create a symbolic link from `public/storage` to `../storage/uploads` so images are accessible.

---

## Option 1: Via SSH (Recommended)

```bash
# Navigate to public directory
cd /home/cp825575/EliteCarHireWeb/public

# Create symlink to storage directory (NOT storage/uploads)
ln -s ../storage storage

# Verify symlink was created
ls -la storage
```

**Expected output:**
```
lrwxrwxrwx 1 cp825575 cp825575 18 Dec 4 storage -> ../storage/uploads
```

---

## Option 2: Via cPanel Terminal

1. **Log into cPanel**
2. **Open Terminal** (under Advanced section)
3. **Run these commands:**

```bash
cd /home/cp825575/EliteCarHireWeb/public
ln -s ../storage storage
ls -la storage
```

---

## Option 3: Via PHP Script (If No SSH Access)

If you don't have SSH or Terminal access, create this file:

**File:** `public/create-storage-link.php`

```php
<?php
// Create storage symlink
// RUN ONCE then DELETE this file

$target = __DIR__ . '/../storage';
$link = __DIR__ . '/storage';

// Check if symlink already exists
if (file_exists($link)) {
    if (is_link($link)) {
        echo "✅ Symlink already exists!<br>";
        echo "Target: " . readlink($link) . "<br>";
        exit;
    } else {
        echo "❌ 'storage' exists but is not a symlink. Please delete it first.<br>";
        exit;
    }
}

// Create symlink
if (symlink($target, $link)) {
    echo "✅ Storage symlink created successfully!<br>";
    echo "Link: $link<br>";
    echo "Target: $target<br>";
    echo "<br><strong>⚠️ DELETE THIS FILE NOW FOR SECURITY!</strong>";
} else {
    echo "❌ Failed to create symlink. Your hosting might not support symlinks.<br>";
    echo "Try Option 4 (copy files) instead.";
}
?>
```

**Steps:**
1. Upload `create-storage-link.php` to `public/` directory
2. Visit: `https://elitecarhire.au/create-storage-link.php`
3. Should see "✅ Storage symlink created successfully!"
4. **DELETE the file immediately** (security risk)

---

## Option 4: Copy Files (If Symlinks Not Supported)

If your host doesn't support symlinks, copy the storage directory:

### Via cPanel File Manager:

1. Navigate to: `/home/cp825575/EliteCarHireWeb/storage/uploads`
2. Select all folders (site-images, vehicles, etc.)
3. Click "Copy"
4. Destination: `/home/cp825575/EliteCarHireWeb/public/storage/uploads`
5. Click "Copy"

### Via SSH/Terminal:

```bash
cd /home/cp825575/EliteCarHireWeb/public
cp -r ../storage ./storage
chmod -R 755 storage
```

**Downside:** Files uploaded through admin won't appear until you copy again.

---

## Verify It Works

After creating the symlink or copying files:

### Test Logo:
Visit: `https://elitecarhire.au/storage/uploads/site-images/`

**Expected:** Should see directory listing of logo files

### Test Direct Image:
Visit: `https://elitecarhire.au/storage/uploads/site-images/logo-123456.png`
(Replace with actual filename)

**Expected:** Image should display

### Test Vehicle Images:
Visit: `https://elitecarhire.au/storage/vehicles/`

**Expected:** Should see vehicle images directory

---

## Alternative: Update .htaccess (If Above Don't Work)

If symlinks and copying don't work, route `/storage/` requests through PHP:

**Add to `public/.htaccess`:**

```apache
# Serve files from /storage/ directory
RewriteCond %{REQUEST_URI} ^/storage/
RewriteCond %{DOCUMENT_ROOT}/../storage%{REQUEST_URI} -f
RewriteRule ^storage/(.*)$ /serve-storage.php?file=$1 [L,QSA]
```

**Create `public/serve-storage.php`:**

```php
<?php
// Serve files from storage directory
$file = $_GET['file'] ?? '';

// Security: prevent directory traversal
if (strpos($file, '..') !== false) {
    http_response_code(403);
    exit('Forbidden');
}

$filePath = __DIR__ . '/../storage/' . $file;

// Check file exists
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit('Not Found');
}

// Get mime type
$mimeType = mime_content_type($filePath);

// Serve file
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
```

---

## Update Vehicle Image Paths (If Needed)

If vehicle images are in `/storage/vehicles/` but database has wrong paths:

```sql
-- Check current paths
SELECT id, vehicle_id, image_path
FROM vehicle_images
LIMIT 10;

-- Update paths if needed
UPDATE vehicle_images
SET image_path = CONCAT('/storage/vehicles/', SUBSTRING_INDEX(image_path, '/', -1))
WHERE image_path NOT LIKE '/storage/vehicles/%';

-- Or if paths have /storage/uploads/vehicles/
UPDATE vehicle_images
SET image_path = REPLACE(image_path, '/storage/uploads/vehicles/', '/storage/vehicles/')
WHERE image_path LIKE '/storage/uploads/vehicles/%';
```

---

## Summary

**Recommended Solution:**
1. Create symlink: `public/storage` → `../storage`
2. This makes both `/storage/uploads/site-images/` and `/storage/vehicles/` accessible via web URLs
3. Works for both logos and vehicle images

**After Creating Symlink:**
- ✅ Logos will show (from `/storage/uploads/site-images/`)
- ✅ Vehicle images will show (from `/storage/vehicles/`)
- ✅ New uploads through admin will work automatically

---

## Testing After Fix

1. **Clear browser cache** (Ctrl+Shift+R)
2. **Visit homepage:** https://elitecarhire.au
3. **Logo should appear** in top-left corner
4. **Browse vehicles:** Vehicle photos should show
5. **Check browser console** (F12 → Console) - no 404 errors

---

**Next Steps:**
1. Create the symlink using one of the options above
2. Test that images load
3. If still having issues, check the paths in the database match the actual file locations
