<?php
namespace controllers;

// Include email notification functions
require_once __DIR__ . '/../helpers/email_sender.php';
require_once __DIR__ . '/../helpers/booking_emails.php';
require_once __DIR__ . '/../helpers/stripe_helper.php';

class PaymentController {
    public function process() {
        requireAuth();

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            json(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
        }

        $bookingId = $_POST['booking_id'] ?? 0;
        $paymentMethodId = $_POST['payment_method_id'] ?? ''; // Stripe Payment Method ID

        $booking = db()->fetch("SELECT * FROM bookings WHERE id = ? AND customer_id = ?",
                              [$bookingId, $_SESSION['user_id']]);

        if (!$booking) {
            json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        // Check if booking is confirmed
        if ($booking['status'] !== 'confirmed') {
            json(['success' => false, 'message' => 'Booking must be confirmed by the owner before payment can be processed'], 400);
        }

        // Check if payment is already made
        if ($booking['payment_status'] === 'paid') {
            json(['success' => false, 'message' => 'Payment has already been processed for this booking'], 400);
        }

        try {
            // Initialize Stripe
            initStripe();

            // Get owner details to verify Stripe Connect (REQUIRED)
            $owner = db()->fetch("SELECT * FROM users WHERE id = ?", [$booking['owner_id']]);

            // CRITICAL: Owner MUST have verified Stripe Connect account
            // This should never happen as booking confirmation requires verified account
            if (empty($owner['stripe_account_id']) ||
                $owner['stripe_account_status'] !== 'verified' ||
                !$owner['stripe_payouts_enabled']) {
                error_log("CRITICAL: Payment attempted for owner without verified Stripe Connect. Owner ID: {$booking['owner_id']}");
                json(['success' => false, 'message' => 'Payment cannot be processed. The vehicle owner needs to complete Stripe account setup.'], 400);
            }

            // Create standard payment (platform receives 100%)
            // Payout will be scheduled for next Monday (minimum 4 days after booking completion)
            $amount = stripeAmount($booking['total_amount']); // Convert to cents

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'aud',
                'payment_method' => $paymentMethodId,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ],
                'description' => "Booking {$booking['booking_reference']} - Elite Car Hire",
                'metadata' => [
                    'booking_id' => $bookingId,
                    'booking_reference' => $booking['booking_reference'],
                    'customer_id' => $booking['customer_id'],
                    'owner_id' => $booking['owner_id'],
                ],
            ]);

            // Check payment status
            if ($paymentIntent->status === 'succeeded') {
                // Get card details from payment method
                $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
                $cardLastFour = $paymentMethod->card->last4 ?? '****';
                $cardBrand = $paymentMethod->card->brand ?? 'card';

                // Record payment in database
                db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method,
                              card_last_four, status, payment_date, stripe_payment_intent_id)
                              VALUES (?, ?, ?, ?, ?, 'completed', NOW(), ?)",
                             [$bookingId, $paymentIntent->id, $booking['total_amount'], $cardBrand, $cardLastFour, $paymentIntent->id]);

                // Update booking payment status
                db()->execute("UPDATE bookings SET payment_status = 'paid' WHERE id = ?", [$bookingId]);

                // Calculate scheduled payout date
                // Payouts occur weekly on Monday, minimum 4 days after booking end_date
                $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];
                $scheduledDate = calculateNextPayoutDate($booking['end_date']);

                // Create scheduled payout (will be processed on Monday via batch job)
                db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, created_at, updated_at)
                              VALUES (?, ?, ?, 'scheduled', ?, NOW(), NOW())",
                             [$booking['owner_id'], $bookingId, $payoutAmount, $scheduledDate]);

                // Create notification
                createNotification($booking['owner_id'], 'payment_received', 'Payment Received',
                                  'Payment has been received for booking ' . $booking['booking_reference']);

                // Log audit
                logAudit('process_payment', 'payments', db()->lastInsertId(), [
                    'booking_id' => $bookingId,
                    'amount' => $booking['total_amount'],
                    'stripe_payment_intent' => $paymentIntent->id
                ]);

                // Send email notifications
                emailCustomerPaymentReceived($bookingId);
                emailOwnerPaymentReceived($bookingId);

                json([
                    'success' => true,
                    'message' => 'Payment processed successfully! Your booking is now locked in.',
                    'transaction_id' => $paymentIntent->id
                ]);
            } else {
                // Payment requires additional action or failed
                json([
                    'success' => false,
                    'message' => 'Payment could not be processed. Please try again or use a different card.',
                    'requires_action' => $paymentIntent->status === 'requires_action',
                    'payment_intent_client_secret' => $paymentIntent->client_secret ?? null
                ], 400);
            }

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            error_log("Stripe Card Error: " . $e->getMessage());
            json(['success' => false, 'message' => 'Your card was declined. ' . $e->getMessage()], 400);

        } catch (\Stripe\Exception\RateLimitException $e) {
            error_log("Stripe Rate Limit: " . $e->getMessage());
            json(['success' => false, 'message' => 'Too many requests. Please try again later.'], 429);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            error_log("Stripe Invalid Request: " . $e->getMessage());
            json(['success' => false, 'message' => 'Invalid payment request. Please try again.'], 400);

        } catch (\Stripe\Exception\AuthenticationException $e) {
            error_log("Stripe Authentication Error: " . $e->getMessage());
            json(['success' => false, 'message' => 'Payment system configuration error. Please contact support.'], 500);

        } catch (\Stripe\Exception\ApiConnectionException $e) {
            error_log("Stripe Network Error: " . $e->getMessage());
            json(['success' => false, 'message' => 'Network error. Please check your connection and try again.'], 500);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe API Error: " . $e->getMessage());
            json(['success' => false, 'message' => 'Payment processing error. Please try again.'], 500);

        } catch (Exception $e) {
            error_log("Payment Error: " . $e->getMessage());
            json(['success' => false, 'message' => 'An error occurred while processing your payment. Please try again.'], 500);
        }
    }

    /**
     * Create Payment Intent (for Stripe Elements)
     */
    public function createPaymentIntent() {
        requireAuth();

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            json(['success' => false, 'message' => 'Invalid security token.'], 403);
        }

        $bookingId = $_POST['booking_id'] ?? 0;

        $booking = db()->fetch("SELECT * FROM bookings WHERE id = ? AND customer_id = ?",
                              [$bookingId, $_SESSION['user_id']]);

        if (!$booking || $booking['status'] !== 'confirmed' || $booking['payment_status'] === 'paid') {
            json(['success' => false, 'message' => 'Invalid booking'], 400);
        }

        try {
            initStripe();

            $amount = stripeAmount($booking['total_amount']);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'aud',
                'automatic_payment_methods' => ['enabled' => true],
                'description' => "Booking {$booking['booking_reference']}",
                'metadata' => [
                    'booking_id' => $bookingId,
                    'booking_reference' => $booking['booking_reference'],
                ],
            ]);

            json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $booking['total_amount']
            ]);

        } catch (Exception $e) {
            error_log("Create Payment Intent Error: " . $e->getMessage());
            json(['success' => false, 'message' => 'Could not initialize payment'], 500);
        }
    }
}
