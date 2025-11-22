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
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT * FROM vehicles WHERE owner_id = ?";
        $params = [$ownerId];

        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $vehicles = db()->fetchAll($sql, $params);
        view('owner/listings', compact('vehicles', 'status'));
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

    public function editListing($id) {
        $ownerId = $_SESSION['user_id'];

        // Get vehicle and verify ownership
        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ? AND owner_id = ?", [$id, $ownerId]);

        if (!$vehicle) {
            flash('error', 'Vehicle not found or access denied');
            redirect('/owner/listings');
        }

        view('owner/edit-listing', compact('vehicle'));
    }

    public function updateListing($id) {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/listings/' . $id . '/edit');
        }

        $ownerId = $_SESSION['user_id'];

        // Verify ownership
        $vehicle = db()->fetch("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?", [$id, $ownerId]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found or access denied');
            redirect('/owner/listings');
        }

        // Get form data
        $make = $_POST['make'] ?? '';
        $model = $_POST['model'] ?? '';
        $year = $_POST['year'] ?? '';
        $color = $_POST['color'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $hourlyRate = $_POST['hourly_rate'] ?? 0;
        $maxPassengers = $_POST['max_passengers'] ?? 4;
        $registrationNumber = $_POST['registration_number'] ?? '';

        // Validate required fields
        if (empty($make) || empty($model) || empty($year) || empty($hourlyRate)) {
            flash('error', 'Make, model, year, and hourly rate are required');
            redirect('/owner/listings/' . $id . '/edit');
        }

        // Update vehicle
        db()->execute("UPDATE vehicles SET make = ?, model = ?, year = ?, color = ?, category = ?,
                      description = ?, hourly_rate = ?, max_passengers = ?, registration_number = ?, updated_at = NOW()
                      WHERE id = ? AND owner_id = ?",
                     [$make, $model, $year, $color, $category, $description, $hourlyRate, $maxPassengers, $registrationNumber, $id, $ownerId]);

        logAudit('update_vehicle', 'vehicles', $id, [
            'make' => $make,
            'model' => $model,
            'hourly_rate' => $hourlyRate
        ]);

        flash('success', 'Vehicle updated successfully');
        redirect('/owner/listings');
    }

    public function bookings() {
        $ownerId = $_SESSION['user_id'];
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT b.*, v.make, v.model, u.first_name, u.last_name
                FROM bookings b
                JOIN vehicles v ON b.vehicle_id = v.id
                JOIN users u ON b.customer_id = u.id
                WHERE b.owner_id = ?";
        $params = [$ownerId];

        if ($status !== 'all') {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY b.booking_date DESC";

        $bookings = db()->fetchAll($sql, $params);
        view('owner/bookings', compact('bookings', 'status'));
    }
    
    public function calendar() {
        $ownerId = $_SESSION['user_id'];
        $vehicles = db()->fetchAll("SELECT id, make, model, year, registration_number FROM vehicles WHERE owner_id = ? AND status = 'approved' ORDER BY make, model", [$ownerId]);

        $blockedDates = db()->fetchAll("SELECT vbd.*, v.make, v.model, v.registration_number
                                        FROM vehicle_blocked_dates vbd
                                        JOIN vehicles v ON vbd.vehicle_id = v.id
                                        WHERE vbd.owner_id = ?
                                        ORDER BY vbd.start_date DESC", [$ownerId]);

        view('owner/calendar', compact('vehicles', 'blockedDates'));
    }

    public function blockDates() {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/calendar');
        }

        $ownerId = $_SESSION['user_id'];
        $vehicleId = $_POST['vehicle_id'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = $_POST['reason'] ?? '';

        // Validate inputs
        if (empty($vehicleId) || empty($startDate) || empty($endDate)) {
            flash('error', 'All fields are required');
            redirect('/owner/calendar');
        }

        // Verify vehicle belongs to owner
        $vehicle = db()->fetch("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?", [$vehicleId, $ownerId]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found or access denied');
            redirect('/owner/calendar');
        }

        // Validate dates
        if (strtotime($startDate) > strtotime($endDate)) {
            flash('error', 'End date must be after start date');
            redirect('/owner/calendar');
        }

        // Check for overlapping blocked dates
        $overlap = db()->fetch("SELECT id FROM vehicle_blocked_dates
                               WHERE vehicle_id = ?
                               AND ((start_date <= ? AND end_date >= ?)
                                    OR (start_date <= ? AND end_date >= ?)
                                    OR (start_date >= ? AND end_date <= ?))",
                              [$vehicleId, $startDate, $startDate, $endDate, $endDate, $startDate, $endDate]);

        if ($overlap) {
            flash('error', 'Date range overlaps with existing blocked dates');
            redirect('/owner/calendar');
        }

        // Insert blocked dates
        db()->execute("INSERT INTO vehicle_blocked_dates (vehicle_id, owner_id, start_date, end_date, reason, created_at)
                      VALUES (?, ?, ?, ?, ?, NOW())",
                     [$vehicleId, $ownerId, $startDate, $endDate, $reason]);

        logAudit('block_vehicle_dates', 'vehicle_blocked_dates', db()->lastInsertId(), [
            'vehicle_id' => $vehicleId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        flash('success', 'Dates blocked successfully');
        redirect('/owner/calendar');
    }

    public function unblockDate() {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/calendar');
        }

        $ownerId = $_SESSION['user_id'];
        $blockId = $_POST['block_id'] ?? '';

        if (empty($blockId)) {
            flash('error', 'Block ID is required');
            redirect('/owner/calendar');
        }

        // Verify block belongs to owner
        $block = db()->fetch("SELECT id FROM vehicle_blocked_dates WHERE id = ? AND owner_id = ?", [$blockId, $ownerId]);
        if (!$block) {
            flash('error', 'Blocked date not found or access denied');
            redirect('/owner/calendar');
        }

        // Delete the block
        db()->execute("DELETE FROM vehicle_blocked_dates WHERE id = ?", [$blockId]);

        logAudit('unblock_vehicle_dates', 'vehicle_blocked_dates', $blockId);

        flash('success', 'Dates unblocked successfully');
        redirect('/owner/calendar');
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
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT * FROM payouts WHERE owner_id = ?";
        $params = [$ownerId];

        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $payouts = db()->fetchAll($sql, $params);
        view('owner/payouts', compact('payouts', 'status'));
    }
    
    public function reviews() {
        $ownerId = $_SESSION['user_id'];
        $rating = $_GET['rating'] ?? 'all';

        $sql = "SELECT r.*, v.make, v.model, u.first_name, u.last_name
                FROM reviews r
                JOIN vehicles v ON r.vehicle_id = v.id
                JOIN users u ON r.customer_id = u.id
                WHERE r.owner_id = ?";
        $params = [$ownerId];

        if ($rating !== 'all') {
            $sql .= " AND r.rating = ?";
            $params[] = $rating;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $reviews = db()->fetchAll($sql, $params);
        view('owner/reviews', compact('reviews', 'rating'));
    }
    
    public function messages() {
        $ownerId = $_SESSION['user_id'];
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT m.*, u.first_name, u.last_name FROM messages m
                JOIN users u ON m.from_user_id = u.id
                WHERE m.to_user_id = ?";
        $params = [$ownerId];

        if ($status !== 'all') {
            $sql .= " AND m.read_at " . ($status === 'read' ? 'IS NOT NULL' : 'IS NULL');
        }

        $sql .= " ORDER BY m.created_at DESC";

        $messages = db()->fetchAll($sql, $params);
        view('owner/messages', compact('messages', 'status'));
    }
    
    public function pendingChanges() {
        $ownerId = $_SESSION['user_id'];
        $changes = db()->fetchAll("SELECT * FROM pending_changes WHERE owner_id = ? ORDER BY created_at DESC", [$ownerId]);
        view('owner/pending-changes', compact('changes'));
    }
}
