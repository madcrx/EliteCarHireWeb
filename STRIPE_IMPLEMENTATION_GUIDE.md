# Stripe Payment Integration Guide
## Elite Car Hire - Complete Implementation Steps

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Stripe Account Setup](#stripe-account-setup)
3. [Database Setup](#database-setup)
4. [System Configuration Integration](#system-configuration-integration)
5. [Backend Implementation](#backend-implementation)
6. [Frontend Implementation](#frontend-implementation)
7. [Webhook Configuration](#webhook-configuration)
8. [Testing](#testing)
9. [Going Live](#going-live)

---

## 1. Prerequisites

### Required
- Stripe Account (https://stripe.com)
- PHP 7.4 or higher
- Composer (for Stripe PHP SDK)
- SSL Certificate (required for production)

### Install Stripe PHP SDK
```bash
cd /path/to/EliteCarHireWeb
composer require stripe/stripe-php
```

---

## 2. Stripe Account Setup

### Step 1: Create Stripe Account
1. Go to https://dashboard.stripe.com/register
2. Complete registration with business details
3. Verify your email address
4. Complete business verification (required for live payments)

### Step 2: Get API Keys
1. Navigate to **Developers → API Keys** in Stripe Dashboard
2. Copy your keys:
   - **Test Mode:**
     - Publishable key: `pk_test_...`
     - Secret key: `sk_test_...`
   - **Live Mode:**
     - Publishable key: `pk_live_...`
     - Secret key: `sk_live_...`

### Step 3: Configure Webhook Endpoint (do this later after deployment)
- Endpoint URL: `https://yourdomain.com/webhook/stripe`
- Events to listen for:
  - `payment_intent.succeeded`
  - `payment_intent.payment_failed`
  - `charge.refunded`
  - `customer.created`

---

## 3. Database Setup

### Add Stripe Configuration to Settings Table
```sql
-- Run this in phpMyAdmin or MySQL console
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('stripe_mode', 'test', 'payment'),
('stripe_test_publishable_key', '', 'payment'),
('stripe_test_secret_key', '', 'payment'),
('stripe_live_publishable_key', '', 'payment'),
('stripe_live_secret_key', '', 'payment'),
('stripe_webhook_secret', '', 'payment'),
('stripe_currency', 'AUD', 'payment'),
('stripe_enabled', '1', 'payment');
```

### Create Payment Intent Tracking Table
```sql
CREATE TABLE IF NOT EXISTS stripe_payment_intents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    payment_intent_id VARCHAR(255) NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'AUD',
    status VARCHAR(50),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_payment_intent (payment_intent_id),
    INDEX idx_booking (booking_id)
) ENGINE=InnoDB;
```

---

## 4. System Configuration Integration

### Create Payment Settings Page

**File:** `/app/views/admin/settings/payment.php`

```php
<?php ob_start(); ?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-credit-card"></i> Payment Settings</h1>

        <form method="POST" action="/admin/settings/payment/save">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="card">
                <h3>Stripe Configuration</h3>

                <div class="form-group">
                    <label>Payment Mode</label>
                    <select name="stripe_mode">
                        <option value="test" <?= ($settings['stripe_mode'] ?? 'test') === 'test' ? 'selected' : '' ?>>
                            Test Mode
                        </option>
                        <option value="live" <?= ($settings['stripe_mode'] ?? '') === 'live' ? 'selected' : '' ?>>
                            Live Mode
                        </option>
                    </select>
                    <small>Always use Test Mode until fully tested</small>
                </div>

                <div class="form-group">
                    <label>Test Publishable Key</label>
                    <input type="text" name="stripe_test_publishable_key"
                           value="<?= e($settings['stripe_test_publishable_key'] ?? '') ?>"
                           placeholder="pk_test_...">
                </div>

                <div class="form-group">
                    <label>Test Secret Key</label>
                    <input type="password" name="stripe_test_secret_key"
                           value="<?= e($settings['stripe_test_secret_key'] ?? '') ?>"
                           placeholder="sk_test_...">
                </div>

                <div class="form-group">
                    <label>Live Publishable Key</label>
                    <input type="text" name="stripe_live_publishable_key"
                           value="<?= e($settings['stripe_live_publishable_key'] ?? '') ?>"
                           placeholder="pk_live_...">
                </div>

                <div class="form-group">
                    <label>Live Secret Key</label>
                    <input type="password" name="stripe_live_secret_key"
                           value="<?= e($settings['stripe_live_secret_key'] ?? '') ?>"
                           placeholder="sk_live_...">
                </div>

                <div class="form-group">
                    <label>Webhook Secret</label>
                    <input type="password" name="stripe_webhook_secret"
                           value="<?= e($settings['stripe_webhook_secret'] ?? '') ?>"
                           placeholder="whsec_...">
                    <small>Get this from Stripe Dashboard → Webhooks</small>
                </div>

                <div class="form-group">
                    <label>Currency</label>
                    <select name="stripe_currency">
                        <option value="AUD" <?= ($settings['stripe_currency'] ?? 'AUD') === 'AUD' ? 'selected' : '' ?>>
                            AUD - Australian Dollar
                        </option>
                        <option value="USD" <?= ($settings['stripe_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>
                            USD - US Dollar
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
```

### Update AdminController

**File:** `/app/controllers/AdminController.php`

Add these methods:

```php
public function paymentSettings() {
    requireAuth('admin');

    // Fetch all payment settings
    $settingsData = db()->fetchAll("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'payment'");
    $settings = [];
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }

    view('admin/settings/payment', compact('settings'));
}

public function savePaymentSettings() {
    requireAuth('admin');

    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($token)) {
        flash('error', 'Invalid security token.');
        redirect('/admin/settings/payment');
    }

    $paymentSettings = [
        'stripe_mode',
        'stripe_test_publishable_key',
        'stripe_test_secret_key',
        'stripe_live_publishable_key',
        'stripe_live_secret_key',
        'stripe_webhook_secret',
        'stripe_currency'
    ];

    foreach ($paymentSettings as $key) {
        $value = $_POST[$key] ?? '';

        $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            db()->execute("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            db()->execute("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'payment')",
                         [$key, $value]);
        }
    }

    logAudit('update_payment_settings', 'settings', null);
    flash('success', 'Payment settings updated successfully');
    redirect('/admin/settings/payment');
}
```

### Add Routes

**File:** `/public/index.php`

```php
$router->get('/admin/settings/payment', 'AdminController@paymentSettings');
$router->post('/admin/settings/payment/save', 'AdminController@savePaymentSettings');
```

---

## 5. Backend Implementation

### Create Stripe Helper Class

**File:** `/app/helpers/StripeHelper.php`

```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class StripeHelper {
    private static $stripe;

    public static function init() {
        if (self::$stripe !== null) {
            return;
        }

        $mode = self::getSetting('stripe_mode', 'test');
        $secretKey = $mode === 'live'
            ? self::getSetting('stripe_live_secret_key')
            : self::getSetting('stripe_test_secret_key');

        \Stripe\Stripe::setApiKey($secretKey);
        self::$stripe = true;
    }

    public static function createPaymentIntent($bookingId, $amount, $description) {
        self::init();

        $currency = self::getSetting('stripe_currency', 'AUD');

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'description' => $description,
                'metadata' => [
                    'booking_id' => $bookingId
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Store payment intent
            db()->execute(
                "INSERT INTO stripe_payment_intents (booking_id, payment_intent_id, client_secret, amount, currency, status, metadata)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$bookingId, $paymentIntent->id, $paymentIntent->client_secret, $amount, $currency,
                 $paymentIntent->status, json_encode($paymentIntent->metadata)]
            );

            return $paymentIntent;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getPublishableKey() {
        $mode = self::getSetting('stripe_mode', 'test');
        return $mode === 'live'
            ? self::getSetting('stripe_live_publishable_key')
            : self::getSetting('stripe_test_publishable_key');
    }

    private static function getSetting($key, $default = '') {
        $result = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return $result ? $result['setting_value'] : $default;
    }
}
```

### Update BookingController for Payment

**File:** `/app/controllers/BookingController.php`

Add payment creation after booking confirmation:

```php
public function initiatePayment($bookingId) {
    requireAuth();

    $userId = $_SESSION['user_id'];

    // Get booking
    $booking = db()->fetch(
        "SELECT b.*, v.make, v.model FROM bookings b
         JOIN vehicles v ON b.vehicle_id = v.id
         WHERE b.id = ? AND b.customer_id = ?",
        [$bookingId, $userId]
    );

    if (!$booking || $booking['status'] !== 'confirmed') {
        flash('error', 'Booking not found or not ready for payment');
        redirect('/customer/hires');
    }

    // Check if payment already exists
    $existingPayment = db()->fetch(
        "SELECT * FROM stripe_payment_intents WHERE booking_id = ? AND status != 'canceled'",
        [$bookingId]
    );

    if ($existingPayment) {
        $clientSecret = $existingPayment['client_secret'];
    } else {
        require_once __DIR__ . '/../helpers/StripeHelper.php';

        $description = "Booking {$booking['booking_reference']} - {$booking['make']} {$booking['model']}";
        $paymentIntent = StripeHelper::createPaymentIntent($bookingId, $booking['total_amount'], $description);

        if (!$paymentIntent) {
            flash('error', 'Unable to create payment. Please try again.');
            redirect('/customer/hires');
        }

        $clientSecret = $paymentIntent->client_secret;
    }

    view('customer/payment', compact('booking', 'clientSecret'));
}
```

---

## 6. Frontend Implementation

### Create Payment Page

**File:** `/app/views/customer/payment.php`

```php
<?php
require_once __DIR__ . '/../helpers/StripeHelper.php';
$publishableKey = StripeHelper::getPublishableKey();
ob_start();
?>
<div class="sidebar-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <h1><i class="fas fa-credit-card"></i> Complete Payment</h1>

        <div class="card">
            <h3>Booking Details</h3>
            <p><strong>Reference:</strong> <?= e($booking['booking_reference']) ?></p>
            <p><strong>Vehicle:</strong> <?= e($booking['make'] . ' ' . $booking['model']) ?></p>
            <p><strong>Date:</strong> <?= date('M d, Y', strtotime($booking['booking_date'])) ?></p>
            <p><strong>Amount:</strong> $<?= number_format($booking['total_amount'], 2) ?></p>
        </div>

        <div class="card">
            <h3>Payment Information</h3>
            <form id="payment-form">
                <div id="payment-element"></div>
                <div id="payment-message" class="alert" style="display: none; margin-top: 1rem;"></div>
                <button type="submit" id="submit-btn" class="btn btn-primary" style="margin-top: 1rem; width: 100%;">
                    <span id="button-text">Pay $<?= number_format($booking['total_amount'], 2) ?></span>
                    <span id="spinner" style="display: none;">Processing...</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('<?= $publishableKey ?>');
const clientSecret = '<?= $clientSecret ?>';

const elements = stripe.elements({ clientSecret });
const paymentElement = elements.create('payment');
paymentElement.mount('#payment-element');

const form = document.getElementById('payment-form');
const submitBtn = document.getElementById('submit-btn');
const buttonText = document.getElementById('button-text');
const spinner = document.getElementById('spinner');
const messageDiv = document.getElementById('payment-message');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    submitBtn.disabled = true;
    buttonText.style.display = 'none';
    spinner.style.display = 'inline';

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: window.location.origin + '/customer/payment/success?booking_id=<?= $booking['id'] ?>',
        },
    });

    if (error) {
        messageDiv.textContent = error.message;
        messageDiv.className = 'alert alert-error';
        messageDiv.style.display = 'block';

        submitBtn.disabled = false;
        buttonText.style.display = 'inline';
        spinner.style.display = 'none';
    }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
```

### Add Payment Success Handler

**File:** `/app/controllers/CustomerController.php`

```php
public function paymentSuccess() {
    requireAuth();

    $bookingId = $_GET['booking_id'] ?? 0;
    $paymentIntentId = $_GET['payment_intent'] ?? '';

    if (!$paymentIntentId) {
        flash('error', 'Invalid payment');
        redirect('/customer/hires');
    }

    // Update booking payment status
    db()->execute(
        "UPDATE bookings SET payment_status = 'paid' WHERE id = ?",
        [$bookingId]
    );

    // Update payment intent status
    db()->execute(
        "UPDATE stripe_payment_intents SET status = 'succeeded' WHERE payment_intent_id = ?",
        [$paymentIntentId]
    );

    flash('success', 'Payment successful! Your booking is confirmed.');
    redirect('/customer/hires');
}
```

---

## 7. Webhook Configuration

### Create Webhook Handler

**File:** `/app/controllers/WebhookController.php`

```php
<?php
namespace controllers;

class WebhookController {
    public function stripeWebhook() {
        require_once __DIR__ . '/../helpers/StripeHelper.php';

        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        $webhookSecret = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_webhook_secret'");
        $webhookSecret = $webhookSecret['setting_value'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSuccess($paymentIntent);
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handlePaymentFailure($paymentIntent);
                break;
        }

        http_response_code(200);
        echo json_encode(['status' => 'success']);
    }

    private function handlePaymentSuccess($paymentIntent) {
        db()->execute(
            "UPDATE stripe_payment_intents SET status = 'succeeded' WHERE payment_intent_id = ?",
            [$paymentIntent->id]
        );

        $booking = db()->fetch(
            "SELECT booking_id FROM stripe_payment_intents WHERE payment_intent_id = ?",
            [$paymentIntent->id]
        );

        if ($booking) {
            db()->execute(
                "UPDATE bookings SET payment_status = 'paid' WHERE id = ?",
                [$booking['booking_id']]
            );
        }
    }

    private function handlePaymentFailure($paymentIntent) {
        db()->execute(
            "UPDATE stripe_payment_intents SET status = 'failed' WHERE payment_intent_id = ?",
            [$paymentIntent->id]
        );
    }
}
```

### Add Webhook Route

**File:** `/public/index.php`

```php
$router->post('/webhook/stripe', 'WebhookController@stripeWebhook');
```

---

## 8. Testing

### Test Mode Cards
Use these test cards in Stripe Test Mode:

| Card Number         | Result             |
|--------------------|--------------------|
| 4242 4242 4242 4242 | Success            |
| 4000 0000 0000 0002 | Card Declined      |
| 4000 0000 0000 9995 | Insufficient Funds |

- Any future expiry date (e.g., 12/34)
- Any 3-digit CVC
- Any billing ZIP code

### Testing Checklist
- [ ] Payment settings page loads
- [ ] Can save Stripe test keys
- [ ] Payment form displays correctly
- [ ] Successful payment redirects properly
- [ ] Failed payment shows error message
- [ ] Booking status updates after payment
- [ ] Webhook receives events
- [ ] Payment logs are created

---

## 9. Going Live

### Pre-Launch Checklist
- [ ] Complete Stripe account verification
- [ ] Enter live API keys in System Configuration
- [ ] Set webhook URL in Stripe Dashboard
- [ ] Switch to Live Mode in payment settings
- [ ] Test with real card (small amount)
- [ ] Ensure SSL certificate is active
- [ ] Review and test refund process
- [ ] Set up email notifications for payments
- [ ] Configure payout schedule in Stripe

### Go Live Steps
1. In Admin → Settings → Payment Settings:
   - Enter Live Publishable Key
   - Enter Live Secret Key
   - Change Mode to "Live"
   - Save settings

2. In Stripe Dashboard:
   - Go to Developers → Webhooks
   - Add endpoint: `https://yourdomain.com/webhook/stripe`
   - Select events and save
   - Copy webhook signing secret
   - Add to payment settings

3. Test small transaction
4. Monitor first few payments closely

---

## Support & Resources

- **Stripe Documentation:** https://stripe.com/docs
- **Stripe Dashboard:** https://dashboard.stripe.com
- **Test Cards:** https://stripe.com/docs/testing
- **Webhooks:** https://stripe.com/docs/webhooks

---

## Security Notes

1. **NEVER** commit API keys to version control
2. Always use environment variables or database for keys
3. Validate webhook signatures
4. Use HTTPS in production
5. Log all payment activities
6. Regularly update Stripe PHP SDK
7. Monitor Stripe Dashboard for suspicious activity

---

**Implementation Complete!** Follow these steps carefully and test thoroughly before going live.
