-- Stripe Integration Database Migrations
-- Run this on your live database to add Stripe-related tables

-- Add Stripe account ID to users table for Connect integration
ALTER TABLE users
ADD COLUMN stripe_account_id VARCHAR(255) NULL AFTER email,
ADD INDEX idx_stripe_account (stripe_account_id);

-- Webhook events tracking table
CREATE TABLE IF NOT EXISTS stripe_webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(255) UNIQUE NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload LONGTEXT,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_processed_at (processed_at)
) ENGINE=InnoDB;

-- Payment failures tracking
CREATE TABLE IF NOT EXISTS payment_failures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    transaction_id VARCHAR(255),
    failure_reason TEXT,
    failure_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_failure_date (failure_date)
) ENGINE=InnoDB;

-- Payment disputes (chargebacks)
CREATE TABLE IF NOT EXISTS payment_disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    dispute_id VARCHAR(255) UNIQUE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    reason VARCHAR(100),
    status VARCHAR(50),
    evidence_text TEXT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    INDEX idx_payment (payment_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;
