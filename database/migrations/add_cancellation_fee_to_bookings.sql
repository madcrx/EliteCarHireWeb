-- Add cancellation fee and refund tracking columns
-- This migration adds support for the 50% cancellation fee policy

-- Add refund_amount column if it doesn't exist
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS refund_amount DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Amount refunded to customer';

-- Add refund_status column if it doesn't exist
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS refund_status VARCHAR(50) DEFAULT 'not_applicable' COMMENT 'Refund status: not_applicable, full_refund, partial_refund, no_refund';

-- Add cancellation_fee column to bookings table
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS cancellation_fee DECIMAL(10, 2) DEFAULT 0.00 COMMENT '50% cancellation fee charged on paid bookings';

-- Add cancelled_at timestamp if it doesn't exist
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS cancelled_at DATETIME NULL COMMENT 'Timestamp when booking was cancelled';

-- Add cancellation_reason if it doesn't exist
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS cancellation_reason TEXT NULL COMMENT 'Reason for booking cancellation';

-- Add initiated_by_customer column to pending_changes table to track who requested the cancellation
ALTER TABLE pending_changes
ADD COLUMN IF NOT EXISTS initiated_by_customer TINYINT(1) DEFAULT 0 COMMENT '1 if change requested by customer, 0 if requested by owner';

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_initiated_by_customer ON pending_changes(initiated_by_customer);

-- Add index for cancellation tracking
CREATE INDEX IF NOT EXISTS idx_cancelled_at ON bookings(cancelled_at);
CREATE INDEX IF NOT EXISTS idx_refund_status ON bookings(refund_status);

