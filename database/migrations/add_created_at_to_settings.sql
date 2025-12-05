-- Migration: Add created_at column to settings table
-- This migration should be run BEFORE attempting to insert Stripe settings with created_at
-- Run Date: 2025-12-05

-- Check if created_at column exists, and add it if it doesn't
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'settings'
    AND COLUMN_NAME = 'created_at'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE settings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER setting_type',
    'SELECT "Column created_at already exists in settings table, skipping..." AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the column was added or exists
SELECT 'Settings table updated successfully. Column structure:' AS status;
SHOW COLUMNS FROM settings;
