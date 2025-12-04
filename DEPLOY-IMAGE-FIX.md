# 🚀 Deploy Image Loading Fix (No SSH Required)

Since you don't have terminal access, follow these steps to deploy the image loading fix via cPanel.

---

## Step 1: Pull Latest Code via cPanel Git

1. **Log into cPanel** (https://elitecarhire.au:2083)
2. **Go to:** Advanced → **Git Version Control**
3. **Click "Manage"** next to your repository
4. **Click "Pull or Deploy"** tab
5. **Click "Update from Remote"**
6. **Select branch:** `claude/fix-car-hire-issues-01QSZ9FtUL8R8k1JxgY6M9ZF`
7. **Click "Pull"**

✅ This will download all the fixes including the automated PHP script.

---

## Step 2: Run the Automated Fix Script

**In your web browser, visit:**

```
https://elitecarhire.au/fix-images.php?secret=fixnow2024
```

This script will automatically:
- ✅ Remove old symlink (if wrong)
- ✅ Create correct symlink: `public/storage` → `../storage`
- ✅ Create image directories:
  - `storage/uploads/logo/`
  - `storage/uploads/site-images/`
  - `storage/vehicles/`
- ✅ Set correct permissions (755)
- ✅ Verify everything works

**The page will show you:**
- Step-by-step progress
- Success/error messages for each step
- Test URLs to verify images are accessible
- Next steps

---

## Step 3: Delete the Fix Script (Security)

**After the script runs successfully:**

1. **Go to:** cPanel → **File Manager**
2. **Navigate to:** `/public/`
3. **Find:** `fix-images.php`
4. **Right-click** → **Delete**

⚠️ **IMPORTANT:** Delete this file immediately after use for security!

---

## Step 4: Test Your Website

1. **Visit:** https://elitecarhire.au
2. **Clear browser cache:** Press `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
3. **Check:**
   - ✅ Logo appears in header
   - ✅ Vehicle images display on fleet page
   - ✅ All images load correctly

---

## If Symlinks Don't Work

If your hosting doesn't allow symlinks, the script will tell you. In that case:

### Manual Alternative (Copy Files):

1. **Via cPanel File Manager:**
   - Navigate to: `/home/cp825575/EliteCarHireWeb/storage/`
   - **Right-click** on `storage` folder → **Copy**
   - Navigate to: `/home/cp825575/EliteCarHireWeb/public/`
   - **Paste** the storage folder here

2. **Set Permissions:**
   - Right-click the `public/storage` folder → **Change Permissions**
   - Set to: `755`

⚠️ **Downside:** You'll need to re-copy after uploading new images.

---

## Troubleshooting

### Images Still Not Showing?

**Check if directories have image files:**

1. **Via cPanel File Manager:**
   - Navigate to: `/home/cp825575/EliteCarHireWeb/storage/uploads/logo/`
   - Check if your logo file exists here
   - Navigate to: `/home/cp825575/EliteCarHireWeb/storage/vehicles/`
   - Check if vehicle images exist here

2. **If files are missing:**
   - Your images might be in a different location
   - Check database `site_images` table for actual paths
   - Upload images via **Admin → Settings** (for logo)
   - Upload images via **Admin → Vehicles** (for vehicle photos)

### Test URLs Return 404?

Visit these URLs:
- https://elitecarhire.au/storage/uploads/logo/
- https://elitecarhire.au/storage/vehicles/

**If you get:**
- **403 Forbidden** = ✅ GOOD (symlink works, directory browsing disabled)
- **404 Not Found** = ❌ Problem (symlink not working)

### Browser Still Shows Old Images?

Try:
- **Hard refresh:** Ctrl+Shift+R (or Cmd+Shift+R on Mac)
- **Clear browser cache completely**
- **Try incognito/private browsing mode**
- **Try a different browser**

---

## Quick Reference

**Files Updated:**
- `.gitignore` - Added `public/storage` to ignore list
- `public/.htaccess` - Added storage URL exception + FollowSymLinks
- `CREATE_STORAGE_SYMLINK.md` - Fixed documentation paths
- `public/create-storage-link.php` - Fixed symlink target
- `public/fix-images.php` - **NEW** - Browser-based deployment script

**Directories Created:**
- `storage/uploads/logo/` - Company logos (Admin → Settings)
- `storage/uploads/site-images/` - Site images (Admin → Images)
- `storage/vehicles/` - Vehicle photos

**Branch:** `claude/fix-car-hire-issues-01QSZ9FtUL8R8k1JxgY6M9ZF`

---

## Summary

✅ **Root cause:** Missing `storage/uploads/logo/` directory + wrong symlink target
✅ **Solution:** Fixed symlink path + created all required directories
✅ **Deployment:** One-click PHP script (no terminal needed)
✅ **Status:** Ready to deploy and test

**Total deployment time:** ~2 minutes
