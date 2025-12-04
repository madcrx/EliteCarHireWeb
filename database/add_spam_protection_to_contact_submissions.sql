-- Add IP address column for spam protection and rate limiting
-- Run this migration to enable spam protection features

ALTER TABLE contact_submissions
ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL AFTER message,
ADD INDEX idx_ip_created (ip_address, created_at);

-- Note: VARCHAR(45) supports both IPv4 and IPv6 addresses
-- Index on (ip_address, created_at) improves rate limiting query performance
