<?php
namespace controllers;

class OwnerController {
    public function __construct() {
        requireAuth('owner');
    }
    
    public function dashboard() {
        $ownerId = $_SESSION['user_id'];
        $stats = [
            'total_vehicles' => db()->fetch("SELECT COUNT(*) as count FROM vehicles WHERE owner_id = ?", [$ownerId])['count'],
            'active_bookings' => db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE owner_id = ? AND status IN ('confirmed', 'in_progress')", [$ownerId])['count'],
            'monthly_earnings' => db()->fetch("SELECT COALESCE(SUM(total_amount - commission_amount), 0) as earnings FROM bookings WHERE owner_id = ? AND status='completed' AND MONTH(created_at) = MONTH(NOW())", [$ownerId])['earnings'],
            'pending_payouts' => db()->fetch("SELECT COALESCE(SUM(amount), 0) as amount FROM payouts WHERE owner_id = ? AND status='pending'", [$ownerId])['amount'],
        ];
        
        $recentBookings = db()->fetchAll("SELECT b.*, v.make, v.model, u.first_name, u.last_name FROM bookings b 
                                          JOIN vehicles v ON b.vehicle_id = v.id 
                                          JOIN users u ON b.customer_id = u.id 
                                          WHERE b.owner_id = ? ORDER BY b.created_at DESC LIMIT 10", [$ownerId]);
        
        view('owner/dashboard', compact('stats', 'recentBookings'));
    }
    
    public function listings() {
        $ownerId = $_SESSION['user_id'];
        $vehicles = db()->fetchAll("SELECT * FROM vehicles WHERE owner_id = ? ORDER BY created_at DESC", [$ownerId]);
        view('owner/listings', compact('vehicles'));
    }
    
    public function addListing() {
        view('owner/add-listing');
    }
    
    public function saveListing() {
        $ownerId = $_SESSION['user_id'];
        $data = [
            'make' => $_POST['make'] ?? '',
            'model' => $_POST['model'] ?? '',
            'year' => $_POST['year'] ?? '',
            'color' => $_POST['color'] ?? '',
            'category' => $_POST['category'] ?? '',
            'description' => $_POST['description'] ?? '',
            'hourly_rate' => $_POST['hourly_rate'] ?? 0,
            'max_passengers' => $_POST['max_passengers'] ?? 4,
        ];
        
        $sql = "INSERT INTO vehicles (owner_id, make, model, year, color, category, description, hourly_rate, max_passengers, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        db()->execute($sql, [$ownerId, $data['make'], $data['model'], $data['year'], $data['color'], 
                            $data['category'], $data['description'], $data['hourly_rate'], $data['max_passengers']]);
        
        $vehicleId = db()->lastInsertId();
        
        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key],
                    ];
                    
                    $path = uploadFile($file, 'vehicles');
                    if ($path) {
                        db()->execute("INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, display_order) VALUES (?, ?, ?, ?)", 
                                     [$vehicleId, $path, $key === 0 ? 1 : 0, $key]);
                    }
                }
            }
        }
        
        logAudit('create_vehicle', 'vehicles', $vehicleId);
        flash('success', 'Vehicle listing submitted for approval');
        redirect('/owner/listings');
    }
    
    public function bookings() {
        $ownerId = $_SESSION['user_id'];
        $bookings = db()->fetchAll("SELECT b.*, v.make, v.model, u.first_name, u.last_name 
                                     FROM bookings b 
                                     JOIN vehicles v ON b.vehicle_id = v.id 
                                     JOIN users u ON b.customer_id = u.id 
                                     WHERE b.owner_id = ? ORDER BY b.booking_date DESC", [$ownerId]);
        view('owner/bookings', compact('bookings'));
    }
    
    public function calendar() {
        view('owner/calendar');
    }
    
    public function analytics() {
        $ownerId = $_SESSION['user_id'];
        
        $monthlyData = db()->fetchAll("SELECT DATE_FORMAT(booking_date, '%Y-%m') as month, 
                                       COUNT(*) as bookings, 
                                       SUM(total_amount - commission_amount) as earnings 
                                       FROM bookings WHERE owner_id = ? AND status='completed' 
                                       GROUP BY DATE_FORMAT(booking_date, '%Y-%m') 
                                       ORDER BY month DESC LIMIT 12", [$ownerId]);
        
        view('owner/analytics', compact('monthlyData'));
    }
    
    public function payouts() {
        $ownerId = $_SESSION['user_id'];
        $payouts = db()->fetchAll("SELECT * FROM payouts WHERE owner_id = ? ORDER BY created_at DESC", [$ownerId]);
        view('owner/payouts', compact('payouts'));
    }
    
    public function reviews() {
        $ownerId = $_SESSION['user_id'];
        $reviews = db()->fetchAll("SELECT r.*, v.make, v.model, u.first_name, u.last_name 
                                    FROM reviews r 
                                    JOIN vehicles v ON r.vehicle_id = v.id 
                                    JOIN users u ON r.customer_id = u.id 
                                    WHERE r.owner_id = ? ORDER BY r.created_at DESC", [$ownerId]);
        view('owner/reviews', compact('reviews'));
    }
    
    public function messages() {
        $ownerId = $_SESSION['user_id'];
        $messages = db()->fetchAll("SELECT m.*, u.first_name, u.last_name FROM messages m 
                                     JOIN users u ON m.from_user_id = u.id 
                                     WHERE m.to_user_id = ? ORDER BY m.created_at DESC", [$ownerId]);
        view('owner/messages', compact('messages'));
    }
    
    public function pendingChanges() {
        $ownerId = $_SESSION['user_id'];
        $changes = db()->fetchAll("SELECT * FROM pending_changes WHERE owner_id = ? ORDER BY created_at DESC", [$ownerId]);
        view('owner/pending-changes', compact('changes'));
    }
}
