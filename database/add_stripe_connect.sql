-- Stripe Connect Database Schema Changes
-- Run this migration to enable Stripe Connect for automated payouts

-- Add Stripe Connect account ID to users table
ALTER TABLE users
ADD COLUMN stripe_account_id VARCHAR(255) NULL AFTER email,
ADD COLUMN stripe_account_status ENUM('not_connected', 'pending', 'verified', 'rejected') DEFAULT 'not_connected' AFTER stripe_account_id,
ADD COLUMN stripe_onboarding_completed BOOLEAN DEFAULT FALSE AFTER stripe_account_status,
ADD COLUMN stripe_details_submitted BOOLEAN DEFAULT FALSE AFTER stripe_onboarding_completed,
ADD COLUMN stripe_charges_enabled BOOLEAN DEFAULT FALSE AFTER stripe_details_submitted,
ADD COLUMN stripe_payouts_enabled BOOLEAN DEFAULT FALSE AFTER stripe_charges_enabled;

-- Add Stripe transfer ID to payouts table for tracking automatic transfers
ALTER TABLE payouts
ADD COLUMN stripe_transfer_id VARCHAR(255) NULL AFTER reference,
ADD COLUMN stripe_payout_id VARCHAR(255) NULL AFTER stripe_transfer_id,
ADD COLUMN transfer_date DATETIME NULL AFTER payout_date,
ADD COLUMN failure_code VARCHAR(100) NULL AFTER notes,
ADD COLUMN failure_message TEXT NULL AFTER failure_code;

-- Add indexes for performance
CREATE INDEX idx_stripe_account_id ON users(stripe_account_id);
CREATE INDEX idx_stripe_transfer_id ON payouts(stripe_transfer_id);
CREATE INDEX idx_payout_status ON payouts(status, created_at);

-- Add Stripe Connect settings to settings table
INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES
('stripe_connect_enabled', '0', NOW(), NOW()),
('stripe_connect_client_id', '', NOW(), NOW()),
('stripe_connect_onboarding_return_url', '', NOW(), NOW()),
('stripe_connect_onboarding_refresh_url', '', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Update payouts table status ENUM to include more statuses (if needed)
-- Note: This is optional, current statuses should work
-- ALTER TABLE payouts MODIFY COLUMN status ENUM('pending', 'scheduled', 'processing', 'completed', 'failed', 'reversed') DEFAULT 'pending';
