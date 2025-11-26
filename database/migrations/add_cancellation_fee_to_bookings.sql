-- Add cancellation fee and initiated_by_customer columns
-- This migration adds support for the 50% cancellation fee policy

-- Add cancellation_fee column to bookings table
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS cancellation_fee DECIMAL(10, 2) DEFAULT 0.00 AFTER refund_amount;

-- Add comment for clarity
ALTER TABLE bookings
MODIFY COLUMN cancellation_fee DECIMAL(10, 2) DEFAULT 0.00 COMMENT '50% cancellation fee charged on paid bookings';

-- Add initiated_by_customer column to pending_changes table to track who requested the cancellation
ALTER TABLE pending_changes
ADD COLUMN IF NOT EXISTS initiated_by_customer TINYINT(1) DEFAULT 0 AFTER status;

-- Add comment for clarity
ALTER TABLE pending_changes
MODIFY COLUMN initiated_by_customer TINYINT(1) DEFAULT 0 COMMENT '1 if change requested by customer, 0 if requested by owner';

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_initiated_by_customer ON pending_changes(initiated_by_customer);
