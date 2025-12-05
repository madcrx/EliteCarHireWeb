<?php
namespace controllers;

class StripeConnectController {
    public function __construct() {
        requireAuth('owner');
    }

    /**
     * Initiate Stripe Connect onboarding
     */
    public function connect() {
        $ownerId = $_SESSION['user_id'];

        // Check if already connected
        $owner = db()->fetch("SELECT stripe_account_id, email, first_name, last_name FROM users WHERE id = ?", [$ownerId]);

        if (!empty($owner['stripe_account_id'])) {
            flash('info', 'Your Stripe account is already connected!');
            redirect('/owner/dashboard');
        }

        // Get Stripe settings from database
        $stripeConnectEnabled = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_enabled'");
        $clientId = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_client_id'");

        if (empty($stripeConnectEnabled['setting_value']) || $stripeConnectEnabled['setting_value'] != '1') {
            flash('error', 'Stripe Connect is not enabled. Please contact support.');
            redirect('/owner/dashboard');
        }

        if (empty($clientId['setting_value'])) {
            flash('error', 'Stripe Connect is not properly configured. Please contact support.');
            redirect('/owner/dashboard');
        }

        // Get return and refresh URLs from settings
        $returnUrlSetting = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_onboarding_return_url'");
        $refreshUrlSetting = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_onboarding_refresh_url'");

        $returnUrl = !empty($returnUrlSetting['setting_value']) ? $returnUrlSetting['setting_value'] : config('app.url') . '/owner/stripe/return';
        $refreshUrl = !empty($refreshUrlSetting['setting_value']) ? $refreshUrlSetting['setting_value'] : config('app.url') . '/owner/stripe/refresh';

        // Build Stripe Connect OAuth URL
        $params = [
            'client_id' => $clientId['setting_value'],
            'state' => generateCsrfToken(), // Use CSRF token as state for security
            'stripe_user[email]' => $owner['email'],
            'stripe_user[first_name]' => $owner['first_name'],
            'stripe_user[last_name]' => $owner['last_name'],
            'stripe_user[business_type]' => 'individual',
            'stripe_user[country]' => 'AU',
            'redirect_uri' => $returnUrl,
            'suggested_capabilities[]' => 'transfers',
        ];

        $connectUrl = 'https://connect.stripe.com/express/oauth/authorize?' . http_build_query($params);

        // Log the connection attempt
        logAudit('stripe_connect_initiated', 'users', $ownerId);

        // Redirect to Stripe Connect
        header('Location: ' . $connectUrl);
        exit;
    }

    /**
     * Handle return from Stripe Connect onboarding
     */
    public function connectReturn() {
        $ownerId = $_SESSION['user_id'];
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        $error = $_GET['error'] ?? '';
        $errorDescription = $_GET['error_description'] ?? '';

        // Check for errors
        if (!empty($error)) {
            error_log("Stripe Connect error for user $ownerId: $error - $errorDescription");
            flash('error', 'Failed to connect Stripe account: ' . ($errorDescription ?: $error));
            redirect('/owner/dashboard');
        }

        // Verify state to prevent CSRF
        if (!verifyCsrf($state)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/dashboard');
        }

        if (empty($code)) {
            flash('error', 'No authorization code received from Stripe.');
            redirect('/owner/dashboard');
        }

        // Exchange authorization code for account ID
        try {
            // Get Stripe secret key from environment
            $stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: $_ENV['STRIPE_SECRET_KEY'] ?? '';

            if (empty($stripeSecretKey)) {
                throw new \Exception('Stripe secret key not configured');
            }

            // Get client secret from settings
            $clientSecret = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_client_secret'");

            // Make request to Stripe to exchange code for account ID
            $ch = curl_init('https://connect.stripe.com/oauth/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]));
            curl_setopt($ch, CURLOPT_USERPWD, $stripeSecretKey . ':');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception('Failed to exchange authorization code: HTTP ' . $httpCode);
            }

            $result = json_decode($response, true);

            if (empty($result['stripe_user_id'])) {
                throw new \Exception('No Stripe account ID received');
            }

            $stripeAccountId = $result['stripe_user_id'];

            // Save Stripe account ID to database
            db()->execute(
                "UPDATE users SET stripe_account_id = ?, updated_at = NOW() WHERE id = ?",
                [$stripeAccountId, $ownerId]
            );

            logAudit('stripe_connect_completed', 'users', $ownerId, [
                'stripe_account_id' => $stripeAccountId
            ]);

            flash('success', 'Your Stripe account has been connected successfully! You can now confirm bookings and receive payouts.');
            redirect('/owner/dashboard');

        } catch (\Exception $e) {
            error_log("Stripe Connect exchange error for user $ownerId: " . $e->getMessage());
            flash('error', 'Failed to complete Stripe connection: ' . $e->getMessage());
            redirect('/owner/dashboard');
        }
    }

    /**
     * Handle refresh/retry of Stripe Connect onboarding
     */
    public function connectRefresh() {
        $ownerId = $_SESSION['user_id'];

        flash('info', 'Let\'s try connecting your Stripe account again.');
        redirect('/owner/stripe/connect');
    }

    /**
     * Disconnect Stripe account
     */
    public function disconnect() {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/dashboard');
        }

        $ownerId = $_SESSION['user_id'];

        // Check if owner has any active bookings
        $activeBookings = db()->fetch(
            "SELECT COUNT(*) as count FROM bookings
             WHERE owner_id = ? AND status IN ('confirmed', 'in_progress')",
            [$ownerId]
        );

        if ($activeBookings['count'] > 0) {
            flash('error', 'You cannot disconnect Stripe while you have active bookings.');
            redirect('/owner/dashboard');
        }

        // Remove Stripe account ID
        db()->execute(
            "UPDATE users SET stripe_account_id = NULL, updated_at = NOW() WHERE id = ?",
            [$ownerId]
        );

        logAudit('stripe_disconnect', 'users', $ownerId);

        flash('success', 'Your Stripe account has been disconnected.');
        redirect('/owner/dashboard');
    }
}
