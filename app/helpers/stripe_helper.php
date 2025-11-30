<?php
/**
 * Stripe Helper Functions
 *
 * Provides Stripe configuration and helper functions for payment processing
 */

/**
 * Get Stripe configuration from database
 *
 * @return array Stripe configuration
 */
function getStripeConfig() {
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    try {
        // Get Stripe mode (test or live)
        $mode = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_mode'");
        $stripeMode = $mode['setting_value'] ?? 'test';

        // Get appropriate keys based on mode
        if ($stripeMode === 'live') {
            $publishableKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_live_publishable_key'");
            $secretKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_live_secret_key'");
        } else {
            $publishableKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_test_publishable_key'");
            $secretKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_test_secret_key'");
        }

        $webhookSecret = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_webhook_secret'");

        $config = [
            'mode' => $stripeMode,
            'publishable_key' => $publishableKey['setting_value'] ?? '',
            'secret_key' => $secretKey['setting_value'] ?? '',
            'webhook_secret' => $webhookSecret['setting_value'] ?? '',
        ];

        return $config;
    } catch (Exception $e) {
        error_log("Error getting Stripe config: " . $e->getMessage());
        return [
            'mode' => 'test',
            'publishable_key' => '',
            'secret_key' => '',
            'webhook_secret' => '',
        ];
    }
}

/**
 * Initialize Stripe API
 *
 * @return void
 */
function initStripe() {
    require_once __DIR__ . '/../../vendor/autoload.php';

    $config = getStripeConfig();

    if (empty($config['secret_key'])) {
        throw new Exception('Stripe secret key not configured');
    }

    \Stripe\Stripe::setApiKey($config['secret_key']);
}

/**
 * Get Stripe publishable key for frontend
 *
 * @return string
 */
function getStripePublishableKey() {
    $config = getStripeConfig();
    return $config['publishable_key'];
}

/**
 * Check if Stripe is configured
 *
 * @return bool
 */
function isStripeConfigured() {
    $config = getStripeConfig();
    return !empty($config['secret_key']) && !empty($config['publishable_key']);
}

/**
 * Format amount for Stripe (convert to cents)
 *
 * @param float $amount Amount in dollars
 * @return int Amount in cents
 */
function stripeAmount($amount) {
    return (int) round($amount * 100);
}

/**
 * Format amount from Stripe (convert from cents)
 *
 * @param int $cents Amount in cents
 * @return float Amount in dollars
 */
function dollarAmount($cents) {
    return $cents / 100;
}
