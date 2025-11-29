# Elite Car Hire - Website Images

This directory contains placeholder SVG images for the Elite Car Hire website.

## Current Placeholder Images

### Vehicle Category Images
Professional SVG illustrations representing different vehicle types:

1. **luxury-sedan.svg** - Executive sedans and premium saloon cars
2. **luxury-suv.svg** - High-end SUVs and large vehicles
3. **muscle-car.svg** - Classic American muscle cars
4. **wedding-car.svg** - Elegant wedding vehicles (Rolls-Royce style)
5. **sports-car.svg** - High-performance sports cars
6. **limousine.svg** - Stretch limousines and VIP transport

### General Images
7. **placeholder.svg** - Generic vehicle placeholder for unassigned images
8. **hero-bg.svg** - Homepage hero section background

## Adding Real Vehicle Photos

To replace these placeholders with actual vehicle photos:

### 1. Prepare Your Images
- **Format**: JPG or PNG
- **Dimensions**: Minimum 800x600px (4:3 ratio recommended)
- **Size**: Optimize images to under 500KB for fast loading
- **Quality**: High-quality photos showing the vehicle from multiple angles

### 2. Image Guidelines
- Use professional photography with good lighting
- Show vehicles from flattering angles (front 3/4 view works best)
- Ensure clean backgrounds
- Multiple photos per vehicle recommended (front, side, interior, rear)

### 3. Upload Process

#### Via Admin Panel:
1. Log in to the admin dashboard
2. Navigate to Vehicles section
3. Select a vehicle to edit
4. Use the image upload feature to add photos
5. Set a primary image for the vehicle listing

#### Manual Upload:
1. Upload images to `/public/uploads/vehicles/`
2. Use descriptive filenames: `{make}-{model}-{year}-{angle}.jpg`
   - Example: `mercedes-s-class-2023-front.jpg`
3. Update the database `vehicles` table:
   - Set `primary_image` to the relative path: `uploads/vehicles/filename.jpg`

### 4. Image Categories by Vehicle Type

**Luxury Sedans**: Mercedes S-Class, BMW 7 Series, Audi A8
- Focus on elegant exterior shots
- Include interior luxury details

**Muscle Cars**: Ford Mustang, Dodge Charger, Chevrolet Camaro
- Action angles highlighting power
- Show custom paint jobs and details

**SUVs**: Range Rover, Cadillac Escalade
- Emphasize size and presence
- Include spacious interior shots

**Wedding Cars**: Rolls-Royce, Bentley, Classic vehicles
- Elegant, dignified angles
- Show any special decorations or features

**Sports Cars**: Ferrari, Lamborghini, Porsche
- Dynamic angles
- Highlight performance features

**Limousines**: Stretch Hummer, Lincoln, Chrysler 300
- Full-length side view to show size
- Interior shots showing capacity and amenities

## Recommended Image Sources

If you need professional vehicle photos:

1. **Your Own Fleet**: Take professional photos of your actual vehicles
2. **Stock Photo Sites**:
   - Unsplash.com (free, high-quality)
   - Pexels.com (free, curated)
   - Pixabay.com (free, creative commons)
3. **Professional Photography**: Hire a professional automotive photographer

## Technical Notes

- SVG images are scalable and load quickly
- They automatically match the website's color scheme (gold #C5A253)
- The website will display SVG placeholders when no primary_image is set
- All images support responsive design and mobile viewing

## File Structure
```
public/
├── assets/
│   └── images/
│       ├── luxury-sedan.svg
│       ├── muscle-car.svg
│       ├── luxury-suv.svg
│       ├── wedding-car.svg
│       ├── sports-car.svg
│       ├── limousine.svg
│       ├── placeholder.svg
│       ├── hero-bg.svg
│       └── README.md (this file)
└── uploads/
    └── vehicles/
        └── (your vehicle photos go here)
```

## Need Help?

For technical assistance with image uploads or any issues, please contact your web administrator.
