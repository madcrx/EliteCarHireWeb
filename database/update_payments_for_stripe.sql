-- Update Payments Table for Stripe Integration
-- This migration adds Stripe-specific columns to the payments table

-- Add stripe_payment_intent_id column if it doesn't exist
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS stripe_payment_intent_id VARCHAR(255) NULL AFTER transaction_id,
ADD INDEX idx_stripe_payment_intent (stripe_payment_intent_id);

-- Update payment_method column to accommodate card brands
ALTER TABLE payments
MODIFY COLUMN payment_method VARCHAR(50) DEFAULT 'credit_card';

-- Add created_at and updated_at if they don't exist
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
