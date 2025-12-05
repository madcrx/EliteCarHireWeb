-- Migration: Add timestamp columns to payments table
-- This migration should be run BEFORE stripe_integration.sql
-- Run Date: 2025-12-05

-- Add created_at and updated_at columns to payments table if they don't exist
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add index for better query performance on timestamps
ALTER TABLE payments
ADD INDEX IF NOT EXISTS idx_created_at (created_at);
