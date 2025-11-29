# Vehicle Image Display Guide

## How Images Work Now

The website now automatically displays category-appropriate SVG placeholder images for all vehicles without needing database updates!

## Category Mapping

When a vehicle doesn't have a `primary_image` set in the database, the system automatically selects the appropriate placeholder based on the vehicle's category:

| Vehicle Category | SVG Image Displayed | Description |
|-----------------|---------------------|-------------|
| `luxury_sedan` or `sedan` | luxury-sedan.svg | Elegant executive sedan silhouette |
| `muscle_car` or `classic` | muscle-car.svg | Classic American muscle car with racing stripes |
| `suv` or `luxury_suv` | luxury-suv.svg | Spacious luxury SUV |
| `wedding` | wedding-car.svg | Elegant Rolls-Royce style wedding car |
| `sports`, `sports_car`, or `supercar` | sports-car.svg | Low-profile high-performance sports car |
| `limousine` or `limo` | limousine.svg | Stretch limousine with multiple windows |
| Any other category | placeholder.svg | Generic vehicle placeholder |

## Where Placeholders Display

The placeholders now show on:

1. **Homepage** (`/`) - Featured vehicles section
2. **Vehicles Page** (`/vehicles`) - Vehicle listing grid
3. **Vehicle Detail Page** (`/vehicles/{id}`) - Individual vehicle view

## Image Specifications

All SVG images feature:
- **Colors**: Match your brand gold (#C5A253) with elegant black/grey tones
- **Responsive**: Scale perfectly on all devices
- **Fast Loading**: Vector graphics load instantly
- **Professional**: Automotive-style silhouettes

## Testing the Fix

Visit your website at:
- http://ech.cyberlogicit.com.au/vehicles

You should now see:
✅ Category-appropriate images for each vehicle type
✅ No more gray boxes with car icons
✅ Professional SVG illustrations matching your brand

## Adding Real Photos (Optional)

When you're ready to add actual vehicle photos:

### Method 1: Database Update
```sql
UPDATE vehicles
SET primary_image = 'uploads/vehicles/your-photo.jpg'
WHERE id = 1;
```

### Method 2: Admin Panel
1. Log into admin dashboard
2. Edit vehicle
3. Upload photo
4. Set as primary image

### Method 3: Helper Script
Run the included script to assign placeholders to database:
```bash
php public/assets/images/assign-placeholder-images.php
```

## File Permissions

All SVG files now have correct permissions (644) for web server access.

## Need Support?

If images still don't show:
1. Check that files are deployed to server: `/public/assets/images/*.svg`
2. Verify web server can access the files
3. Clear browser cache (Ctrl+Shift+R)
4. Check browser console for any 404 errors

---

**Note**: These changes work immediately without database modifications. The PHP code intelligently selects the right placeholder based on each vehicle's category field.
