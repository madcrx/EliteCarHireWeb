<?php ob_start(); ?>
<div class="container dashboard">
    <h1>Booking Details</h1>

    <div class="card">
        <h2>Booking Reference: <?= e($booking['booking_reference']) ?></h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
            <!-- Left Column - Vehicle & Booking Info -->
            <div>
                <?php if (!empty($images)): ?>
                    <img src="<?= e($images[0]['image_path']) ?>" alt="Vehicle Image"
                         style="width: 100%; border-radius: 8px; margin-bottom: 1rem;">
                <?php endif; ?>

                <h3>Vehicle Information</h3>
                <table style="width: 100%; margin-bottom: 1.5rem;">
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Vehicle:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['year'] . ')') ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Color:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e(ucfirst($booking['color'])) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Category:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e(ucwords(str_replace('_', ' ', $booking['category']))) ?></td>
                    </tr>
                </table>

                <h3>Booking Information</h3>
                <table style="width: 100%; margin-bottom: 1.5rem;">
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Date:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= date('l, F d, Y', strtotime($booking['booking_date'])) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Time:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Duration:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= $booking['duration_hours'] ?> hours</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Pickup Location:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e($booking['pickup_location']) ?></td>
                    </tr>
                    <?php if (!empty($booking['event_type'])): ?>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Event Type:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e(ucwords(str_replace('_', ' ', $booking['event_type']))) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($booking['special_requirements'])): ?>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Special Requirements:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e($booking['special_requirements']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <h3>Owner Contact</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Name:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e($booking['owner_first_name'] . ' ' . $booking['owner_last_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0;"><strong>Phone:</strong></td>
                        <td style="padding: 0.5rem 0;"><?= e($booking['owner_phone']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Right Column - Payment Info & Form -->
            <div>
                <h3>Payment Summary</h3>
                <table style="width: 100%; margin-bottom: 1.5rem;">
                    <tr>
                        <td style="padding: 0.5rem 0;">Base Amount:</td>
                        <td style="padding: 0.5rem 0; text-align: right;"><?= formatMoney($booking['base_amount']) ?></td>
                    </tr>
                    <?php if ($booking['additional_charges'] > 0): ?>
                    <tr>
                        <td style="padding: 0.5rem 0;">Additional Charges:</td>
                        <td style="padding: 0.5rem 0; text-align: right;"><?= formatMoney($booking['additional_charges']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($booking['toll_charges'] > 0): ?>
                    <tr>
                        <td style="padding: 0.5rem 0;">Toll Charges:</td>
                        <td style="padding: 0.5rem 0; text-align: right;"><?= formatMoney($booking['toll_charges']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="border-top: 2px solid #ddd;">
                        <td style="padding: 0.75rem 0;"><strong>Total Amount:</strong></td>
                        <td style="padding: 0.75rem 0; text-align: right;"><strong><?= formatMoney($booking['total_amount']) ?></strong></td>
                    </tr>
                </table>

                <div style="margin-bottom: 1.5rem;">
                    <p><strong>Booking Status:</strong>
                        <span class="badge badge-<?= $booking['status'] === 'completed' ? 'success' : ($booking['status'] === 'confirmed' ? 'success' : 'info') ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </p>
                    <p><strong>Payment Status:</strong>
                        <span class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                            <?= ucfirst($booking['payment_status']) ?>
                        </span>
                    </p>
                </div>

                <?php if ($booking['status'] === 'confirmed' && $booking['payment_status'] !== 'paid'): ?>
                <div class="card" style="background-color: #fff9e6; border: 2px solid #ffd700;">
                    <h3 style="margin-top: 0; color: #d97706;">Action Required: Payment Pending</h3>
                    <p style="margin-bottom: 1rem;">Your booking has been confirmed by the owner. Please complete the payment to lock in your reservation permanently.</p>

                    <form id="paymentForm" style="margin-top: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                        <!-- Stripe Card Element -->
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Card Details</label>
                            <div id="card-element" style="padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: white;"></div>
                            <div id="card-errors" style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem;"></div>
                        </div>

                        <button type="submit" id="submit-button" class="btn btn-primary" style="width: 100%;">
                            <span id="button-text">Pay <?= formatMoney($booking['total_amount']) ?> Now</span>
                            <span id="spinner" style="display: none;">Processing...</span>
                        </button>
                    </form>

                    <div id="paymentMessage" style="margin-top: 1rem;"></div>

                    <div style="margin-top: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 4px; font-size: 0.875rem;">
                        <p style="margin: 0;"><i class="fas fa-lock"></i> Secure payment powered by Stripe</p>
                    </div>
                </div>
                <?php elseif ($booking['payment_status'] === 'paid'): ?>
                <div class="card" style="background-color: #e6f7e6; border: 2px solid #4caf50;">
                    <h3 style="margin-top: 0; color: #2e7d32;">Payment Completed</h3>
                    <p>Your payment has been successfully processed. Your booking is confirmed and locked in.</p>
                </div>
                <?php else: ?>
                <div class="card" style="background-color: #e3f2fd; border: 2px solid #2196f3;">
                    <h3 style="margin-top: 0; color: #1565c0;">Awaiting Owner Confirmation</h3>
                    <p>Your booking request is pending. You will be able to make payment once the owner confirms your booking.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="/customer/bookings" class="btn btn-secondary">Back to Bookings</a>
        </div>
    </div>
</div>

<?php if ($booking['status'] === 'confirmed' && $booking['payment_status'] !== 'paid'): ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
// Initialize Stripe
const stripe = Stripe('<?= getStripePublishableKey() ?>');
const elements = stripe.elements();

// Create card element
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#dc3545',
            iconColor: '#dc3545'
        }
    }
});

// Mount card element
cardElement.mount('#card-element');

// Handle realtime validation errors
cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

// Handle form submission
const form = document.getElementById('paymentForm');
const submitButton = document.getElementById('submit-button');
const buttonText = document.getElementById('button-text');
const spinner = document.getElementById('spinner');
const messageDiv = document.getElementById('paymentMessage');

form.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Disable submit button and show loading
    submitButton.disabled = true;
    buttonText.style.display = 'none';
    spinner.style.display = 'inline';
    messageDiv.innerHTML = '';

    try {
        // Create payment method
        const {error, paymentMethod} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        });

        if (error) {
            throw new Error(error.message);
        }

        // Send payment method to server
        const formData = new FormData(form);
        formData.append('payment_method_id', paymentMethod.id);

        const response = await fetch('/api/payment/process', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Payment failed');
        }

    } catch (error) {
        messageDiv.innerHTML = '<div class="alert alert-error">' + error.message + '</div>';
        submitButton.disabled = false;
        buttonText.style.display = 'inline';
        spinner.style.display = 'none';
    }
});
</script>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
