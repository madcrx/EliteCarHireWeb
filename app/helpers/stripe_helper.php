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

/**
 * Get Stripe Connect configuration
 *
 * @return array Connect configuration
 */
function getStripeConnectConfig() {
    try {
        $enabled = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_enabled'");
        $clientId = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_client_id'");
        $returnUrl = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_onboarding_return_url'");
        $refreshUrl = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_onboarding_refresh_url'");

        return [
            'enabled' => ($enabled['setting_value'] ?? '0') === '1',
            'client_id' => $clientId['setting_value'] ?? '',
            'return_url' => $returnUrl['setting_value'] ?? '',
            'refresh_url' => $refreshUrl['setting_value'] ?? '',
        ];
    } catch (Exception $e) {
        error_log("Error getting Stripe Connect config: " . $e->getMessage());
        return [
            'enabled' => false,
            'client_id' => '',
            'return_url' => '',
            'refresh_url' => '',
        ];
    }
}

/**
 * Check if Stripe Connect is enabled and configured
 *
 * @return bool
 */
function isStripeConnectEnabled() {
    $config = getStripeConnectConfig();
    return $config['enabled'] && !empty($config['client_id']);
}

/**
 * Create Stripe Connect Express account for owner
 *
 * @param int $userId Owner user ID
 * @param string $email Owner email
 * @param string $businessName Business name (optional)
 * @return array|null Returns account details or null on failure
 */
function createStripeConnectAccount($userId, $email, $businessName = null) {
    try {
        initStripe();

        // Create Express account
        $account = \Stripe\Account::create([
            'type' => 'express',
            'country' => 'AU',
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'metadata' => [
                'user_id' => $userId,
            ],
        ]);

        // Update user record with Stripe account ID
        db()->execute(
            "UPDATE users SET stripe_account_id = ?, stripe_account_status = 'pending', updated_at = NOW() WHERE id = ?",
            [$account->id, $userId]
        );

        return [
            'account_id' => $account->id,
            'status' => 'pending',
        ];

    } catch (Exception $e) {
        error_log("Error creating Stripe Connect account: " . $e->getMessage());
        return null;
    }
}

/**
 * Create account link for Stripe Connect onboarding
 *
 * @param string $accountId Stripe account ID
 * @param string $returnUrl URL to return after onboarding
 * @param string $refreshUrl URL to refresh if link expires
 * @return string|null Account link URL or null on failure
 */
function createStripeConnectAccountLink($accountId, $returnUrl = null, $refreshUrl = null) {
    try {
        initStripe();

        $config = getStripeConnectConfig();
        $returnUrl = $returnUrl ?? $config['return_url'] ?? ($_SERVER['HTTP_HOST'] . '/owner/stripe/return');
        $refreshUrl = $refreshUrl ?? $config['refresh_url'] ?? ($_SERVER['HTTP_HOST'] . '/owner/stripe/refresh');

        $accountLink = \Stripe\AccountLink::create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);

        return $accountLink->url;

    } catch (Exception $e) {
        error_log("Error creating account link: " . $e->getMessage());
        return null;
    }
}

/**
 * Get Stripe Connect account status
 *
 * @param string $accountId Stripe account ID
 * @return array|null Account status details or null on failure
 */
function getStripeConnectAccountStatus($accountId) {
    try {
        initStripe();

        $account = \Stripe\Account::retrieve($accountId);

        return [
            'account_id' => $account->id,
            'details_submitted' => $account->details_submitted,
            'charges_enabled' => $account->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled,
            'requirements' => $account->requirements->currently_due ?? [],
        ];

    } catch (Exception $e) {
        error_log("Error retrieving account status: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user's Stripe Connect account status in database
 *
 * @param int $userId User ID
 * @param array $accountStatus Status from getStripeConnectAccountStatus()
 * @return bool Success
 */
function updateUserStripeConnectStatus($userId, $accountStatus) {
    try {
        $status = 'pending';
        if ($accountStatus['charges_enabled'] && $accountStatus['payouts_enabled']) {
            $status = 'verified';
        }

        db()->execute(
            "UPDATE users SET
                stripe_account_status = ?,
                stripe_onboarding_completed = ?,
                stripe_details_submitted = ?,
                stripe_charges_enabled = ?,
                stripe_payouts_enabled = ?,
                updated_at = NOW()
            WHERE id = ?",
            [
                $status,
                $accountStatus['details_submitted'] ? 1 : 0,
                $accountStatus['details_submitted'] ? 1 : 0,
                $accountStatus['charges_enabled'] ? 1 : 0,
                $accountStatus['payouts_enabled'] ? 1 : 0,
                $userId
            ]
        );

        return true;

    } catch (Exception $e) {
        error_log("Error updating user Stripe status: " . $e->getMessage());
        return false;
    }
}

/**
 * Create destination charge with automatic split to owner
 *
 * @param float $amount Total amount in dollars
 * @param string $ownerAccountId Owner's Stripe Connect account ID
 * @param string $description Payment description
 * @param array $metadata Additional metadata
 * @return array|null Charge details or null on failure
 */
function createDestinationCharge($amount, $ownerAccountId, $description, $metadata = []) {
    try {
        initStripe();

        // Calculate commission (15%)
        $commissionRate = 0.15;
        $ownerAmount = $amount * (1 - $commissionRate);
        $commissionAmount = $amount * $commissionRate;

        $charge = \Stripe\Charge::create([
            'amount' => stripeAmount($amount),
            'currency' => 'aud',
            'description' => $description,
            'transfer_data' => [
                'destination' => $ownerAccountId,
                'amount' => stripeAmount($ownerAmount), // 85% to owner
            ],
            'metadata' => array_merge($metadata, [
                'commission_amount' => $commissionAmount,
                'owner_amount' => $ownerAmount,
            ]),
        ]);

        return [
            'charge_id' => $charge->id,
            'amount' => $amount,
            'owner_amount' => $ownerAmount,
            'commission_amount' => $commissionAmount,
            'transfer_id' => $charge->transfer ?? null,
        ];

    } catch (Exception $e) {
        error_log("Error creating destination charge: " . $e->getMessage());
        return null;
    }
}

/**
 * Create manual transfer to owner (for bookings paid before Connect was enabled)
 *
 * @param float $amount Amount to transfer in dollars
 * @param string $ownerAccountId Owner's Stripe Connect account ID
 * @param string $description Transfer description
 * @param array $metadata Additional metadata
 * @return array|null Transfer details or null on failure
 */
function createManualTransfer($amount, $ownerAccountId, $description, $metadata = []) {
    try {
        initStripe();

        $transfer = \Stripe\Transfer::create([
            'amount' => stripeAmount($amount),
            'currency' => 'aud',
            'destination' => $ownerAccountId,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        return [
            'transfer_id' => $transfer->id,
            'amount' => $amount,
            'status' => $transfer->status,
        ];

    } catch (Exception $e) {
        error_log("Error creating manual transfer: " . $e->getMessage());
        return null;
    }
}
