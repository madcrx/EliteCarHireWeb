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

    public function approveBooking() {
        requireAuth('customer');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/customer/bookings');
        }

        $bookingId = $_POST['booking_id'] ?? '';
        $customerId = $_SESSION['user_id'];

        // Verify booking belongs to customer and is awaiting approval
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model
             FROM bookings b
             JOIN vehicles v ON b.vehicle_id = v.id
             WHERE b.id = ? AND b.customer_id = ?",
            [$bookingId, $customerId]
        );

        if (!$booking) {
            flash('error', 'Booking not found or access denied');
            redirect('/customer/bookings');
        }

        if ($booking['status'] !== 'awaiting_approval') {
            flash('error', 'This booking is not awaiting approval');
            redirect('/customer/bookings');
        }

        // Update booking status to confirmed
        db()->execute(
            "UPDATE bookings SET
                status = 'confirmed',
                updated_at = NOW()
             WHERE id = ?",
            [$bookingId]
        );

        // Log the approval
        logAudit('approve_booking_changes', 'bookings', $bookingId, [
            'customer_id' => $customerId,
            'total_amount' => $booking['total_amount'],
            'additional_charges' => $booking['additional_charges']
        ]);

        // Send notification to owner
        db()->execute(
            "INSERT INTO notifications (user_id, title, message, type, created_at)
             VALUES (?, ?, ?, 'booking', NOW())",
            [
                $booking['owner_id'],
                'Customer Approved Booking Changes',
                "Customer has approved the updated booking for {$booking['make']} {$booking['model']} (Ref: {$booking['booking_reference']}). Total amount: $" . number_format($booking['total_amount'], 2) . ". Awaiting payment."
            ]
        );

        flash('success', 'Booking approved! Please proceed with payment to secure your booking.');
        redirect('/customer/bookings/' . $bookingId);
    }

    public function rejectBooking() {
        requireAuth('customer');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/customer/bookings');
        }

        $bookingId = $_POST['booking_id'] ?? '';
        $customerId = $_SESSION['user_id'];

        // Verify booking belongs to customer and is awaiting approval
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model
             FROM bookings b
             JOIN vehicles v ON b.vehicle_id = v.id
             WHERE b.id = ? AND b.customer_id = ?",
            [$bookingId, $customerId]
        );

        if (!$booking) {
            flash('error', 'Booking not found or access denied');
            redirect('/customer/bookings');
        }

        if ($booking['status'] !== 'awaiting_approval') {
            flash('error', 'This booking is not awaiting approval');
            redirect('/customer/bookings');
        }

        // Cancel the booking
        db()->execute(
            "UPDATE bookings SET
                status = 'cancelled',
                cancellation_reason = 'Customer rejected additional charges',
                cancelled_at = NOW(),
                updated_at = NOW()
             WHERE id = ?",
            [$bookingId]
        );

        // Log the rejection
        logAudit('reject_booking_changes', 'bookings', $bookingId, [
            'customer_id' => $customerId,
            'rejected_amount' => $booking['total_amount'],
            'additional_charges' => $booking['additional_charges']
        ]);

        // Send notification to owner
        db()->execute(
            "INSERT INTO notifications (user_id, title, message, type, created_at)
             VALUES (?, ?, ?, 'booking', NOW())",
            [
                $booking['owner_id'],
                'Customer Rejected Booking Changes',
                "Customer has rejected the additional charges for {$booking['make']} {$booking['model']} (Ref: {$booking['booking_reference']}). The booking has been cancelled."
            ]
        );

        flash('success', 'Additional charges rejected. The booking has been cancelled.');
        redirect('/customer/bookings');
    }
}
