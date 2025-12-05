-- Stripe Connect Configuration for Database Settings Table
-- This script configures Stripe Connect settings in the database
-- Run AFTER stripe_integration.sql has been executed successfully
-- Run Date: 2025-12-05

-- Enable Stripe Connect (MANDATORY)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_enabled', '1', 'boolean')
ON DUPLICATE KEY UPDATE setting_value = '1', updated_at = CURRENT_TIMESTAMP;

-- Add Client ID (replace with your actual Stripe Connect Client ID)
-- Get from: https://dashboard.stripe.com/settings/connect
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_client_id', 'ca_TXwndOWxhuCtEo3ijE0yIaCB4ErO4QEp', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'ca_TXwndOWxhuCtEo3ijE0yIaCB4ErO4QEp', updated_at = CURRENT_TIMESTAMP;

-- Set Onboarding Return URL (where owners return after connecting Stripe)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_onboarding_return_url', 'https://elitecarhire.au/owner/stripe/return', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'https://elitecarhire.au/owner/stripe/return', updated_at = CURRENT_TIMESTAMP;

-- Set Onboarding Refresh URL (where owners can restart onboarding)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_connect_onboarding_refresh_url', 'https://elitecarhire.au/owner/stripe/refresh', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'https://elitecarhire.au/owner/stripe/refresh', updated_at = CURRENT_TIMESTAMP;

-- Add Webhook Secret (replace with your actual webhook signing secret)
-- Get from: https://dashboard.stripe.com/webhooks (click on your endpoint, reveal signing secret)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_webhook_secret', 'whsec_g2rjILd8GCux6XwIWExPGUHYw6MhWP7U', 'string')
ON DUPLICATE KEY UPDATE setting_value = 'whsec_g2rjILd8GCux6XwIWExPGUHYw6MhWP7U', updated_at = CURRENT_TIMESTAMP;

-- Verify settings were added
SELECT 'Stripe Connect settings configured successfully!' AS status;
SELECT setting_key, setting_value, setting_type, updated_at
FROM settings
WHERE setting_key LIKE 'stripe%'
ORDER BY setting_key;
