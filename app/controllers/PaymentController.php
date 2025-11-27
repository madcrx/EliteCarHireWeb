<?php
namespace controllers;

/**
 * Payment Controller - Stripe Integration
 *
 * Handles real payment processing via Stripe Payment Intents API
 * Processes customer payments and manages owner payouts
 */
class PaymentController {

    /**
     * Process payment using Stripe
     *
     * Endpoint: POST /api/payment/process
     * Required: booking_id, payment_method_id (from Stripe.js)
     */
    public function process() {
        requireAuth();

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            json(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
        }

        $bookingId = $_POST['booking_id'] ?? 0;
        $paymentMethodId = $_POST['payment_method_id'] ?? ''; // From Stripe.js

        // Validate booking
        $booking = db()->fetch("SELECT b.*, v.make, v.model, v.year, u.first_name, u.last_name, u.email,
                                o.stripe_account_id as owner_stripe_account
                                FROM bookings b
                                JOIN vehicles v ON b.vehicle_id = v.id
                                JOIN users u ON b.customer_id = u.id
                                LEFT JOIN users o ON b.owner_id = o.id
                                WHERE b.id = ? AND b.customer_id = ?",
                              [$bookingId, $_SESSION['user_id']]);

        if (!$booking) {
            json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        // Check booking status
        if ($booking['status'] !== 'confirmed') {
            json(['success' => false, 'message' => 'Booking must be confirmed by the owner before payment can be processed'], 400);
        }

        // Check if already paid
        if ($booking['payment_status'] === 'paid') {
            json(['success' => false, 'message' => 'Payment has already been processed for this booking'], 400);
        }

        // Initialize Stripe
        $stripe = $this->initializeStripe();
        if (!$stripe) {
            json(['success' => false, 'message' => 'Payment system configuration error. Please contact support.'], 500);
        }

        try {
            // Calculate amounts in cents
            $totalAmount = (int)($booking['total_amount'] * 100); // Convert to cents
            $commissionAmount = (int)($booking['commission_amount'] * 100);
            $ownerAmount = $totalAmount - $commissionAmount;

            // Create Payment Intent
            $paymentIntentData = [
                'amount' => $totalAmount,
                'currency' => strtolower(config('payment.currency')),
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'description' => "Elite Car Hire - Booking #{$booking['booking_reference']}",
                'metadata' => [
                    'booking_id' => $bookingId,
                    'booking_reference' => $booking['booking_reference'],
                    'customer_name' => $booking['first_name'] . ' ' . $booking['last_name'],
                    'customer_email' => $booking['email'],
                    'vehicle' => "{$booking['year']} {$booking['make']} {$booking['model']}",
                ],
                'receipt_email' => $booking['email'],
                'return_url' => url('/customer/bookings/' . $bookingId),
            ];

            // If owner has Stripe Connect account, use destination charges
            if (!empty($booking['owner_stripe_account'])) {
                $paymentIntentData['application_fee_amount'] = $commissionAmount;
                $paymentIntentData['transfer_data'] = [
                    'destination' => $booking['owner_stripe_account'],
                ];
            }

            $paymentIntent = \Stripe\PaymentIntent::create($paymentIntentData);

            // Check if payment requires additional action (3D Secure, etc.)
            if ($paymentIntent->status === 'requires_action' && $paymentIntent->next_action->type === 'redirect_to_url') {
                // Return redirect URL for 3D Secure authentication
                json([
                    'success' => true,
                    'requires_action' => true,
                    'redirect_url' => $paymentIntent->next_action->redirect_to_url->url,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            }

            // Payment successful
            if ($paymentIntent->status === 'succeeded') {
                // Get payment method details
                $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
                $cardBrand = $paymentMethod->card->brand ?? 'unknown';
                $cardLastFour = $paymentMethod->card->last4 ?? '0000';

                // Record payment in database
                db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method,
                              card_last_four, card_brand, status, payment_date, metadata)
                              VALUES (?, ?, ?, 'credit_card', ?, ?, 'completed', NOW(), ?)",
                             [$bookingId, $paymentIntent->id, $booking['total_amount'],
                              $cardLastFour, ucfirst($cardBrand), json_encode([
                                  'stripe_payment_intent' => $paymentIntent->id,
                                  'stripe_charge' => $paymentIntent->charges->data[0]->id ?? null,
                              ])]);

                $paymentId = db()->lastInsertId();

                // Update booking payment status
                db()->execute("UPDATE bookings SET payment_status = 'paid', updated_at = NOW() WHERE id = ?", [$bookingId]);

                // Create payout record for owner (if not using Stripe Connect)
                if (empty($booking['owner_stripe_account'])) {
                    $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];
                    db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, notes)
                                  VALUES (?, ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?)",
                                 [$booking['owner_id'], $bookingId, $payoutAmount,
                                  'Manual payout required - Owner not connected to Stripe Connect']);
                } else {
                    // Payout handled automatically via Stripe Connect
                    $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];
                    db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, payout_date, notes, reference)
                                  VALUES (?, ?, ?, 'completed', NOW(), ?, ?)",
                                 [$booking['owner_id'], $bookingId, $payoutAmount,
                                  'Automatic payout via Stripe Connect', $paymentIntent->id]);
                }

                // Send notifications
                createNotification($booking['owner_id'], 'payment_received', 'Payment Received',
                                  "Payment of $" . number_format($booking['total_amount'], 2) .
                                  " received for booking {$booking['booking_reference']}",
                                  "/owner/bookings");

                // Send email to customer
                sendEmail(
                    $booking['email'],
                    'Payment Confirmation - Booking ' . $booking['booking_reference'],
                    $this->getPaymentConfirmationEmail($booking, $paymentIntent->id, $cardBrand, $cardLastFour)
                );

                // Send email to owner
                $ownerEmail = db()->fetch("SELECT email FROM users WHERE id = ?", [$booking['owner_id']]);
                if ($ownerEmail) {
                    sendEmail(
                        $ownerEmail['email'],
                        'Payment Received - Booking ' . $booking['booking_reference'],
                        $this->getOwnerPaymentNotificationEmail($booking, $payoutAmount)
                    );
                }

                // Log audit
                logAudit('process_payment', 'payments', $paymentId, null, [
                    'booking_id' => $bookingId,
                    'amount' => $booking['total_amount'],
                    'stripe_payment_intent' => $paymentIntent->id,
                ]);

                json([
                    'success' => true,
                    'message' => 'Payment processed successfully! Your booking is now confirmed.',
                    'transaction_id' => $paymentIntent->id,
                    'booking_reference' => $booking['booking_reference'],
                    'redirect_url' => '/customer/bookings/' . $bookingId,
                ]);
            }

            // Payment failed or requires action
            json([
                'success' => false,
                'message' => 'Payment could not be completed. Status: ' . $paymentIntent->status,
                'status' => $paymentIntent->status,
            ]);

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            logAudit('payment_failed', 'bookings', $bookingId, null, [
                'error' => $e->getMessage(),
                'decline_code' => $e->getDeclineCode(),
            ]);

            json([
                'success' => false,
                'message' => 'Card declined: ' . $e->getError()->message,
                'error_type' => 'card_error',
            ], 402);

        } catch (\Stripe\Exception\RateLimitException $e) {
            json(['success' => false, 'message' => 'Too many requests. Please try again shortly.'], 429);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            json(['success' => false, 'message' => 'Invalid payment request: ' . $e->getMessage()], 400);

        } catch (\Stripe\Exception\AuthenticationException $e) {
            error_log('Stripe authentication error: ' . $e->getMessage());
            json(['success' => false, 'message' => 'Payment system authentication error. Please contact support.'], 500);

        } catch (\Stripe\Exception\ApiConnectionException $e) {
            json(['success' => false, 'message' => 'Network error. Please check your connection and try again.'], 503);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe API error: ' . $e->getMessage());
            json(['success' => false, 'message' => 'Payment processing error. Please try again or contact support.'], 500);

        } catch (\Exception $e) {
            error_log('Payment error: ' . $e->getMessage());
            json(['success' => false, 'message' => 'An unexpected error occurred. Please contact support.'], 500);
        }
    }

    /**
     * Create Payment Intent (for Stripe Elements)
     *
     * Endpoint: POST /api/payment/create-intent
     */
    public function createIntent() {
        requireAuth();

        $bookingId = $_POST['booking_id'] ?? 0;

        $booking = db()->fetch("SELECT * FROM bookings WHERE id = ? AND customer_id = ?",
                              [$bookingId, $_SESSION['user_id']]);

        if (!$booking) {
            json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        if ($booking['status'] !== 'confirmed') {
            json(['success' => false, 'message' => 'Booking must be confirmed before payment'], 400);
        }

        if ($booking['payment_status'] === 'paid') {
            json(['success' => false, 'message' => 'Booking already paid'], 400);
        }

        $stripe = $this->initializeStripe();
        if (!$stripe) {
            json(['success' => false, 'message' => 'Payment system not configured'], 500);
        }

        try {
            $amount = (int)($booking['total_amount'] * 100); // Convert to cents

            $intent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => strtolower(config('payment.currency')),
                'metadata' => [
                    'booking_id' => $bookingId,
                    'booking_reference' => $booking['booking_reference'],
                ],
            ]);

            json([
                'success' => true,
                'client_secret' => $intent->client_secret,
                'amount' => $booking['total_amount'],
            ]);

        } catch (\Exception $e) {
            error_log('Create Payment Intent error: ' . $e->getMessage());
            json(['success' => false, 'message' => 'Could not initialize payment'], 500);
        }
    }

    /**
     * Process refund
     *
     * Endpoint: POST /api/payment/refund
     */
    public function refund() {
        requireAuth('admin'); // Only admins can process refunds

        $paymentId = $_POST['payment_id'] ?? 0;
        $amount = $_POST['amount'] ?? null; // Partial refund amount (optional)
        $reason = $_POST['reason'] ?? '';

        $payment = db()->fetch("SELECT p.*, b.booking_reference, b.total_amount, b.owner_id
                                FROM payments p
                                JOIN bookings b ON p.booking_id = b.id
                                WHERE p.id = ?", [$paymentId]);

        if (!$payment) {
            json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        if ($payment['status'] === 'refunded') {
            json(['success' => false, 'message' => 'Payment already refunded'], 400);
        }

        $stripe = $this->initializeStripe();
        if (!$stripe) {
            json(['success' => false, 'message' => 'Payment system not configured'], 500);
        }

        try {
            $refundData = [
                'payment_intent' => $payment['transaction_id'],
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'admin_reason' => $reason,
                    'refunded_by' => $_SESSION['user_id'],
                ],
            ];

            // Partial refund if amount specified
            if ($amount !== null) {
                $refundData['amount'] = (int)($amount * 100);
            }

            $refund = \Stripe\Refund::create($refundData);

            // Update payment record
            $refundAmount = $amount ?? $payment['amount'];
            db()->execute("UPDATE payments SET status = ?, refund_amount = refund_amount + ?,
                          refund_date = NOW(), updated_at = NOW() WHERE id = ?",
                         [$refund->status === 'succeeded' ? 'refunded' : 'processing', $refundAmount, $paymentId]);

            // Update booking
            db()->execute("UPDATE bookings SET payment_status = 'refunded', updated_at = NOW()
                          WHERE id = ?", [$payment['booking_id']]);

            // Notify customer
            createNotification($payment['customer_id'] ?? 0, 'refund_processed', 'Refund Processed',
                              "Refund of $" . number_format($refundAmount, 2) .
                              " processed for booking {$payment['booking_reference']}");

            logAudit('refund_payment', 'payments', $paymentId, null, [
                'refund_id' => $refund->id,
                'amount' => $refundAmount,
                'reason' => $reason,
            ]);

            json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'refund_id' => $refund->id,
                'amount' => $refundAmount,
            ]);

        } catch (\Exception $e) {
            error_log('Refund error: ' . $e->getMessage());
            json(['success' => false, 'message' => 'Refund failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Initialize Stripe with API key
     */
    private function initializeStripe() {
        $secretKey = config('payment.stripe.secret_key');

        if (empty($secretKey)) {
            error_log('Stripe secret key not configured');
            return false;
        }

        // Check if Stripe library is available
        $stripePath = __DIR__ . '/../../vendor/stripe/stripe-php/init.php';
        if (file_exists($stripePath)) {
            require_once $stripePath;
        } else {
            error_log('Stripe PHP library not found. Run: composer require stripe/stripe-php');
            return false;
        }

        \Stripe\Stripe::setApiKey($secretKey);
        \Stripe\Stripe::setApiVersion('2023-10-16');
        \Stripe\Stripe::setAppInfo(
            'Elite Car Hire',
            '1.0.0',
            url('/')
        );

        return true;
    }

    /**
     * Get payment confirmation email HTML
     */
    private function getPaymentConfirmationEmail($booking, $transactionId, $cardBrand, $cardLastFour) {
        $amount = number_format($booking['total_amount'], 2);
        $vehicle = "{$booking['year']} {$booking['make']} {$booking['model']}";
        $viewUrl = generateLoginUrl("/customer/bookings");
        $viewButton = getEmailButton($viewUrl, 'View My Bookings', 'success');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #4caf50;'>✓ Payment Successful!</h2>
            <p>Dear {$booking['first_name']},</p>
            <p>Your payment has been successfully processed and your booking is now <strong>fully confirmed</strong>!</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$vehicle}</p>
                <p><strong>Date:</strong> {$booking['booking_date']}</p>
                <p><strong>Time:</strong> {$booking['start_time']} - {$booking['end_time']}</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
            </div>

            <div style='background: #e8f5e9; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Payment Details</h3>
                <p><strong>Amount Paid:</strong> \${$amount} AUD</p>
                <p><strong>Payment Method:</strong> " . ucfirst($cardBrand) . " ending in {$cardLastFour}</p>
                <p><strong>Transaction ID:</strong> {$transactionId}</p>
                <p><strong>Status:</strong> <span style='color: #4caf50; font-weight: bold;'>PAID</span></p>
            </div>

            <p>Your booking is now fully confirmed. The vehicle owner will contact you closer to the booking date with pickup arrangements.</p>

            {$viewButton}

            <p>If you have any questions, please contact us at " . config('email.support', 'support@elitecarhire.au') . "</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";
    }

    /**
     * Get owner payment notification email HTML
     */
    private function getOwnerPaymentNotificationEmail($booking, $payoutAmount) {
        $amount = number_format($booking['total_amount'], 2);
        $payout = number_format($payoutAmount, 2);
        $commission = number_format($booking['commission_amount'], 2);
        $vehicle = "{$booking['year']} {$booking['make']} {$booking['model']}";
        $viewBookingsUrl = generateLoginUrl("/owner/bookings");
        $viewPayoutsUrl = generateLoginUrl("/owner/payouts");
        $viewButton = getEmailButton($viewBookingsUrl, 'View Booking Details', 'primary');
        $payoutsButton = getEmailButton($viewPayoutsUrl, 'View Payouts', 'success');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #4caf50;'>💰 Payment Received for Your Vehicle</h2>
            <p>Good news! Payment has been received for your upcoming booking.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$vehicle}</p>
                <p><strong>Customer:</strong> {$booking['first_name']} {$booking['last_name']}</p>
                <p><strong>Date:</strong> {$booking['booking_date']}</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
            </div>

            <div style='background: #e8f5e9; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Payment Breakdown</h3>
                <p><strong>Total Payment:</strong> \${$amount} AUD</p>
                <p><strong>Platform Fee (15%):</strong> -\${$commission} AUD</p>
                <p style='font-size: 18px; color: #4caf50;'><strong>Your Payout:</strong> \${$payout} AUD</p>
                <p style='font-size: 12px; color: #666;'>Payout will be processed within 7 business days</p>
            </div>

            <p>Please ensure your vehicle is clean, fueled, and ready for pickup on the scheduled date.</p>

            {$viewButton}
            {$payoutsButton}

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong></p>
        </div>
        ";
    }
}
