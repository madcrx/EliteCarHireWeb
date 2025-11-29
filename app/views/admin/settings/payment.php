<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-credit-card"></i> Payment Settings (Stripe)</h1>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/settings/payment/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h2>Stripe API Configuration</h2>

                <div class="form-group">
                    <label for="stripe_mode">Stripe Mode</label>
                    <select name="stripe_mode" id="stripe_mode" class="form-control">
                        <option value="test" <?= ($stripeMode ?? 'test') === 'test' ? 'selected' : '' ?>>Test Mode</option>
                        <option value="live" <?= ($stripeMode ?? 'test') === 'live' ? 'selected' : '' ?>>Live Mode</option>
                    </select>
                    <small style="color: var(--dark-gray);">Switch between test and live Stripe keys</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Test Mode Keys</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    Use these keys for testing. Get them from: <a href="https://dashboard.stripe.com/test/apikeys" target="_blank">Stripe Test Dashboard</a>
                </small>

                <div class="form-group">
                    <label for="stripe_test_secret_key">Test Secret Key (sk_test_...)</label>
                    <input type="password" name="stripe_test_secret_key" id="stripe_test_secret_key"
                           value="<?= e($stripeTestKey ?? '') ?>" class="form-control"
                           placeholder="sk_test_...">
                </div>

                <div class="form-group">
                    <label for="stripe_test_publishable_key">Test Publishable Key (pk_test_...)</label>
                    <input type="text" name="stripe_test_publishable_key" id="stripe_test_publishable_key"
                           value="<?= e($stripeTestPublishable ?? '') ?>" class="form-control"
                           placeholder="pk_test_...">
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Live Mode Keys</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    Use these keys for production. Get them from: <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Live Dashboard</a>
                </small>

                <div class="form-group">
                    <label for="stripe_live_secret_key">Live Secret Key (sk_live_...)</label>
                    <input type="password" name="stripe_live_secret_key" id="stripe_live_secret_key"
                           value="<?= e($stripeLiveKey ?? '') ?>" class="form-control"
                           placeholder="sk_live_...">
                    <small style="color: #c53030;">⚠️ Keep this secret! Never expose in client-side code</small>
                </div>

                <div class="form-group">
                    <label for="stripe_live_publishable_key">Live Publishable Key (pk_live_...)</label>
                    <input type="text" name="stripe_live_publishable_key" id="stripe_live_publishable_key"
                           value="<?= e($stripeLivePublishable ?? '') ?>" class="form-control"
                           placeholder="pk_live_...">
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Webhook Configuration</h3>
                <small style="color: var(--dark-gray); display: block; margin-bottom: 1rem;">
                    Set up webhooks at: <a href="https://dashboard.stripe.com/webhooks" target="_blank">Stripe Webhooks</a><br>
                    Webhook URL: <code><?= e($_SERVER['HTTP_HOST'] ?? 'your-domain.com') ?>/webhook/stripe</code>
                </small>

                <div class="form-group">
                    <label for="stripe_webhook_secret">Webhook Signing Secret (whsec_...)</label>
                    <input type="password" name="stripe_webhook_secret" id="stripe_webhook_secret"
                           value="<?= e($stripeWebhookSecret ?? '') ?>" class="form-control"
                           placeholder="whsec_...">
                    <small>Required to verify webhook authenticity</small>
                </div>

                <hr>

                <h3 style="margin-top: 1.5rem;">Payment Configuration</h3>

                <div class="form-group">
                    <label for="payment_currency">Currency</label>
                    <select name="payment_currency" id="payment_currency" class="form-control">
                        <option value="AUD" <?= ($paymentCurrency ?? 'AUD') === 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar</option>
                        <option value="USD" <?= ($paymentCurrency ?? 'AUD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                        <option value="EUR" <?= ($paymentCurrency ?? 'AUD') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                        <option value="GBP" <?= ($paymentCurrency ?? 'AUD') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                    </select>
                </div>

                <div style="margin-top: 2rem; padding: 1rem; background: #f7fafc; border-left: 4px solid var(--primary-gold);">
                    <strong>Important Notes:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <li>Always test in Test Mode before going live</li>
                        <li>Never commit API keys to version control</li>
                        <li>Rotate keys immediately if exposed</li>
                        <li>See <code>STRIPE_IMPLEMENTATION_GUIDE.md</code> for full integration instructions</li>
                    </ul>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Payment Settings
                    </button>
                    <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
