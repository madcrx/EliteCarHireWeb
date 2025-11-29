-- Email Reminders Tracking Table
-- This table tracks sent notification emails and manages reminder scheduling

CREATE TABLE IF NOT EXISTS email_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_type VARCHAR(50) NOT NULL COMMENT 'Type of email: booking_request, vehicle_approval, cancellation_request, contact_form',
    entity_type VARCHAR(50) NOT NULL COMMENT 'Entity type: booking, vehicle, pending_change, contact_submission',
    entity_id INT NOT NULL COMMENT 'ID of the related entity',
    recipient_email VARCHAR(255) NOT NULL COMMENT 'Email address of recipient',
    subject VARCHAR(255) NOT NULL COMMENT 'Email subject line',
    sent_at DATETIME NOT NULL COMMENT 'When the original email was sent',
    reminder_sent_at DATETIME NULL COMMENT 'When the reminder was sent (NULL if not sent yet)',
    response_received TINYINT(1) DEFAULT 0 COMMENT '1 if action was taken, 0 if still pending',
    response_received_at DATETIME NULL COMMENT 'When the response was received',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email_type (email_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_response_received (response_received),
    INDEX idx_reminder_check (response_received, reminder_sent_at, sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tracks notification emails for automated reminder sending';

-- Add index for finding emails that need reminders
-- (not responded to, no reminder sent, sent more than 12 hours ago)
CREATE INDEX idx_needs_reminder ON email_reminders(response_received, reminder_sent_at, sent_at);
