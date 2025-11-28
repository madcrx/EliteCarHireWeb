-- Add destination fields to bookings table for multiple stop tracking
-- These fields help owners make informed decisions about bookings with multiple destinations

ALTER TABLE bookings
ADD COLUMN destination_1 VARCHAR(255) NULL AFTER pickup_location,
ADD COLUMN destination_2 VARCHAR(255) NULL AFTER destination_1,
ADD COLUMN destination_3 VARCHAR(255) NULL AFTER destination_2;

-- Note: dropoff_location column already exists in the table at line 76 of schema
