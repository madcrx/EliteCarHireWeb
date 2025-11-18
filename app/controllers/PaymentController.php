<?php
namespace controllers;

class PaymentController {
    public function process() {
        requireAuth();
        
        $bookingId = $_POST['booking_id'] ?? 0;
        $cardNumber = $_POST['card_number'] ?? '';
        $cardExpiry = $_POST['card_expiry'] ?? '';
        $cardCvv = $_POST['card_cvv'] ?? '';
        
        $booking = db()->fetch("SELECT * FROM bookings WHERE id = ? AND customer_id = ?", 
                              [$bookingId, $_SESSION['user_id']]);
        
        if (!$booking) {
            json(['success' => false, 'message' => 'Booking not found'], 404);
        }
        
        // Simulate payment processing
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        $cardLastFour = substr($cardNumber, -4);
        
        db()->execute("INSERT INTO payments (booking_id, transaction_id, amount, payment_method, 
                      card_last_four, status, payment_date) 
                      VALUES (?, ?, ?, 'credit_card', ?, 'completed', NOW())", 
                     [$bookingId, $transactionId, $booking['total_amount'], $cardLastFour]);
        
        db()->execute("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?", [$bookingId]);
        
        // Create payout for owner
        $payoutAmount = $booking['total_amount'] - $booking['commission_amount'];
        db()->execute("INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date) 
                      VALUES (?, ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY))", 
                     [$booking['owner_id'], $bookingId, $payoutAmount]);
        
        createNotification($booking['owner_id'], 'payment_received', 'Payment Received', 
                          'Payment has been received for booking ' . $booking['booking_reference']);
        
        logAudit('process_payment', 'payments', db()->lastInsertId());
        
        json(['success' => true, 'message' => 'Payment processed successfully', 'transaction_id' => $transactionId]);
    }
}
