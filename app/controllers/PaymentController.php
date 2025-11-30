<?php
namespace controllers;

// Include email notification functions
require_once __DIR__ . '/../helpers/email_sender.php';
require_once __DIR__ . '/../helpers/booking_emails.php';

class PaymentController {
    public function process() {
        requireAuth();

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            json(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
        }

        $bookingId = $_POST['booking_id'] ?? 0;
        $cardNumber = $_POST['card_number'] ?? '';
        $cardExpiry = $_POST['card_expiry'] ?? '';
        $cardCvv = $_POST['card_cvv'] ?? '';

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

        // Validate card details
        $cardNumber = str_replace(' ', '', $cardNumber);
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            json(['success' => false, 'message' => 'Invalid card number'], 400);
        }

        if (strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
            json(['success' => false, 'message' => 'Invalid CVV'], 400);
        }

        // Simulate payment processing
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        $cardLastFour = substr($cardNumber, -4);

        db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method,
                      card_last_four, status, payment_date)
                      VALUES (?, ?, ?, 'credit_card', ?, 'completed', NOW())",
                     [$bookingId, $transactionId, $booking['total_amount'], $cardLastFour]);

        db()->execute("UPDATE bookings SET payment_status = 'paid' WHERE id = ?", [$bookingId]);

        // Create payout for owner
        $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];
        db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date)
                      VALUES (?, ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY))",
                     [$booking['owner_id'], $bookingId, $payoutAmount]);

        createNotification($booking['owner_id'], 'payment_received', 'Payment Received',
                          'Payment has been received for booking ' . $booking['booking_reference']);

        logAudit('process_payment', 'payments', db()->lastInsertId());

        // Send email notifications
        emailCustomerPaymentReceived($bookingId);
        emailOwnerPaymentReceived($bookingId);

        json(['success' => true, 'message' => 'Payment processed successfully! Your booking is now locked in.', 'transaction_id' => $transactionId]);
    }
}
