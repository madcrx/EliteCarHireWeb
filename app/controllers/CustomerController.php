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
}
