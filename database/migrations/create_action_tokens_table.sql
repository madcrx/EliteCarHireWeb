-- Action Tokens Table for Secure Email Links
-- Allows one-click actions from emails without requiring login

CREATE TABLE IF NOT EXISTS action_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    metadata JSON NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at),
    INDEX idx_action (action_type, entity_type, entity_id),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Examples of action types:
-- 'confirm_booking', 'cancel_booking', 'approve_vehicle', 'reject_vehicle',
-- 'approve_changes', 'view_booking', 'reply_contact'

-- Cleanup job: Delete expired tokens older than 30 days
-- Can be run via cron: DELETE FROM action_tokens WHERE expires_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
