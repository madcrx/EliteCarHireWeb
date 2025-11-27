<?php
namespace controllers;

class CustomerController {
    public function __construct() {
        requireAuth('customer');
    }
    
    public function dashboard() {
        $customerId = $_SESSION['user_id'];
        $stats = [
            'total_bookings' => db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE customer_id = ?", [$customerId])['count'],
            'active_bookings' => db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE customer_id = ? AND status IN ('confirmed', 'in_progress')", [$customerId])['count'],
            'completed_bookings' => db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE customer_id = ? AND status='completed'", [$customerId])['count'],
        ];
        
        $upcomingBookings = db()->fetchAll("SELECT b.*, v.make, v.model FROM bookings b
                                            JOIN vehicles v ON b.vehicle_id = v.id
                                            WHERE b.customer_id = ? AND b.booking_date >= CURDATE()
                                            ORDER BY b.booking_date ASC LIMIT 5", [$customerId]);
        
        view('customer/dashboard', compact('stats', 'upcomingBookings'));
    }
    
    public function hires() {
        $this->bookings();
    }
    
    public function bookings() {
        $customerId = $_SESSION['user_id'];
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT b.*, v.make, v.model, v.year FROM bookings b
                JOIN vehicles v ON b.vehicle_id = v.id
                WHERE b.customer_id = ?";
        $params = [$customerId];

        if ($status !== 'all') {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $bookings = db()->fetchAll($sql, $params);
        view('customer/bookings', compact('bookings', 'status'));
    }
    
    public function profile() {
        $user = authUser();
        view('customer/profile', compact('user'));
    }
    
    public function updateProfile() {
        $userId = $_SESSION['user_id'];
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';

        db()->execute("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?",
                     [$firstName, $lastName, $phone, $userId]);

        logAudit('update_profile', 'users', $userId);
        flash('success', 'Profile updated successfully');
        redirect('/customer/profile');
    }

    public function viewBooking($id) {
        $customerId = $_SESSION['user_id'];

        // Get booking details with vehicle and owner information
        $booking = db()->fetch("SELECT b.*, v.make, v.model, v.year, v.color, v.category,
                                u.first_name as owner_first_name, u.last_name as owner_last_name,
                                u.phone as owner_phone
                                FROM bookings b
                                JOIN vehicles v ON b.vehicle_id = v.id
                                JOIN users u ON b.owner_id = u.id
                                WHERE b.id = ? AND b.customer_id = ?", [$id, $customerId]);

        if (!$booking) {
            flash('error', 'Booking not found');
            redirect('/customer/bookings');
        }

        // Get vehicle images
        $images = db()->fetchAll("SELECT image_path FROM vehicle_images
                                  WHERE vehicle_id = ? ORDER BY is_primary DESC, display_order ASC",
                                 [$booking['vehicle_id']]);

        view('customer/booking-detail', compact('booking', 'images'));
    }

    public function showCancelForm($id) {
        $customerId = $_SESSION['user_id'];

        // Get booking details
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model, v.year
             FROM bookings b
             JOIN vehicles v ON b.vehicle_id = v.id
             WHERE b.id = ? AND b.customer_id = ?",
            [$id, $customerId]
        );

        if (!$booking) {
            flash('error', 'Booking not found or access denied');
            redirect('/customer/bookings');
        }

        // Check if booking can be cancelled
        if ($booking['status'] === 'cancelled') {
            flash('error', 'This booking has already been cancelled');
            redirect('/customer/bookings');
        }

        if ($booking['status'] === 'completed') {
            flash('error', 'Completed bookings cannot be cancelled');
            redirect('/customer/bookings');
        }

        $user = authUser();
        view('customer/cancel-booking', compact('booking', 'user'));
    }

    public function submitCancellation($id) {
        requireAuth('customer');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/customer/bookings/' . $id . '/cancel');
        }

        $customerId = $_SESSION['user_id'];

        // Verify booking belongs to customer
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model, v.year, o.id as owner_id
             FROM bookings b
             JOIN vehicles v ON b.vehicle_id = v.id
             JOIN users o ON b.owner_id = o.id
             WHERE b.id = ? AND b.customer_id = ?",
            [$id, $customerId]
        );

        if (!$booking) {
            flash('error', 'Booking not found or access denied');
            redirect('/customer/bookings');
        }

        if ($booking['status'] === 'cancelled' || $booking['status'] === 'completed') {
            flash('error', 'This booking cannot be cancelled');
            redirect('/customer/bookings');
        }

        // Get form data
        $reason = $_POST['reason'] ?? '';
        $additionalDetails = $_POST['additional_details'] ?? '';
        $contactPhone = $_POST['contact_phone'] ?? '';
        $acknowledged = isset($_POST['acknowledge_policy']);

        // Validate inputs
        if (empty($reason) || empty($additionalDetails)) {
            flash('error', 'Please provide a reason and additional details for the cancellation');
            redirect('/customer/bookings/' . $id . '/cancel');
        }

        if (!$acknowledged) {
            flash('error', 'You must acknowledge the cancellation policy to proceed');
            redirect('/customer/bookings/' . $id . '/cancel');
        }

        if (strlen($additionalDetails) < 10) {
            flash('error', 'Additional details must be at least 10 characters');
            redirect('/customer/bookings/' . $id . '/cancel');
        }

        // Combine reason and details
        $fullReason = $reason . "\n\nAdditional Details:\n" . $additionalDetails;
        if ($contactPhone) {
            $fullReason .= "\n\nContact Phone: " . $contactPhone;
        }

        // Create pending change request for admin approval
        // Note: For customer-initiated cancellations, we use customer_id instead of owner_id
        db()->execute(
            "INSERT INTO pending_changes (owner_id, entity_type, entity_id, change_type, old_data, new_data, reason, status, created_at, initiated_by_customer)
             VALUES (?, 'booking', ?, 'cancellation', ?, ?, ?, 'pending', NOW(), 1)",
            [
                $booking['owner_id'], // We still use owner_id for the record, but mark it as customer-initiated
                $id,
                json_encode(['status' => $booking['status']]),
                json_encode(['status' => 'cancelled', 'cancellation_reason' => $fullReason]),
                $fullReason
            ]
        );

        // Notify all admins
        if (file_exists(__DIR__ . '/../helpers/notifications.php')) {
            try {
                require_once __DIR__ . '/../helpers/notifications.php';
                if (function_exists('notifyBookingCancellationPending')) {
                    $admins = db()->fetchAll("SELECT id FROM users WHERE role = 'admin'");
                    $vehicleName = $booking['year'] . ' ' . $booking['make'] . ' ' . $booking['model'];

                    foreach ($admins as $admin) {
                        notifyBookingCancellationPending(
                            $admin['id'],
                            $booking['booking_reference'],
                            $vehicleName,
                            $fullReason
                        );
                    }
                }
            } catch (\Exception $e) {
                error_log("Notification error in submitCancellation: " . $e->getMessage());
            } catch (\Error $e) {
                error_log("Notification fatal error in submitCancellation: " . $e->getMessage());
            }
        }

        // Send confirmation email to customer
        $this->sendCancellationRequestConfirmationEmail($booking, $fullReason, $customerId);

        logAudit('request_booking_cancellation_customer', 'bookings', $id, [
            'booking_reference' => $booking['booking_reference'],
            'reason' => $reason
        ]);

        flash('success', 'Cancellation request submitted successfully. Our admin team will review your request and process the cancellation. You will receive an email confirmation once processed.');
        redirect('/customer/bookings');
    }

    private function sendCancellationRequestConfirmationEmail($booking, $reason, $customerId) {
        $customer = db()->fetch("SELECT * FROM users WHERE id = ?", [$customerId]);
        $vehicleName = "{$booking['year']} {$booking['make']} {$booking['model']}";

        // Calculate potential refund
        $refundAmount = 0;
        $cancellationFee = 0;
        if ($booking['payment_status'] === 'paid') {
            $cancellationFee = $booking['total_amount'] * 0.5;
            $refundAmount = $booking['total_amount'] * 0.5;
        }

        $viewUrl = generateLoginUrl("/customer/bookings");
        $viewButton = getEmailButton($viewUrl, 'View My Bookings', 'primary');

        $refundInfo = '';
        if ($booking['payment_status'] === 'paid') {
            $refundInfo = "
            <div style='background: #fff3cd; padding: 20px; border-left: 4px solid #f39c12; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #f39c12;'>💰 Expected Refund</h3>
                <p><strong>Original Booking Amount:</strong> \$" . number_format($booking['total_amount'], 2) . " AUD</p>
                <p><strong>Cancellation Fee (50%):</strong> \$" . number_format($cancellationFee, 2) . " AUD</p>
                <p><strong>Refund Amount (50%):</strong> <span style='color: #4caf50; font-weight: bold;'>\$" . number_format($refundAmount, 2) . " AUD</span></p>
                <p style='margin: 10px 0 0 0; font-size: 14px;'><em>The refund will be processed to your original payment method within 5-7 business days after approval.</em></p>
            </div>";
        }

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #f39c12;'>📋 Cancellation Request Received</h2>
            <p>Dear {$customer['first_name']},</p>
            <p>We have received your booking cancellation request. Our admin team will review and process your request shortly.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #f39c12; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Date:</strong> {$booking['booking_date']}</p>
                <p><strong>Time:</strong> {$booking['start_time']} - {$booking['end_time']}</p>
                <p><strong>Total Amount:</strong> \$" . number_format($booking['total_amount'], 2) . " AUD</p>
                <p><strong>Payment Status:</strong> {$booking['payment_status']}</p>
                <p><strong>Current Status:</strong> <span style='color: #f39c12; font-weight: bold;'>CANCELLATION PENDING</span></p>
            </div>

            {$refundInfo}

            <div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0;'>
                <p style='margin: 0;'><strong>📋 Reminder:</strong> A 50% cancellation fee applies to all booking cancellations, regardless of when the cancellation is made. The remaining 50% will be refunded to your original payment method after admin approval.</p>
            </div>

            <div style='background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Your Cancellation Reason:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>

            {$viewButton}

            <p><strong>What happens next?</strong></p>
            <ul style='padding-left: 20px;'>
                <li>Our admin team will review your cancellation request</li>
                <li>You will receive an email confirmation once the cancellation is processed</li>
                <li>If payment was made, the refund will be initiated automatically</li>
            </ul>

            <p>If you have any questions, please contact us at support@elitecarhire.au</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($customer['email'], "Cancellation Request Received - {$booking['booking_reference']}", $body);
    }
}
