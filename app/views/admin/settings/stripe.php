<?php ob_start(); ?>
<style>
    .stripe-settings .form-group {
        margin-bottom: 1.5rem;
    }
    .stripe-settings label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--dark-gray);
    }
    .stripe-settings input[type="text"],
    .stripe-settings input[type="password"],
    .stripe-settings select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius);
        font-size: 1rem;
    }
    .stripe-settings .help-text {
        font-size: 0.875rem;
        color: #666;
        margin-top: 0.25rem;
    }
    .stripe-settings .section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
    }
    .stripe-settings .section h3 {
        margin-top: 0;
        color: #333;
    }
    .alert-info {
        background: #d1ecf1;
        border-left: 4px solid #0c5460;
        color: #0c5460;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 4px;
    }
    .key-display {
        font-family: 'Courier New', monospace;
        background: #fff;
        padding: 0.5rem;
        border-radius: 4px;
        word-break: break-all;
    }
    .mode-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    .mode-test {
        background: #fff3cd;
        color: #856404;
    }
    .mode-live {
        background: #d4edda;
        color: #155724;
    }
</style>

<div class="container dashboard stripe-settings">
    <h1>Stripe Payment Settings</h1>

    <div class="alert-info">
        <strong>Stripe Integration:</strong> Configure your Stripe payment gateway settings below. Make sure to use Test keys for development and Live keys for production.
        <br><br>
        <strong>Current Mode: </strong>
        <span class="mode-badge mode-<?= $stripeMode ?>">
            <?= strtoupper($stripeMode) ?> MODE
        </span>
    </div>

    <form method="POST" action="/admin/settings/stripe/update">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <!-- Payment Gateway Selection -->
        <div class="section">
            <h3>Payment Gateway</h3>
            <div class="form-group">
                <label for="payment_gateway">Active Payment Gateway</label>
                <select name="payment_gateway" id="payment_gateway">
                    <option value="stripe" <?= $paymentGateway === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                    <option value="manual" <?= $paymentGateway === 'manual' ? 'selected' : '' ?>>Manual Processing</option>
                </select>
                <p class="help-text">Select Stripe for automatic payment processing or Manual for offline payments.</p>
            </div>
        </div>

        <!-- Stripe Mode Selection -->
        <div class="section">
            <h3>Stripe Mode</h3>
            <div class="form-group">
                <label for="stripe_mode">Operating Mode</label>
                <select name="stripe_mode" id="stripe_mode">
                    <option value="test" <?= $stripeMode === 'test' ? 'selected' : '' ?>>Test Mode</option>
                    <option value="live" <?= $stripeMode === 'live' ? 'selected' : '' ?>>Live Mode</option>
                </select>
                <p class="help-text">
                    <strong>Test Mode:</strong> Use test keys for development and testing.<br>
                    <strong>Live Mode:</strong> Use live keys for production (processes real payments).
                </p>
            </div>
        </div>

        <!-- Test Keys Section -->
        <div class="section">
            <h3>Test Keys <span class="mode-badge mode-test">Development</span></h3>
            <p style="margin-bottom: 1rem; color: #666;">Use these keys for testing and development. No real charges will be processed.</p>

            <div class="form-group">
                <label for="stripe_test_publishable_key">Test Publishable Key</label>
                <input type="text"
                       name="stripe_test_publishable_key"
                       id="stripe_test_publishable_key"
                       value="<?= e($stripeTestPublishableKey) ?>"
                       placeholder="pk_test_...">
                <p class="help-text">Starts with pk_test_. Safe to use in your frontend code.</p>
            </div>

            <div class="form-group">
                <label for="stripe_test_secret_key">Test Secret Key</label>
                <input type="password"
                       name="stripe_test_secret_key"
                       id="stripe_test_secret_key"
                       value="<?= e($stripeTestSecretKey) ?>"
                       placeholder="sk_test_...">
                <p class="help-text">Starts with sk_test_. Keep this secret! Never expose in frontend code.</p>
            </div>
        </div>

        <!-- Live Keys Section -->
        <div class="section">
            <h3>Live Keys <span class="mode-badge mode-live">Production</span></h3>
            <p style="margin-bottom: 1rem; color: #666;">
                <strong style="color: #dc3545;">⚠️ Warning:</strong> These keys process real payments. Only use in production.
            </p>

            <div class="form-group">
                <label for="stripe_live_publishable_key">Live Publishable Key</label>
                <input type="text"
                       name="stripe_live_publishable_key"
                       id="stripe_live_publishable_key"
                       value="<?= e($stripeLivePublishableKey) ?>"
                       placeholder="pk_live_...">
                <p class="help-text">Starts with pk_live_. Safe to use in your frontend code.</p>
            </div>

            <div class="form-group">
                <label for="stripe_live_secret_key">Live Secret Key</label>
                <input type="password"
                       name="stripe_live_secret_key"
                       id="stripe_live_secret_key"
                       value="<?= e($stripeLiveSecretKey) ?>"
                       placeholder="sk_live_...">
                <p class="help-text">Starts with sk_live_. Keep this secret! Never expose in frontend code.</p>
            </div>
        </div>

        <!-- Webhook Section -->
        <div class="section">
            <h3>Webhook Configuration</h3>
            <p style="margin-bottom: 1rem; color: #666;">
                Webhooks allow Stripe to notify your application about payment events.
            </p>

            <div class="form-group">
                <label for="stripe_webhook_secret">Webhook Signing Secret</label>
                <input type="password"
                       name="stripe_webhook_secret"
                       id="stripe_webhook_secret"
                       value="<?= e($stripeWebhookSecret) ?>"
                       placeholder="whsec_...">
                <p class="help-text">Starts with whsec_. Used to verify webhook authenticity.</p>
            </div>

            <div style="background: #fff; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                <p style="margin: 0; font-weight: 600;">Webhook Endpoint URL:</p>
                <p class="key-display" style="margin: 0.5rem 0 0 0;">
                    <?= config('app.url') ?>/webhook/stripe
                </p>
                <p class="help-text">Configure this URL in your Stripe Dashboard → Developers → Webhooks</p>
            </div>
        </div>

        <!-- Test Card Information -->
        <div class="section" style="background: #e7f3ff; border: 2px solid #0066cc;">
            <h3>Test Card Numbers</h3>
            <p style="margin-bottom: 1rem;">Use these test cards when in Test Mode:</p>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 0.75rem; text-align: left;">Card Number</th>
                        <th style="padding: 0.75rem; text-align: left;">Result</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 0.75rem;"><code>4242 4242 4242 4242</code></td>
                        <td style="padding: 0.75rem; color: #28a745;"><strong>✓ Success</strong></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 0.75rem;"><code>4000 0025 0000 3155</code></td>
                        <td style="padding: 0.75rem; color: #0066cc;"><strong>Requires 3D Secure</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem;"><code>4000 0000 0000 9995</code></td>
                        <td style="padding: 0.75rem; color: #dc3545;"><strong>✗ Declined</strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="help-text" style="margin-top: 1rem;">
                Use any future expiry date, any 3-digit CVV, and any postal code.
            </p>
        </div>

        <!-- Submit Buttons -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="/admin/settings" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Settings
            </a>
        </div>
    </form>
</div>

<script>
// Show warning when switching to live mode
document.getElementById('stripe_mode')?.addEventListener('change', function(e) {
    if (e.target.value === 'live') {
        if (!confirm('⚠️ WARNING: You are switching to LIVE MODE. Real payments will be processed.\n\nMake sure you have configured your live Stripe keys correctly.\n\nContinue?')) {
            e.target.value = 'test';
        }
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
