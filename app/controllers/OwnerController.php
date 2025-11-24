<?php
namespace controllers;

class OwnerController {
    public function __construct() {
        try {
            error_log("OwnerController::__construct() - Start");
            error_log("OwnerController::__construct() - Session role: " . ($_SESSION['role'] ?? 'NOT SET'));
            error_log("OwnerController::__construct() - Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

            requireAuth('owner');

            error_log("OwnerController::__construct() - Auth check passed");
        } catch (Exception $e) {
            error_log("OwnerController::__construct() - Exception: " . $e->getMessage());
            error_log("OwnerController::__construct() - Stack trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Error $e) {
            error_log("OwnerController::__construct() - Fatal Error: " . $e->getMessage());
            error_log("OwnerController::__construct() - Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    public function dashboard() {
        try {
            error_log("OwnerController::dashboard() - Start");

            $ownerId = $_SESSION['user_id'] ?? null;
            error_log("OwnerController::dashboard() - Owner ID: " . $ownerId);

            if (!$ownerId) {
                error_log("OwnerController::dashboard() - No owner ID in session");
                redirect('/login');
                return;
            }

            // Initialize notifications as empty (graceful fallback)
            $notifications = [];
            $notificationCount = 0;

            // Load helper files in correct order (notifications first, then booking automation)
            error_log("OwnerController::dashboard() - Loading notifications helper");
            if (file_exists(__DIR__ . '/../helpers/notifications.php')) {
                try {
                    require_once __DIR__ . '/../helpers/notifications.php';
                    if (function_exists('getUnreadNotifications') && function_exists('getUnreadNotificationCount')) {
                        $notifications = getUnreadNotifications($ownerId, 5);
                        $notificationCount = getUnreadNotificationCount($ownerId);
                    }
                } catch (Exception $e) {
                    error_log("Notifications error: " . $e->getMessage());
                } catch (Error $e) {
                    error_log("Notifications fatal error: " . $e->getMessage());
                }
            }

            error_log("OwnerController::dashboard() - Loading booking automation");
            if (file_exists(__DIR__ . '/../helpers/booking_automation.php')) {
                try {
                    require_once __DIR__ . '/../helpers/booking_automation.php';
                    if (function_exists('autoUpdateBookingStatuses')) {
                        autoUpdateBookingStatuses();
                    }
                } catch (Exception $e) {
                    error_log("Booking automation error: " . $e->getMessage());
                } catch (Error $e) {
                    error_log("Booking automation fatal error: " . $e->getMessage());
                }
            }

            error_log("OwnerController::dashboard() - Fetching stats");

            // PHP 8.2 compatible: fetch results first, validate type, then access array keys safely
            $vehicleCount = db()->fetch("SELECT COUNT(*) as count FROM vehicles WHERE owner_id = ?", [$ownerId]);
            $bookingCount = db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE owner_id = ? AND status IN ('confirmed', 'in_progress')", [$ownerId]);
            $earnings = db()->fetch("SELECT COALESCE(SUM(total_amount - commission_amount), 0) as earnings FROM bookings WHERE owner_id = ? AND status='completed' AND MONTH(created_at) = MONTH(NOW())", [$ownerId]);
            $payouts = db()->fetch("SELECT COALESCE(SUM(amount), 0) as amount FROM payouts WHERE owner_id = ? AND status='pending'", [$ownerId]);

            $stats = [
                'total_vehicles' => (is_array($vehicleCount) && isset($vehicleCount['count'])) ? $vehicleCount['count'] : 0,
                'active_bookings' => (is_array($bookingCount) && isset($bookingCount['count'])) ? $bookingCount['count'] : 0,
                'monthly_earnings' => (is_array($earnings) && isset($earnings['earnings'])) ? $earnings['earnings'] : 0,
                'pending_payouts' => (is_array($payouts) && isset($payouts['amount'])) ? $payouts['amount'] : 0,
            ];

            error_log("OwnerController::dashboard() - Fetching recent bookings");
            $recentBookings = db()->fetchAll("SELECT b.*, v.make, v.model, u.first_name, u.last_name FROM bookings b
                                              JOIN vehicles v ON b.vehicle_id = v.id
                                              JOIN users u ON b.customer_id = u.id
                                              WHERE b.owner_id = ? ORDER BY b.created_at DESC LIMIT 10", [$ownerId]);

            // Ensure recentBookings is an array
            if (!is_array($recentBookings)) {
                error_log("OwnerController::dashboard() - fetchAll returned non-array: " . gettype($recentBookings));
                $recentBookings = [];
            }

            error_log("OwnerController::dashboard() - Rendering view");
            view('owner/dashboard', compact('stats', 'recentBookings', 'notifications', 'notificationCount'));
            error_log("OwnerController::dashboard() - Complete");

        } catch (Exception $e) {
            error_log("OwnerController::dashboard() - Exception: " . $e->getMessage());
            error_log("OwnerController::dashboard() - Stack trace: " . $e->getTraceAsString());
            echo "An error occurred loading the dashboard. Please check the error logs.";
            exit;
        } catch (Error $e) {
            error_log("OwnerController::dashboard() - Fatal Error: " . $e->getMessage());
            error_log("OwnerController::dashboard() - Stack trace: " . $e->getTraceAsString());
            echo "A fatal error occurred loading the dashboard. Please check the error logs.";
            exit;
        }
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
        $view = $_GET['view'] ?? 'table';

        // Auto-update booking statuses (with error handling)
        if (file_exists(__DIR__ . '/../helpers/booking_automation.php')) {
            try {
                require_once __DIR__ . '/../helpers/booking_automation.php';
                if (function_exists('autoUpdateBookingStatuses')) {
                    autoUpdateBookingStatuses();
                }
            } catch (Exception $e) {
                error_log("Booking automation error: " . $e->getMessage());
            } catch (Error $e) {
                error_log("Booking automation fatal error: " . $e->getMessage());
            }
        }

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

        // Ensure bookings is an array (type safety)
        if (!is_array($bookings)) {
            error_log("OwnerController::bookings() - fetchAll returned non-array: " . gettype($bookings));
            $bookings = [];
        }

        // Fetch blocked dates for the calendar view
        $blockedDates = db()->fetchAll(
            "SELECT vbd.*, v.make, v.model
             FROM vehicle_blocked_dates vbd
             JOIN vehicles v ON vbd.vehicle_id = v.id
             WHERE vbd.owner_id = ?
             ORDER BY vbd.start_date ASC",
            [$ownerId]
        );

        // Ensure blockedDates is an array
        if (!is_array($blockedDates)) {
            error_log("OwnerController::bookings() - blockedDates fetchAll returned non-array: " . gettype($blockedDates));
            $blockedDates = [];
        }

        view('owner/bookings', compact('bookings', 'status', 'view', 'blockedDates'));
    }

    public function confirmBooking() {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/bookings');
        }

        $bookingId = $_POST['booking_id'] ?? '';
        $ownerId = $_SESSION['user_id'];

        // Verify booking belongs to owner
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model, u.first_name, u.last_name
             FROM bookings b
             JOIN vehicles v ON b.vehicle_id = v.id
             JOIN users u ON b.customer_id = u.id
             WHERE b.id = ? AND b.owner_id = ?",
            [$bookingId, $ownerId]
        );

        if (!$booking) {
            flash('error', 'Booking not found or access denied');
            redirect('/owner/bookings');
        }

        if ($booking['status'] !== 'pending') {
            flash('error', 'Only pending bookings can be confirmed');
            redirect('/owner/bookings');
        }

        // Update booking status
        db()->execute(
            "UPDATE bookings SET status = 'confirmed', updated_at = NOW()
             WHERE id = ?",
            [$bookingId]
        );

        // Create notification for customer
        if (file_exists(__DIR__ . '/../helpers/notifications.php')) {
            try {
                require_once __DIR__ . '/../helpers/notifications.php';
                if (function_exists('notifyBookingConfirmed')) {
                    $vehicleName = $booking['make'] . ' ' . $booking['model'];
                    notifyBookingConfirmed(
                        $booking['customer_id'],
                        $booking['booking_reference'],
                        $vehicleName
                    );
                }
            } catch (Exception $e) {
                error_log("Notification error in confirmBooking: " . $e->getMessage());
            } catch (Error $e) {
                error_log("Notification fatal error in confirmBooking: " . $e->getMessage());
            }
        }

        // If payment is already made and booking time has started, transition to in_progress
        if ($booking['payment_status'] === 'paid') {
            if (file_exists(__DIR__ . '/../helpers/booking_automation.php')) {
                try {
                    require_once __DIR__ . '/../helpers/booking_automation.php';
                    if (function_exists('canTransitionToInProgress') && canTransitionToInProgress($bookingId)) {
                        transitionBookingToInProgress($bookingId);
                        flash('success', 'Booking confirmed and started!');
                    } else {
                        flash('success', 'Booking confirmed successfully! It will automatically start when the booking time begins.');
                    }
                } catch (Exception $e) {
                    error_log("Booking automation error in confirmBooking: " . $e->getMessage());
                    flash('success', 'Booking confirmed successfully! It will automatically start when the booking time begins.');
                } catch (Error $e) {
                    error_log("Booking automation fatal error in confirmBooking: " . $e->getMessage());
                    flash('success', 'Booking confirmed successfully! It will automatically start when the booking time begins.');
                }
            } else {
                flash('success', 'Booking confirmed successfully! It will automatically start when the booking time begins.');
            }
        } else {
            flash('success', 'Booking confirmed successfully! Waiting for customer payment.');
        }

        logAudit('confirm_booking', 'bookings', $bookingId, [
            'booking_reference' => $booking['booking_reference']
        ]);

        redirect('/owner/bookings');
    }

    public function cancelBooking() {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/bookings');
        }

        $bookingId = $_POST['booking_id'] ?? '';
        $reason = $_POST['cancellation_reason'] ?? '';
        $ownerId = $_SESSION['user_id'];

        // Validate reason
        if (empty($reason)) {
            flash('error', 'Cancellation reason is required');
            redirect('/owner/bookings');
        }

        // Verify booking belongs to owner
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model
             FROM bookings b
             JOIN vehicles v ON b.vehicle_id = v.id
             WHERE b.id = ? AND b.owner_id = ?",
            [$bookingId, $ownerId]
        );

        if (!$booking) {
            flash('error', 'Booking not found or access denied');
            redirect('/owner/bookings');
        }

        if ($booking['status'] === 'cancelled' || $booking['status'] === 'completed') {
            flash('error', 'This booking cannot be cancelled');
            redirect('/owner/bookings');
        }

        // Create pending change request for admin approval
        db()->execute(
            "INSERT INTO pending_changes (owner_id, entity_type, entity_id, change_type, old_data, new_data, reason, status, created_at)
             VALUES (?, 'booking', ?, 'cancellation', ?, ?, ?, 'pending', NOW())",
            [
                $ownerId,
                $bookingId,
                json_encode(['status' => $booking['status']]),
                json_encode(['status' => 'cancelled', 'cancellation_reason' => $reason]),
                $reason
            ]
        );

        // Notify all admins
        if (file_exists(__DIR__ . '/../helpers/notifications.php')) {
            try {
                require_once __DIR__ . '/../helpers/notifications.php';
                if (function_exists('notifyBookingCancellationPending')) {
                    $admins = db()->fetchAll("SELECT id FROM users WHERE role = 'admin'");
                    $vehicleName = $booking['make'] . ' ' . $booking['model'];

                    foreach ($admins as $admin) {
                        notifyBookingCancellationPending(
                            $admin['id'],
                            $booking['booking_reference'],
                            $vehicleName,
                            $reason
                        );
                    }
                }
            } catch (Exception $e) {
                error_log("Notification error in cancelBooking: " . $e->getMessage());
            } catch (Error $e) {
                error_log("Notification fatal error in cancelBooking: " . $e->getMessage());
            }
        }

        logAudit('request_booking_cancellation', 'bookings', $bookingId, [
            'booking_reference' => $booking['booking_reference'],
            'reason' => $reason
        ]);

        flash('success', 'Cancellation request submitted. Waiting for admin approval.');
        redirect('/owner/bookings');
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

        // Detect if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // If AJAX, ensure clean JSON output
        if ($isAjax) {
            // Clear all output buffers to prevent JSON parsing errors
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();
        }

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid security token. Please try again.']);
                exit;
            }
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/calendar');
        }

        $ownerId = $_SESSION['user_id'];
        $vehicleId = $_POST['vehicle_id'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $frequency = $_POST['frequency'] ?? 'daily';
        $customDays = $_POST['days'] ?? [];

        // Validate inputs
        if (empty($vehicleId) || empty($startDate) || empty($endDate)) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit;
            }
            flash('error', 'All fields are required');
            redirect('/owner/calendar');
        }

        // Verify vehicle belongs to owner
        $vehicle = db()->fetch("SELECT id FROM vehicles WHERE id = ? AND owner_id = ?", [$vehicleId, $ownerId]);
        if (!$vehicle) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vehicle not found or access denied']);
                exit;
            }
            flash('error', 'Vehicle not found or access denied');
            redirect('/owner/calendar');
        }

        // Validate dates
        if (strtotime($startDate) > strtotime($endDate)) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
                exit;
            }
            flash('error', 'End date must be after start date');
            redirect('/owner/calendar');
        }

        // Generate dates based on frequency
        $datesToBlock = $this->generateBlockDates($startDate, $endDate, $frequency, $customDays);

        if (empty($datesToBlock)) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No valid dates to block based on your selection']);
                exit;
            }
            flash('error', 'No valid dates to block based on your selection');
            redirect('/owner/calendar');
        }

        // Block each date individually
        $blockedCount = 0;
        try {
            foreach ($datesToBlock as $dateToBlock) {
                // Check for overlapping blocked dates for this specific date
                $overlap = db()->fetch("SELECT id FROM vehicle_blocked_dates
                                       WHERE vehicle_id = ?
                                       AND ? BETWEEN start_date AND end_date",
                                      [$vehicleId, $dateToBlock]);

                if (!$overlap) {
                    // Insert blocked date (single day blocks)
                    db()->execute("INSERT INTO vehicle_blocked_dates (vehicle_id, owner_id, start_date, end_date, reason, created_at)
                                  VALUES (?, ?, ?, ?, ?, NOW())",
                                 [$vehicleId, $ownerId, $dateToBlock, $dateToBlock, $reason]);
                    $blockedCount++;
                }
            }
        } catch (Exception $e) {
            error_log("Error blocking dates: " . $e->getMessage());
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
            flash('error', 'Error blocking dates. Please try again.');
            redirect('/owner/calendar');
        }

        if ($blockedCount > 0) {
            logAudit('block_vehicle_dates', 'vehicle_blocked_dates', null, [
                'vehicle_id' => $vehicleId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'frequency' => $frequency,
                'blocked_count' => $blockedCount
            ]);
        }

        // Return response based on request type
        if ($isAjax) {
            $output = ob_get_clean();
            header('Content-Type: application/json');
            if ($blockedCount > 0) {
                echo json_encode(['success' => true, 'message' => $blockedCount . ' date(s) blocked successfully']);
            } else {
                echo json_encode(['success' => true, 'message' => 'No new dates were blocked (all dates already blocked or no matching days)', 'warning' => true]);
            }
            exit;
        } else {
            if ($blockedCount > 0) {
                flash('success', $blockedCount . ' date(s) blocked successfully');
            } else {
                flash('warning', 'No new dates were blocked (all dates already blocked or no matching days)');
            }
            redirect('/owner/calendar');
        }
    }

    private function generateBlockDates($startDate, $endDate, $frequency, $customDays) {
        $dates = [];
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);

        while ($current <= $end) {
            $dayOfWeek = (int)$current->format('w'); // 0 = Sunday, 6 = Saturday
            $shouldBlock = false;

            switch ($frequency) {
                case 'daily':
                    $shouldBlock = true;
                    break;

                case 'weekdays':
                    // Monday = 1, Friday = 5
                    $shouldBlock = ($dayOfWeek >= 1 && $dayOfWeek <= 5);
                    break;

                case 'weekends':
                    // Saturday = 6, Sunday = 0
                    $shouldBlock = ($dayOfWeek == 0 || $dayOfWeek == 6);
                    break;

                case 'custom':
                    // Check if current day of week is in custom days array
                    $shouldBlock = in_array((string)$dayOfWeek, $customDays);
                    break;
            }

            if ($shouldBlock) {
                $dates[] = $current->format('Y-m-d');
            }

            $current->modify('+1 day');
        }

        return $dates;
    }

    public function unblockDate() {
        requireAuth('owner');

        // Detect if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // If AJAX, ensure clean JSON output
        if ($isAjax) {
            // Clear all output buffers to prevent JSON parsing errors
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();
        }

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid security token. Please try again.']);
                exit;
            }
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/calendar');
        }

        $ownerId = $_SESSION['user_id'];
        $blockId = $_POST['block_id'] ?? '';

        if (empty($blockId)) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Block ID is required']);
                exit;
            }
            flash('error', 'Block ID is required');
            redirect('/owner/calendar');
        }

        // Verify block belongs to owner
        $block = db()->fetch("SELECT id FROM vehicle_blocked_dates WHERE id = ? AND owner_id = ?", [$blockId, $ownerId]);
        if (!$block) {
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Blocked date not found or access denied']);
                exit;
            }
            flash('error', 'Blocked date not found or access denied');
            redirect('/owner/calendar');
        }

        // Delete the block
        try {
            db()->execute("DELETE FROM vehicle_blocked_dates WHERE id = ?", [$blockId]);
            logAudit('unblock_vehicle_dates', 'vehicle_blocked_dates', $blockId);
        } catch (Exception $e) {
            error_log("Error unblocking date: " . $e->getMessage());
            if ($isAjax) {
                ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
            flash('error', 'Error unblocking date. Please try again.');
            redirect('/owner/calendar');
        }

        // Return response based on request type
        if ($isAjax) {
            $output = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Dates unblocked successfully']);
            exit;
        } else {
            flash('success', 'Dates unblocked successfully');
            redirect('/owner/calendar');
        }
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
