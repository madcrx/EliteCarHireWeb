# Phase 2 Database Update Instructions

## Overview
This file contains instructions for updating the database with Phase 2 enhancements including:
- Site image management table
- Vehicle blocked dates table (for owner calendar)
- State field for vehicles (for location filtering)

## How to Update

### Option 1: Using cPanel phpMyAdmin (Recommended)

1. Log into your cPanel account
2. Open phpMyAdmin
3. Select your database: `cyberlog_elite_car_hire`
4. Click the "SQL" tab at the top
5. Open the file `/database/phase2_updates.sql` from this repository
6. Copy all the SQL content
7. Paste it into the SQL query box in phpMyAdmin
8. Click "Go" to execute
9. You should see success messages for each table creation/alteration

### Option 2: Using MySQL Command Line

```bash
mysql -u cyberlog_ECHadmin -p cyberlog_elite_car_hire < database/phase2_updates.sql
```

Enter your database password (`ECH2973!`) when prompted.

## What Gets Updated

### 1. site_images Table
- New table for managing site images (logos, banners, etc.)
- Allows admin to upload custom images and revert to defaults
- Includes default records for logo_header, logo_footer, hero_home, banner_services, banner_about

### 2. vehicle_blocked_dates Table
- New table for owner calendar blocking
- Allows vehicle owners to block dates when vehicles are unavailable
- Supports date ranges with optional reasons
- Prevents double booking during blocked periods

### 3. vehicles Table Updates
- Adds `state` VARCHAR(50) field for location-based filtering
- Allows users to search vehicles by state/location

## Verify Installation

After running the SQL, verify the updates by running these queries in phpMyAdmin:

```sql
-- Check site_images table exists and has default data
SELECT COUNT(*) as image_count FROM site_images;
-- Should return: 5 rows

-- Check vehicle_blocked_dates table exists
SHOW TABLES LIKE 'vehicle_blocked_dates';
-- Should return: vehicle_blocked_dates

-- Check state column was added to vehicles
SHOW COLUMNS FROM vehicles LIKE 'state';
-- Should return: state | varchar(50) | YES | | NULL |
```

## Rollback (if needed)

If you need to rollback these changes:

```sql
-- Remove site_images table
DROP TABLE IF EXISTS site_images;

-- Remove vehicle_blocked_dates table
DROP TABLE IF EXISTS vehicle_blocked_dates;

-- Remove state field from vehicles
ALTER TABLE vehicles DROP COLUMN state;
```

## Troubleshooting

### Error: Table already exists
If you see "Table 'site_images' already exists", the updates have already been applied. No action needed.

### Error: Duplicate column name 'state'
If you see this error, the state column already exists. You can ignore this error or run just the parts of the SQL that are missing.

### Error: Access denied
Ensure you're using the correct database credentials:
- Database: cyberlog_elite_car_hire
- Username: cyberlog_ECHadmin
- Password: ECH2973!

## Sample Data

To populate sample data for testing, see `database/sample_data.sql` which includes:
- 6 sample vehicles with various categories and states
- 3 sample CMS pages
- 3 sample contact submissions

## Questions?

If you encounter any issues, check:
1. Database connection is working (test via /admin/dashboard)
2. You have appropriate permissions on the database
3. The SQL syntax is correct for your MySQL version (5.7+)
