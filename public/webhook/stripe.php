<?php
/**
 * Stripe Webhook Handler
 *
 * This endpoint receives and processes webhook events from Stripe
 * Configure this URL in your Stripe Dashboard: https://dashboard.stripe.com/webhooks
 */

// Set headers for webhook response
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../app/Database.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/helpers/stripe_helper.php';
require_once __DIR__ . '/../../app/helpers/email_sender.php';
require_once __DIR__ . '/../../app/helpers/booking_emails.php';

// Get webhook payload
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Log webhook received
error_log("Stripe Webhook received: " . substr($payload, 0, 200));

try {
    // Get Stripe configuration
    $config = getStripeConfig();
    $webhookSecret = $config['webhook_secret'];

    if (empty($webhookSecret)) {
        http_response_code(500);
        echo json_encode(['error' => 'Webhook secret not configured']);
        error_log("Stripe Webhook Error: No webhook secret configured");
        exit;
    }

    // Initialize Stripe
    require_once __DIR__ . '/../../vendor/autoload.php';
    \Stripe\Stripe::setApiKey($config['secret_key']);

    // Verify webhook signature
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );
    } catch (\UnexpectedValueException $e) {
        // Invalid payload
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        error_log("Stripe Webhook Error: Invalid payload - " . $e->getMessage());
        exit;
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        http_response_code(400);
        echo json_encode(['error' => 'Invalid signature']);
        error_log("Stripe Webhook Error: Invalid signature - " . $e->getMessage());
        exit;
    }

    // Handle the event
    switch ($event->type) {
        case 'payment_intent.succeeded':
            handlePaymentIntentSucceeded($event->data->object);
            break;

        case 'payment_intent.payment_failed':
            handlePaymentIntentFailed($event->data->object);
            break;

        case 'charge.refunded':
            handleChargeRefunded($event->data->object);
            break;

        case 'payment_intent.created':
            // Payment intent created (informational)
            error_log("Stripe: Payment intent created - " . $event->data->object->id);
            break;

        default:
            // Unexpected event type
            error_log("Stripe Webhook: Unexpected event type - " . $event->type);
    }

    // Return success response
    http_response_code(200);
    echo json_encode(['received' => true]);

} catch (Exception $e) {
    error_log("Stripe Webhook Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle successful payment intent
 */
function handlePaymentIntentSucceeded($paymentIntent) {
    $bookingId = $paymentIntent->metadata->booking_id ?? null;

    if (!$bookingId) {
        error_log("Stripe Webhook: No booking ID in payment intent metadata");
        return;
    }

    // Check if payment already recorded
    $existing = db()->fetch("SELECT id FROM payments WHERE stripe_payment_intent_id = ?", [$paymentIntent->id]);

    if ($existing) {
        error_log("Stripe Webhook: Payment already recorded for intent " . $paymentIntent->id);
        return;
    }

    // Get booking details
    $booking = db()->fetch("SELECT * FROM bookings WHERE id = ?", [$bookingId]);

    if (!$booking) {
        error_log("Stripe Webhook: Booking not found - " . $bookingId);
        return;
    }

    // Record payment if not already done
    if ($booking['payment_status'] !== 'paid') {
        $amount = dollarAmount($paymentIntent->amount);

        db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method,
                      card_last_four, status, payment_date, stripe_payment_intent_id)
                      VALUES (?, ?, ?, 'card', '****', 'completed', NOW(), ?)",
                     [$bookingId, $paymentIntent->id, $amount, $paymentIntent->id]);

        db()->execute("UPDATE bookings SET payment_status = 'paid' WHERE id = ?", [$bookingId]);

        // Create payout for owner
        $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];
        db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date)
                      VALUES (?, ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY))",
                     [$booking['owner_id'], $bookingId, $payoutAmount]);

        // Create notification
        createNotification($booking['owner_id'], 'payment_received', 'Payment Received',
                          'Payment has been received for booking ' . $booking['booking_reference']);

        // Send email notifications
        emailCustomerPaymentReceived($bookingId);
        emailOwnerPaymentReceived($bookingId);

        logAudit('webhook_payment_success', 'payments', $bookingId, [
            'payment_intent' => $paymentIntent->id,
            'amount' => $amount
        ]);

        error_log("Stripe Webhook: Payment processed successfully for booking " . $bookingId);
    }
}

/**
 * Handle failed payment intent
 */
function handlePaymentIntentFailed($paymentIntent) {
    $bookingId = $paymentIntent->metadata->booking_id ?? null;

    if (!$bookingId) {
        return;
    }

    // Log failed payment
    db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method,
                  status, payment_date, stripe_payment_intent_id)
                  VALUES (?, ?, ?, 'card', 'failed', NOW(), ?)",
                 [$bookingId, $paymentIntent->id, dollarAmount($paymentIntent->amount), $paymentIntent->id]);

    logAudit('webhook_payment_failed', 'payments', $bookingId, [
        'payment_intent' => $paymentIntent->id,
        'failure_message' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
    ]);

    error_log("Stripe Webhook: Payment failed for booking " . $bookingId);
}

/**
 * Handle refunded charge
 */
function handleChargeRefunded($charge) {
    $paymentIntentId = $charge->payment_intent ?? null;

    if (!$paymentIntentId) {
        return;
    }

    // Find payment by payment intent ID
    $payment = db()->fetch("SELECT * FROM payments WHERE stripe_payment_intent_id = ?", [$paymentIntentId]);

    if (!$payment) {
        error_log("Stripe Webhook: Payment not found for refund - " . $paymentIntentId);
        return;
    }

    // Update payment status
    db()->execute("UPDATE payments SET status = 'refunded' WHERE id = ?", [$payment['id']]);

    // Update booking payment status
    db()->execute("UPDATE bookings SET payment_status = 'refunded' WHERE id = ?", [$payment['booking_id']]);

    logAudit('webhook_payment_refunded', 'payments', $payment['booking_id'], [
        'payment_id' => $payment['id'],
        'refund_amount' => dollarAmount($charge->amount_refunded)
    ]);

    error_log("Stripe Webhook: Payment refunded for booking " . $payment['booking_id']);
}
