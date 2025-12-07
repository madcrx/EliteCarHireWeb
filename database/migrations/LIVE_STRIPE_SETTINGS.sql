-- LIVE STRIPE SETTINGS CONFIGURATION
-- IMPORTANT: Use this for production/live environment only!
-- INSTRUCTIONS: Replace the placeholder values below with your actual Stripe keys
-- Copy and paste this ENTIRE file into phpMyAdmin SQL tab

-- 1. Enable Stripe Connect
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_enabled', '1', 'boolean')
ON DUPLICATE KEY UPDATE setting_value = '1';

-- 2. Add Live Client ID
-- REPLACE: 'YOUR_STRIPE_CONNECT_CLIENT_ID' with your actual Client ID (starts with ca_)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_client_id', 'YOUR_STRIPE_CONNECT_CLIENT_ID', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'YOUR_STRIPE_CONNECT_CLIENT_ID';

-- 3. Set Return URL (Update if your domain is different)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_onboarding_return_url', 'https://elitecarhire.au/owner/stripe/return', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'https://elitecarhire.au/owner/stripe/return';

-- 4. Set Refresh URL (Update if your domain is different)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_onboarding_refresh_url', 'https://elitecarhire.au/owner/stripe/refresh', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'https://elitecarhire.au/owner/stripe/refresh';

-- 5. Add Live Webhook Secret
-- REPLACE: 'YOUR_STRIPE_WEBHOOK_SECRET' with your actual Webhook Secret (starts with whsec_)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_webhook_secret', 'YOUR_STRIPE_WEBHOOK_SECRET', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'YOUR_STRIPE_WEBHOOK_SECRET';

-- Verify all settings were added
SELECT 'SUCCESS! Live Stripe settings configured.' AS Status;
SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'stripe%';
