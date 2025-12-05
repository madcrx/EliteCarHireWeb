-- Migration: Add created_at and updated_at columns to bookings table
-- This migration should be run on the live database
-- Run Date: 2025-12-05

-- Check if created_at column exists in bookings table
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bookings'
    AND COLUMN_NAME = 'created_at'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER cancellation_reason',
    'SELECT "Column created_at already exists in bookings table, skipping..." AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if updated_at column exists in bookings table
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bookings'
    AND COLUMN_NAME = 'updated_at'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT "Column updated_at already exists in bookings table, skipping..." AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the columns were added or exist
SELECT 'Bookings table timestamp columns updated successfully!' AS status;
SHOW COLUMNS FROM bookings LIKE '%_at';
