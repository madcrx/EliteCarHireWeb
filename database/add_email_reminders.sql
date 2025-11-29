-- Email Reminders Table
-- Tracks reminder emails sent and scheduled

CREATE TABLE IF NOT EXISTS email_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    reminder_type ENUM('payment_reminder', 'approval_reminder', 'booking_confirmation', 'general') NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    next_retry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_status (status),
    INDEX idx_next_retry (next_retry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add last_email_sent column to bookings for tracking
ALTER TABLE bookings
ADD COLUMN last_email_sent TIMESTAMP NULL AFTER updated_at;
