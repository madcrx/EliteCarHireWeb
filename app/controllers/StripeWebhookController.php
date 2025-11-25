<?php
namespace controllers;

/**
 * Stripe Webhook Controller
 *
 * Handles incoming webhook events from Stripe
 * https://stripe.com/docs/webhooks
 */
class StripeWebhookController {

    /**
     * Handle incoming webhook
     *
     * Endpoint: POST /webhooks/stripe
     */
    public function handle() {
        // Get raw POST data
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (empty($payload) || empty($sigHeader)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        // Verify webhook signature
        $event = $this->verifyWebhook($payload, $sigHeader);

        if (!$event) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }

        // Log webhook event
        $this->logWebhookEvent($event);

        // Handle different event types
        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;

                case 'charge.refunded':
                    $this->handleRefund($event->data->object);
                    break;

                case 'charge.dispute.created':
                    $this->handleDispute($event->data->object);
                    break;

                case 'payout.paid':
                    $this->handlePayoutPaid($event->data->object);
                    break;

                case 'payout.failed':
                    $this->handlePayoutFailed($event->data->object);
                    break;

                default:
                    // Log unhandled event type
                    error_log('Unhandled Stripe webhook event type: ' . $event->type);
            }

            // Return 200 OK to acknowledge receipt
            http_response_code(200);
            echo json_encode(['status' => 'success']);

        } catch (\Exception $e) {
            error_log('Webhook handling error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhook($payload, $sigHeader) {
        $webhookSecret = config('payment.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            error_log('Stripe webhook secret not configured');
            return null;
        }

        // Load Stripe library
        $stripePath = __DIR__ . '/../../vendor/stripe/stripe-php/init.php';
        if (!file_exists($stripePath)) {
            error_log('Stripe PHP library not found');
            return null;
        }

        require_once $stripePath;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
            return $event;
        } catch (\UnexpectedValueException $e) {
            error_log('Invalid webhook payload: ' . $e->getMessage());
            return null;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            error_log('Invalid webhook signature: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log webhook event to database
     */
    private function logWebhookEvent($event) {
        try {
            db()->execute("INSERT INTO stripe_webhook_events (event_id, event_type, payload, processed_at)
                          VALUES (?, ?, ?, NOW())",
                         [$event->id, $event->type, json_encode($event->data->object)]);
        } catch (\Exception $e) {
            error_log('Failed to log webhook event: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSuccess($paymentIntent) {
        $bookingId = $paymentIntent->metadata->booking_id ?? null;

        if (!$bookingId) {
            error_log('Payment success webhook: booking_id not found in metadata');
            return;
        }

        // Check if payment already recorded
        $existing = db()->fetch("SELECT id FROM payments WHERE transaction_id = ?", [$paymentIntent->id]);
        if ($existing) {
            // Payment already recorded (from synchronous processing)
            return;
        }

        // Record payment (backup in case synchronous processing failed)
        $booking = db()->fetch("SELECT * FROM bookings WHERE id = ?", [$bookingId]);
        if (!$booking) {
            error_log('Payment success webhook: booking not found');
            return;
        }

        db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method,
                      status, payment_date, metadata)
                      VALUES (?, ?, ?, 'credit_card', 'completed', NOW(), ?)",
                     [$bookingId, $paymentIntent->id, $booking['total_amount'],
                      json_encode(['source' => 'webhook', 'payment_intent' => $paymentIntent->id])]);

        db()->execute("UPDATE bookings SET payment_status = 'paid', updated_at = NOW() WHERE id = ?", [$bookingId]);

        // Send notification
        createNotification($booking['customer_id'], 'payment_confirmed', 'Payment Confirmed',
                          'Your payment has been confirmed for booking ' . $booking['booking_reference']);
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($paymentIntent) {
        $bookingId = $paymentIntent->metadata->booking_id ?? null;

        if (!$bookingId) {
            return;
        }

        $booking = db()->fetch("SELECT * FROM bookings WHERE id = ?", [$bookingId]);
        if (!$booking) {
            return;
        }

        // Log failure
        db()->execute("INSERT INTO payment_failures (booking_id, transaction_id, failure_reason, failure_date)
                      VALUES (?, ?, ?, NOW())",
                     [$bookingId, $paymentIntent->id, $paymentIntent->last_payment_error->message ?? 'Unknown error']);

        // Notify customer
        createNotification($booking['customer_id'], 'payment_failed', 'Payment Failed',
                          'Your payment for booking ' . $booking['booking_reference'] . ' has failed. Please try again.',
                          '/customer/bookings/' . $bookingId);

        // Send email
        $user = db()->fetch("SELECT email, first_name FROM users WHERE id = ?", [$booking['customer_id']]);
        if ($user) {
            sendEmail(
                $user['email'],
                'Payment Failed - Booking ' . $booking['booking_reference'],
                $this->getPaymentFailedEmail($user['first_name'], $booking, $paymentIntent->last_payment_error->message ?? 'Unknown error')
            );
        }
    }

    /**
     * Handle refund
     */
    private function handleRefund($charge) {
        $paymentIntentId = $charge->payment_intent;

        if (!$paymentIntentId) {
            return;
        }

        $payment = db()->fetch("SELECT * FROM payments WHERE transaction_id = ?", [$paymentIntentId]);
        if (!$payment) {
            error_log('Refund webhook: payment not found for payment_intent: ' . $paymentIntentId);
            return;
        }

        $refundAmount = $charge->amount_refunded / 100; // Convert from cents

        // Update payment status
        db()->execute("UPDATE payments SET status = 'refunded', refund_amount = ?,
                      refund_date = NOW(), updated_at = NOW() WHERE id = ?",
                     [$refundAmount, $payment['id']]);

        // Update booking
        db()->execute("UPDATE bookings SET payment_status = 'refunded', updated_at = NOW()
                      WHERE id = ?", [$payment['booking_id']]);

        // Notify customer
        $booking = db()->fetch("SELECT customer_id, booking_reference FROM bookings WHERE id = ?",
                              [$payment['booking_id']]);
        if ($booking) {
            createNotification($booking['customer_id'], 'refund_processed', 'Refund Processed',
                              "A refund of $" . number_format($refundAmount, 2) .
                              " has been processed for booking {$booking['booking_reference']}");
        }
    }

    /**
     * Handle dispute (chargeback)
     */
    private function handleDispute($dispute) {
        $chargeId = $dispute->charge;

        // Find payment by charge ID
        $payment = db()->fetch("SELECT p.*, b.booking_reference FROM payments p
                                JOIN bookings b ON p.booking_id = b.id
                                WHERE JSON_EXTRACT(p.metadata, '$.stripe_charge') = ?",
                              [$chargeId]);

        if (!$payment) {
            error_log('Dispute webhook: payment not found for charge: ' . $chargeId);
            return;
        }

        // Record dispute
        db()->execute("INSERT INTO payment_disputes (payment_id, dispute_id, amount, reason, status, created_at)
                      VALUES (?, ?, ?, ?, ?, NOW())",
                     [$payment['id'], $dispute->id, $dispute->amount / 100, $dispute->reason, $dispute->status]);

        // Notify admin
        $admins = db()->fetchAll("SELECT id FROM users WHERE role = 'admin'");
        foreach ($admins as $admin) {
            createNotification($admin['id'], 'dispute_created', 'Payment Dispute Created',
                              "A dispute has been filed for booking {$payment['booking_reference']}. Amount: $" .
                              number_format($dispute->amount / 100, 2),
                              '/admin/payments');
        }

        // Send email to admin
        sendEmail(
            'support@elitecarhire.au',
            'URGENT: Payment Dispute - Booking ' . $payment['booking_reference'],
            $this->getDisputeAlertEmail($payment, $dispute)
        );
    }

    /**
     * Handle successful payout to connected account
     */
    private function handlePayoutPaid($payout) {
        // Update payout record if exists
        db()->execute("UPDATE payouts SET status = 'completed', payout_date = NOW()
                      WHERE reference = ?", [$payout->id]);
    }

    /**
     * Handle failed payout
     */
    private function handlePayoutFailed($payout) {
        db()->execute("UPDATE payouts SET status = 'failed', notes = ?
                      WHERE reference = ?",
                     [$payout->failure_message ?? 'Payout failed', $payout->id]);

        // Notify admin
        $admins = db()->fetchAll("SELECT id FROM users WHERE role = 'admin'");
        foreach ($admins as $admin) {
            createNotification($admin['id'], 'payout_failed', 'Payout Failed',
                              'A payout of $' . number_format($payout->amount / 100, 2) . ' has failed.',
                              '/admin/payouts');
        }
    }

    /**
     * Get payment failed email HTML
     */
    private function getPaymentFailedEmail($name, $booking, $reason) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #f44336;'>Payment Failed</h2>
            <p>Dear {$name},</p>
            <p>Unfortunately, your payment for booking <strong>{$booking['booking_reference']}</strong> could not be processed.</p>

            <div style='background: #ffebee; padding: 20px; border-left: 4px solid #f44336; margin: 20px 0;'>
                <p><strong>Reason:</strong> {$reason}</p>
            </div>

            <p>Please try again with a different payment method or contact your bank for assistance.</p>

            <p><a href='" . url('/customer/bookings/' . $booking['id']) . "' style='display: inline-block; background: #C5A253; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin-top: 10px;'>Retry Payment</a></p>

            <p>If you continue to experience issues, please contact us at support@elitecarhire.au or call 0406 907 849.</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong></p>
        </div>
        ";
    }

    /**
     * Get dispute alert email HTML
     */
    private function getDisputeAlertEmail($payment, $dispute) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #f44336;'>⚠️ URGENT: Payment Dispute</h2>
            <p>A customer has filed a dispute/chargeback for the following booking:</p>

            <div style='background: #ffebee; padding: 20px; border-left: 4px solid #f44336; margin: 20px 0;'>
                <p><strong>Booking Reference:</strong> {$payment['booking_reference']}</p>
                <p><strong>Dispute Amount:</strong> $" . number_format($dispute->amount / 100, 2) . " AUD</p>
                <p><strong>Reason:</strong> {$dispute->reason}</p>
                <p><strong>Status:</strong> {$dispute->status}</p>
                <p><strong>Dispute ID:</strong> {$dispute->id}</p>
            </div>

            <p><strong>Action Required:</strong> You need to respond to this dispute in your Stripe Dashboard within the required timeframe.</p>

            <p><a href='https://dashboard.stripe.com/disputes/{$dispute->id}' style='display: inline-block; background: #f44336; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin-top: 10px;'>View Dispute in Stripe</a></p>

            <p style='margin-top: 30px;'><strong>Elite Car Hire Payment System</strong></p>
        </div>
        ";
    }
}
