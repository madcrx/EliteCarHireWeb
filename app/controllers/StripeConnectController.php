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
        try {
            $ownerId = $_SESSION['user_id'];
            error_log("StripeConnectController::connect() - Owner ID: " . $ownerId);

            // Check if already connected
            $owner = db()->fetch("SELECT stripe_account_id, email, first_name, last_name FROM users WHERE id = ?", [$ownerId]);

            if (!$owner) {
                error_log("StripeConnectController::connect() - Owner not found");
                flash('error', 'User account not found.');
                redirect('/owner/dashboard');
                return;
            }

            if (!empty($owner['stripe_account_id'])) {
                error_log("StripeConnectController::connect() - Already connected: " . $owner['stripe_account_id']);
                flash('info', 'Your Stripe account is already connected!');
                redirect('/owner/dashboard');
                return;
            }

            // Get Stripe settings from database
            $stripeConnectEnabled = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_enabled'");
            $clientId = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_client_id'");

            error_log("StripeConnectController::connect() - Enabled: " . json_encode($stripeConnectEnabled));
            error_log("StripeConnectController::connect() - Client ID exists: " . (!empty($clientId['setting_value']) ? 'Yes' : 'No'));

            if (empty($stripeConnectEnabled['setting_value']) || $stripeConnectEnabled['setting_value'] != '1') {
                error_log("StripeConnectController::connect() - Stripe Connect not enabled");
                flash('error', 'Stripe Connect is not enabled. Please contact support.');
                redirect('/owner/dashboard');
                return;
            }

            if (empty($clientId['setting_value'])) {
                error_log("StripeConnectController::connect() - Client ID not configured");
                flash('error', 'Stripe Connect is not properly configured. Please contact support.');
                redirect('/owner/dashboard');
                return;
            }

            // Get return and refresh URLs from settings
            $returnUrlSetting = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_onboarding_return_url'");
            $refreshUrlSetting = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_connect_onboarding_refresh_url'");

            // Use configured URLs or fallback
            $returnUrl = !empty($returnUrlSetting['setting_value']) ? $returnUrlSetting['setting_value'] : 'https://elitecarhire.au/owner/stripe/return';
            $refreshUrl = !empty($refreshUrlSetting['setting_value']) ? $refreshUrlSetting['setting_value'] : 'https://elitecarhire.au/owner/stripe/refresh';

            // Generate state token for CSRF protection
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $state = $_SESSION['csrf_token'];

            error_log("StripeConnectController::connect() - Return URL: " . $returnUrl);

            // Build Stripe Connect OAuth URL
            $params = [
                'client_id' => $clientId['setting_value'],
                'state' => $state,
                'stripe_user[email]' => $owner['email'],
                'stripe_user[first_name]' => $owner['first_name'] ?? '',
                'stripe_user[last_name]' => $owner['last_name'] ?? '',
                'stripe_user[business_type]' => 'individual',
                'stripe_user[country]' => 'AU',
                'redirect_uri' => $returnUrl,
                'suggested_capabilities[]' => 'transfers',
            ];

            $connectUrl = 'https://connect.stripe.com/express/oauth/authorize?' . http_build_query($params);

            error_log("StripeConnectController::connect() - Redirecting to Stripe");

            // Log the connection attempt
            logAudit('stripe_connect_initiated', 'users', $ownerId);

            // Redirect to Stripe Connect
            header('Location: ' . $connectUrl);
            exit;

        } catch (\Exception $e) {
            error_log("StripeConnectController::connect() - Exception: " . $e->getMessage());
            error_log("StripeConnectController::connect() - Stack trace: " . $e->getTraceAsString());
            flash('error', 'An error occurred while initiating Stripe Connect. Please try again.');
            redirect('/owner/dashboard');
        }
    }

    /**
     * Handle return from Stripe Connect onboarding
     */
    public function connectReturn() {
        try {
            $ownerId = $_SESSION['user_id'];
            $code = $_GET['code'] ?? '';
            $state = $_GET['state'] ?? '';
            $error = $_GET['error'] ?? '';
            $errorDescription = $_GET['error_description'] ?? '';

            error_log("StripeConnectController::connectReturn() - Owner ID: " . $ownerId);
            error_log("StripeConnectController::connectReturn() - Has code: " . (!empty($code) ? 'Yes' : 'No'));
            error_log("StripeConnectController::connectReturn() - Has error: " . (!empty($error) ? $error : 'No'));

            // Check for errors from Stripe
            if (!empty($error)) {
                error_log("Stripe Connect error for user $ownerId: $error - $errorDescription");
                flash('error', 'Failed to connect Stripe account: ' . ($errorDescription ?: $error));
                redirect('/owner/dashboard');
                return;
            }

            // Verify state to prevent CSRF - compare with session token
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            if (empty($state) || empty($sessionToken) || $state !== $sessionToken) {
                error_log("StripeConnectController::connectReturn() - CSRF validation failed");
                flash('error', 'Invalid security token. Please try again.');
                redirect('/owner/dashboard');
                return;
            }

            if (empty($code)) {
                error_log("StripeConnectController::connectReturn() - No authorization code");
                flash('error', 'No authorization code received from Stripe.');
                redirect('/owner/dashboard');
                return;
            }

            // Get Stripe secret key from environment
            $stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: $_ENV['STRIPE_SECRET_KEY'] ?? '';

            if (empty($stripeSecretKey)) {
                error_log("StripeConnectController::connectReturn() - Stripe secret key not configured");
                flash('error', 'Stripe secret key not configured. Please contact support.');
                redirect('/owner/dashboard');
                return;
            }

            error_log("StripeConnectController::connectReturn() - Exchanging authorization code");

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
            $curlError = curl_error($ch);
            curl_close($ch);

            error_log("StripeConnectController::connectReturn() - Stripe response code: " . $httpCode);

            if ($httpCode !== 200) {
                error_log("StripeConnectController::connectReturn() - Stripe API error: HTTP $httpCode, Response: $response");
                flash('error', 'Failed to complete Stripe connection. Please try again.');
                redirect('/owner/dashboard');
                return;
            }

            if ($curlError) {
                error_log("StripeConnectController::connectReturn() - cURL error: " . $curlError);
                flash('error', 'Network error connecting to Stripe. Please try again.');
                redirect('/owner/dashboard');
                return;
            }

            $result = json_decode($response, true);

            if (empty($result['stripe_user_id'])) {
                error_log("StripeConnectController::connectReturn() - No stripe_user_id in response: " . $response);
                flash('error', 'Invalid response from Stripe. Please try again.');
                redirect('/owner/dashboard');
                return;
            }

            $stripeAccountId = $result['stripe_user_id'];

            error_log("StripeConnectController::connectReturn() - Saving Stripe account ID: " . $stripeAccountId);

            // Save Stripe account ID to database
            db()->execute(
                "UPDATE users SET stripe_account_id = ?, updated_at = NOW() WHERE id = ?",
                [$stripeAccountId, $ownerId]
            );

            logAudit('stripe_connect_completed', 'users', $ownerId, [
                'stripe_account_id' => $stripeAccountId
            ]);

            error_log("StripeConnectController::connectReturn() - Success!");

            flash('success', 'Your Stripe account has been connected successfully! You can now confirm bookings and receive payouts.');
            redirect('/owner/dashboard');

        } catch (\Exception $e) {
            error_log("StripeConnectController::connectReturn() - Exception: " . $e->getMessage());
            error_log("StripeConnectController::connectReturn() - Stack trace: " . $e->getTraceAsString());
            flash('error', 'An error occurred while completing Stripe connection. Please try again.');
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
