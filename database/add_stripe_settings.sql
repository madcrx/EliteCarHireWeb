-- Stripe Integration Settings Migration
-- This migration adds Stripe configuration to the settings table

-- Insert Stripe mode (test/live)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_mode', 'test', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Insert Stripe Test Keys (to be configured via Admin UI at /admin/settings/stripe)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_test_publishable_key', '', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_test_secret_key', '', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Insert Stripe Live Keys (placeholders)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_live_publishable_key', '', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_live_secret_key', '', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Insert Webhook Secret (to be configured via Admin UI at /admin/settings/stripe)
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('stripe_webhook_secret', '', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Payment gateway selection
INSERT INTO settings (setting_key, setting_value, setting_type)
VALUES ('payment_gateway', 'stripe', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
