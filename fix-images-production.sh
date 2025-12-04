#!/bin/bash
# Quick fix script for image loading issue on production server
# Run this on elitecarhire.au server

set -e  # Exit on any error

echo "=========================================="
echo "Elite Car Hire - Image Loading Fix"
echo "=========================================="
echo ""

# Change to project directory
cd /home/cp825575/EliteCarHireWeb || {
    echo "ERROR: Project directory not found!"
    exit 1
}

echo "Step 1: Pulling latest changes from Git..."
git pull origin claude/fix-car-hire-issues-01QSZ9FtUL8R8k1JxgY6M9ZF

echo ""
echo "Step 2: Removing old symlink (if exists)..."
cd public
rm -f storage
echo "✓ Old symlink removed"

echo ""
echo "Step 3: Creating correct symlink..."
ln -s ../storage storage
echo "✓ Symlink created: public/storage -> ../storage"

echo ""
echo "Step 4: Verifying symlink..."
ls -la storage | head -n 3
echo "✓ Symlink verified"

echo ""
echo "Step 5: Creating image directories..."
cd /home/cp825575/EliteCarHireWeb
mkdir -p storage/uploads/logo
mkdir -p storage/uploads/site-images
mkdir -p storage/vehicles
echo "✓ Directories created"

echo ""
echo "Step 6: Setting correct permissions..."
chmod 755 storage/uploads/logo
chmod 755 storage/uploads/site-images
chmod 755 storage/vehicles
echo "✓ Permissions set to 755"

echo ""
echo "=========================================="
echo "✅ IMAGE LOADING FIX COMPLETE!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Visit https://elitecarhire.au and clear browser cache (Ctrl+Shift+R)"
echo "2. If images still don't show, check that image files exist in:"
echo "   - storage/uploads/logo/ (for company logo)"
echo "   - storage/vehicles/ (for vehicle images)"
echo ""
echo "3. Test URLs:"
echo "   - https://elitecarhire.au/storage/uploads/logo/"
echo "   - https://elitecarhire.au/storage/vehicles/"
echo ""
echo "Expected: 403 Forbidden (this is OK - means symlink works!)"
echo "Bad: 404 Not Found (means symlink not working)"
echo ""
