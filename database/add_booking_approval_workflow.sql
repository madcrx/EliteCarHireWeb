-- Add additional_charges_reason field to bookings table
ALTER TABLE bookings
ADD COLUMN additional_charges_reason TEXT NULL AFTER additional_charges;

-- Update status ENUM to include 'awaiting_approval' status
ALTER TABLE bookings
MODIFY COLUMN status ENUM('pending', 'awaiting_approval', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';
