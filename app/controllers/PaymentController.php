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

            // Get owner details to check for Stripe Connect
            $owner = db()->fetch("SELECT * FROM users WHERE id = ?", [$booking['owner_id']]);

            // Check if owner has verified Stripe Connect account
            $useConnect = isStripeConnectEnabled()
                         && !empty($owner['stripe_account_id'])
                         && $owner['stripe_account_status'] === 'verified'
                         && $owner['stripe_payouts_enabled'];

            $amount = stripeAmount($booking['total_amount']); // Convert to cents
            $stripeTransferId = null;

            // Create payment with or without Stripe Connect
            if ($useConnect) {
                // Use Stripe Connect destination charge for automatic split
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
                    'transfer_data' => [
                        'destination' => $owner['stripe_account_id'],
                        'amount' => stripeAmount($booking['total_amount'] - $booking['commission_amount']), // 85% to owner
                    ],
                ]);

                // Get transfer ID from the charge
                if ($paymentIntent->status === 'succeeded' && !empty($paymentIntent->charges->data)) {
                    $charge = $paymentIntent->charges->data[0];
                    $stripeTransferId = $charge->transfer ?? null;
                }
            } else {
                // Standard payment (manual payout later)
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => 'aud',
                    'payment_method' => $paymentMethodId,
                    'confirm' => true,
                    'automatic_payment_methods' => [
                        'enabled' => true,
                        'allow_redirects' => 'never'
                    ],
                    'description' => "Booking {$booking['booking_reference']} - {$booking['customer_id']}",
                    'metadata' => [
                        'booking_id' => $bookingId,
                        'booking_reference' => $booking['booking_reference'],
                        'customer_id' => $booking['customer_id'],
                    ],
                ]);
            }

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

                // Create payout record for owner
                $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];

                if ($useConnect && $stripeTransferId) {
                    // Automatic payout via Stripe Connect - mark as completed immediately
                    db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, stripe_transfer_id,
                                  transfer_date, scheduled_date, paid_at, created_at, updated_at)
                                  VALUES (?, ?, ?, 'completed', ?, NOW(), NOW(), NOW(), NOW(), NOW())",
                                 [$booking['owner_id'], $bookingId, $payoutAmount, $stripeTransferId]);
                } else {
                    // Manual payout - schedule for later
                    db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, created_at, updated_at)
                                  VALUES (?, ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW(), NOW())",
                                 [$booking['owner_id'], $bookingId, $payoutAmount]);
                }

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
