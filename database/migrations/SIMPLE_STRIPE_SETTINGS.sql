-- SIMPLE STRIPE SETTINGS CONFIGURATION
-- Copy and paste this ENTIRE file into phpMyAdmin SQL tab
-- This version works WITHOUT created_at column

-- 1. Enable Stripe Connect
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_enabled', '1', 'boolean')
ON DUPLICATE KEY UPDATE setting_value = '1';

-- 2. Add Client ID
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_client_id', 'ca_TXwndOWxhuCtEo3ijE0yIaCB4ErO4QEp', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'ca_TXwndOWxhuCtEo3ijE0yIaCB4ErO4QEp';

-- 3. Set Return URL
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_onboarding_return_url', 'https://elitecarhire.au/owner/stripe/return', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'https://elitecarhire.au/owner/stripe/return';

-- 4. Set Refresh URL
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_onboarding_refresh_url', 'https://elitecarhire.au/owner/stripe/refresh', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'https://elitecarhire.au/owner/stripe/refresh';

-- 5. Add Webhook Secret
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_webhook_secret', 'whsec_g2rjILd8GCux6XwIWExPGUHYw6MhWP7U', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'whsec_g2rjILd8GCux6XwIWExPGUHYw6MhWP7U';

-- Verify all settings were added
SELECT 'SUCCESS! Stripe settings configured.' AS Status;
SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'stripe%';
