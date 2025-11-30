<?php
namespace controllers;

require_once __DIR__ . '/../helpers/stripe_helper.php';

class AdminStripeController {
    public function __construct() {
        requireAuth('admin');
    }

    /**
     * Display Stripe settings page
     */
    public function index() {
        $stripeMode = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_mode'");
        $paymentGateway = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'payment_gateway'");

        $stripeTestPublishableKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_test_publishable_key'");
        $stripeTestSecretKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_test_secret_key'");

        $stripeLivePublishableKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_live_publishable_key'");
        $stripeLiveSecretKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_live_secret_key'");

        $stripeWebhookSecret = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_webhook_secret'");

        $data = [
            'stripeMode' => $stripeMode['setting_value'] ?? 'test',
            'paymentGateway' => $paymentGateway['setting_value'] ?? 'stripe',
            'stripeTestPublishableKey' => $stripeTestPublishableKey['setting_value'] ?? '',
            'stripeTestSecretKey' => $stripeTestSecretKey['setting_value'] ?? '',
            'stripeLivePublishableKey' => $stripeLivePublishableKey['setting_value'] ?? '',
            'stripeLiveSecretKey' => $stripeLiveSecretKey['setting_value'] ?? '',
            'stripeWebhookSecret' => $stripeWebhookSecret['setting_value'] ?? '',
        ];

        extract($data);
        require __DIR__ . '/../views/admin/settings/stripe.php';
    }

    /**
     * Update Stripe settings
     */
    public function update() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/settings/stripe');
        }

        // Get form data
        $settings = [
            'payment_gateway' => $_POST['payment_gateway'] ?? 'stripe',
            'stripe_mode' => $_POST['stripe_mode'] ?? 'test',
            'stripe_test_publishable_key' => trim($_POST['stripe_test_publishable_key'] ?? ''),
            'stripe_test_secret_key' => trim($_POST['stripe_test_secret_key'] ?? ''),
            'stripe_live_publishable_key' => trim($_POST['stripe_live_publishable_key'] ?? ''),
            'stripe_live_secret_key' => trim($_POST['stripe_live_secret_key'] ?? ''),
            'stripe_webhook_secret' => trim($_POST['stripe_webhook_secret'] ?? ''),
        ];

        // Validate Stripe keys if Stripe is selected
        if ($settings['payment_gateway'] === 'stripe') {
            if ($settings['stripe_mode'] === 'live') {
                if (empty($settings['stripe_live_publishable_key']) || empty($settings['stripe_live_secret_key'])) {
                    flash('error', 'Live mode requires both publishable and secret keys.');
                    redirect('/admin/settings/stripe');
                }
            } else {
                if (empty($settings['stripe_test_publishable_key']) || empty($settings['stripe_test_secret_key'])) {
                    flash('error', 'Test mode requires both publishable and secret keys.');
                    redirect('/admin/settings/stripe');
                }
            }
        }

        // Update each setting
        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);

            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, setting_type, created_at)
                              VALUES (?, ?, 'string', NOW())", [$key, $value]);
            }

            logAudit('update_stripe_setting', 'settings', null, [
                'setting_key' => $key,
                'value_length' => strlen($value) // Don't log actual keys
            ]);
        }

        flash('success', 'Stripe settings saved successfully');
        redirect('/admin/settings/stripe');
    }
}
