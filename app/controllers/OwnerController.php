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
        } catch (\Exception $e) {
            error_log("OwnerController::__construct() - Exception: " . $e->getMessage());
            error_log("OwnerController::__construct() - Stack trace: " . $e->getTraceAsString());
            throw $e;
        } catch (\Error $e) {
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
                } catch (\Exception $e) {
                    error_log("Notifications error: " . $e->getMessage());
                } catch (\Error $e) {
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
                } catch (\Exception $e) {
                    error_log("Booking automation error: " . $e->getMessage());
                } catch (\Error $e) {
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

        } catch (\Exception $e) {
            error_log("OwnerController::dashboard() - Exception: " . $e->getMessage());
            error_log("OwnerController::dashboard() - Stack trace: " . $e->getTraceAsString());
            echo "An error occurred loading the dashboard. Please check the error logs.";
            exit;
        } catch (\Error $e) {
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
            'state' => $_POST['state'] ?? 'VIC',
            'description' => $_POST['description'] ?? '',
            'hourly_rate' => $_POST['hourly_rate'] ?? 0,
            'max_passengers' => $_POST['max_passengers'] ?? 4,
        ];

        $sql = "INSERT INTO vehicles (owner_id, make, model, year, color, category, state, description, hourly_rate, max_passengers, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        db()->execute($sql, [$ownerId, $data['make'], $data['model'], $data['year'], $data['color'],
                            $data['category'], $data['state'], $data['description'], $data['hourly_rate'], $data['max_passengers']]);

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
        $state = $_POST['state'] ?? 'VIC';
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
        db()->execute("UPDATE vehicles SET make = ?, model = ?, year = ?, color = ?, category = ?, state = ?,
                      description = ?, hourly_rate = ?, max_passengers = ?, registration_number = ?, updated_at = NOW()
                      WHERE id = ? AND owner_id = ?",
                     [$make, $model, $year, $color, $category, $state, $description, $hourlyRate, $maxPassengers, $registrationNumber, $id, $ownerId]);

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
            } catch (\Exception $e) {
                error_log("Booking automation error: " . $e->getMessage());
            } catch (\Error $e) {
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

        // Calculate status counts for the calendar legend
        // We need to count unique dates, not bookings
        $bookedDates = [];
        $pendingDates = [];

        foreach ($bookings as $booking) {
            if (!empty($booking['booking_date']) && !empty($booking['status'])) {
                $date = $booking['booking_date'];
                if ($booking['status'] === 'confirmed' || $booking['status'] === 'in_progress') {
                    $bookedDates[$date] = true;
                } elseif ($booking['status'] === 'pending') {
                    // Only count as pending if not already booked
                    if (!isset($bookedDates[$date])) {
                        $pendingDates[$date] = true;
                    }
                }
            }
        }

        // Count blocked dates (unique dates, not blocks)
        $blockedUniqueDates = [];
        foreach ($blockedDates as $block) {
            try {
                if (!empty($block['start_date']) && !empty($block['end_date'])) {
                    $start = new \DateTime($block['start_date']);
                    $end = new \DateTime($block['end_date']);
                    $current = clone $start;

                    while ($current <= $end) {
                        $dateStr = $current->format('Y-m-d');
                        $blockedUniqueDates[$dateStr] = true;
                        $current->modify('+1 day');
                    }
                }
            } catch (\Exception $e) {
                error_log("Error processing blocked date: " . $e->getMessage());
                continue;
            }
        }

        $statusCounts = [
            'booked' => count($bookedDates),
            'pending' => count($pendingDates),
            'blocked' => count($blockedUniqueDates),
            'available' => 0 // Will be calculated based on current month in the view
        ];

        view('owner/bookings', compact('bookings', 'status', 'view', 'blockedDates', 'statusCounts'));
    }

    public function updateBookingPrice() {
        requireAuth('owner');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/owner/bookings');
        }

        $bookingId = $_POST['booking_id'] ?? '';
        $additionalCharges = floatval($_POST['additional_charges'] ?? 0);
        $ownerId = $_SESSION['user_id'];

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

        if ($booking['status'] !== 'pending') {
            flash('error', 'Only pending bookings can have their price edited');
            redirect('/owner/bookings');
        }

        // Calculate new total amount
        $newTotalAmount = $booking['base_amount'] + $additionalCharges;

        // Update booking with new charges
        db()->execute(
            "UPDATE bookings SET additional_charges = ?, total_amount = ?, updated_at = NOW()
             WHERE id = ?",
            [$additionalCharges, $newTotalAmount, $bookingId]
        );

        logAudit('update_booking_price', 'bookings', $bookingId, [
            'additional_charges' => $additionalCharges,
            'new_total' => $newTotalAmount
        ]);

        flash('success', 'Booking price updated successfully! New total: $' . number_format($newTotalAmount, 2));
        redirect('/owner/bookings');
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
        $additionalCharges = floatval($_POST['additional_charges'] ?? 0);
        $additionalChargesReason = trim($_POST['additional_charges_reason'] ?? '');
        $ownerId = $_SESSION['user_id'];

        // Validate reason is provided if there are additional charges
        if ($additionalCharges > 0 && empty($additionalChargesReason)) {
            flash('error', 'Please provide a reason for the additional charges.');
            redirect('/owner/bookings');
        }

        // Verify booking belongs to owner
        $booking = db()->fetch(
            "SELECT b.*, v.make, v.model, u.first_name, u.last_name, u.email
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

        // Calculate final total amount (base + any additional charges)
        $finalTotalAmount = $booking['base_amount'] + $additionalCharges;

        // Determine booking status based on whether there are additional charges
        // If additional charges exist, customer must approve before payment
        $newStatus = $additionalCharges > 0 ? 'awaiting_approval' : 'confirmed';

        // Update booking with final price, reason, and status
        db()->execute(
            "UPDATE bookings SET
                additional_charges = ?,
                additional_charges_reason = ?,
                total_amount = ?,
                status = ?,
                updated_at = NOW()
             WHERE id = ?",
            [$additionalCharges, $additionalChargesReason, $finalTotalAmount, $newStatus, $bookingId]
        );

        // Log the confirmation
        logAudit('confirm_booking', 'bookings', $bookingId, [
            'additional_charges' => $additionalCharges,
            'additional_charges_reason' => $additionalChargesReason,
            'final_total' => $finalTotalAmount,
            'new_status' => $newStatus
        ]);

        // Create notification for customer based on workflow
        $vehicleName = $booking['make'] . ' ' . $booking['model'];
        $notificationTitle = '';
        $notificationMessage = '';

        if ($additionalCharges > 0) {
            // Customer needs to approve additional charges first
            $notificationTitle = 'Booking Updated - Approval Required';
            $notificationMessage = "Your booking for {$vehicleName} (Ref: {$booking['booking_reference']}) has been reviewed by the owner. " .
                                   "Additional charges of $" . number_format($additionalCharges, 2) . " have been added. " .
                                   "Reason: {$additionalChargesReason}. " .
                                   "New total: $" . number_format($finalTotalAmount, 2) . ". " .
                                   "Please review and approve the updated amount to proceed with your booking.";
        } else {
            // Direct confirmation - no additional charges
            $notificationTitle = 'Booking Confirmed - Payment Required';
            $notificationMessage = "Your booking for {$vehicleName} (Ref: {$booking['booking_reference']}) has been confirmed! " .
                                   "Total amount: $" . number_format($finalTotalAmount, 2) . ". " .
                                   "Please proceed with payment to secure your booking.";
        }

        // Send in-app notification to customer
        db()->execute(
            "INSERT INTO notifications (user_id, title, message, type, created_at)
             VALUES (?, ?, ?, 'booking', NOW())",
            [$booking['customer_id'], $notificationTitle, $notificationMessage]
        );

        // Send email notification to customer
        if ($additionalCharges > 0) {
            // Email for additional charges requiring approval
            $emailSubject = "Booking Update - Approval Required (Ref: {$booking['booking_reference']})";
            $emailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #FFD700; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #dee2e6; border-top: none; }
        .alert { background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; }
        .price-breakdown { background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0; }
        .price-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .price-total { font-size: 1.3em; font-weight: bold; color: #28a745; padding-top: 15px; }
        .reason-box { background: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin: 20px 0; }
        .button { display: inline-block; padding: 12px 30px; background: #ff9800; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        .button-secondary { background: #dc3545; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 0.9em; color: #666; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0; color: #FFD700;'>⚠️ Booking Update Required</h1>
            <p style='margin: 10px 0 0 0; color: #ffffff;'>Elite Car Hire</p>
        </div>

        <div class='content'>
            <div class='alert'>
                <strong>Action Required:</strong> The owner has updated your booking with additional charges. Please review and approve to proceed.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Your booking has been reviewed by the vehicle owner, and additional charges have been added to your booking.</p>

            <p><strong>Booking Details:</strong></p>
            <ul>
                <li><strong>Reference:</strong> {$booking['booking_reference']}</li>
                <li><strong>Vehicle:</strong> {$vehicleName}</li>
                <li><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</li>
            </ul>

            <div class='price-breakdown'>
                <h3 style='margin-top: 0;'>Price Breakdown</h3>
                <div class='price-row'>
                    <span>Original Booking Amount:</span>
                    <strong>$" . number_format($booking['base_amount'], 2) . "</strong>
                </div>
                <div class='price-row' style='color: #ff9800;'>
                    <span><strong>Additional Charges:</strong></span>
                    <strong>+ $" . number_format($additionalCharges, 2) . "</strong>
                </div>
                <div class='price-row price-total'>
                    <span>New Total Amount:</span>
                    <span>$" . number_format($finalTotalAmount, 2) . "</span>
                </div>
            </div>

            <div class='reason-box'>
                <h4 style='margin-top: 0;'>Reason for Additional Charges:</h4>
                <p style='margin: 0; white-space: pre-wrap;'>{$additionalChargesReason}</p>
            </div>

            <p><strong>What You Need to Do:</strong></p>
            <ol>
                <li>Log in to your Elite Car Hire account</li>
                <li>Go to \"My Bookings\"</li>
                <li>Click on \"Needs Approval\" to view this booking</li>
                <li>Review the additional charges and reason</li>
                <li>Choose to approve or reject the changes</li>
            </ol>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings?status=awaiting_approval' class='button'>Review Booking Now</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'>
                <strong>Note:</strong> If you approve the changes, you'll proceed to payment for the updated total.
                If you reject the changes, the booking will be cancelled.
            </p>
        </div>

        <div class='footer'>
            <p>This is an automated message from Elite Car Hire.</p>
            <p>Please do not reply to this email.</p>
            <p>If you have any questions, please contact us through your account dashboard.</p>
        </div>
    </div>
</body>
</html>";

            sendEmail($booking['email'], $emailSubject, $emailBody);
        } else {
            // Email for direct confirmation (no additional charges)
            $emailSubject = "Booking Confirmed - Payment Required (Ref: {$booking['booking_reference']})";
            $emailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #FFD700; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #dee2e6; border-top: none; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; color: #155724; }
        .button { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 0.9em; color: #666; border-radius: 0 0 8px 8px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0; color: #FFD700;'>✓ Booking Confirmed</h1>
            <p style='margin: 10px 0 0 0; color: #ffffff;'>Elite Car Hire</p>
        </div>

        <div class='content'>
            <div class='success'>
                <strong>Great News!</strong> Your booking has been confirmed by the vehicle owner.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Your booking for <strong>{$vehicleName}</strong> has been confirmed!</p>

            <p><strong>Booking Details:</strong></p>
            <ul>
                <li><strong>Reference:</strong> {$booking['booking_reference']}</li>
                <li><strong>Vehicle:</strong> {$vehicleName}</li>
                <li><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</li>
                <li><strong>Total Amount:</strong> $" . number_format($finalTotalAmount, 2) . "</li>
            </ul>

            <p><strong>Next Step:</strong> Please proceed with payment to secure your booking.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings' class='button'>Make Payment Now</a>
            </div>
        </div>

        <div class='footer'>
            <p>This is an automated message from Elite Car Hire.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>";

            sendEmail($booking['email'], $emailSubject, $emailBody);
        }

        // Prepare success message for owner
        $successMessage = '';
        if ($additionalCharges > 0) {
            $successMessage = 'Booking updated with additional charges of $' . number_format($additionalCharges, 2) . '. ' .
                            'New total: $' . number_format($finalTotalAmount, 2) . '. ' .
                            'Customer has been notified and must approve the changes before proceeding to payment.';
        } else {
            // No additional charges - booking directly confirmed
            $successMessage = 'Booking confirmed! Customer has been notified and can now proceed to payment for $' . number_format($finalTotalAmount, 2) . '.';
        }

        flash('success', $successMessage);
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
            } catch (\Exception $e) {
                error_log("Notification error in cancelBooking: " . $e->getMessage());
            } catch (\Error $e) {
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

        // Ensure vehicles is an array
        if (!is_array($vehicles)) {
            error_log("OwnerController::calendar() - vehicles fetchAll returned non-array: " . gettype($vehicles));
            $vehicles = [];
        }

        $blockedDates = db()->fetchAll("SELECT vbd.*, v.make, v.model, v.registration_number
                                        FROM vehicle_blocked_dates vbd
                                        JOIN vehicles v ON vbd.vehicle_id = v.id
                                        WHERE vbd.owner_id = ?
                                        ORDER BY vbd.start_date DESC", [$ownerId]);

        // Ensure blockedDates is an array
        if (!is_array($blockedDates)) {
            error_log("OwnerController::calendar() - blockedDates fetchAll returned non-array: " . gettype($blockedDates));
            $blockedDates = [];
        }

        // Generate colors for vehicles (consistent color for each vehicle)
        $colors = [];
        $predefinedColors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
        $colorIndex = 0;
        foreach ($vehicles as $vehicle) {
            $colors[$vehicle['id']] = $predefinedColors[$colorIndex % count($predefinedColors)];
            $colorIndex++;
        }

        view('owner/calendar', compact('vehicles', 'blockedDates', 'colors'));
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

        try {

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
        } catch (\Exception $e) {
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

        } catch (\Exception $e) {
            error_log("Error in blockDates: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            if ($isAjax) {
                if (ob_get_level()) ob_get_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'An error occurred while blocking dates. Please try again.']);
                exit;
            } else {
                flash('error', 'An error occurred while blocking dates. Please try again.');
                redirect('/owner/calendar');
            }
        }
    }

    private function generateBlockDates($startDate, $endDate, $frequency, $customDays) {
        $dates = [];

        try {
            $current = new \DateTime($startDate);
            $end = new \DateTime($endDate);

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
        } catch (\Exception $e) {
            error_log("Error in generateBlockDates: " . $e->getMessage());
            return [];
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
        } catch (\Exception $e) {
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

    public function notifications() {
        requireAuth('owner');
        $ownerId = $_SESSION['user_id'];

        $notifications = db()->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
            [$ownerId]
        );

        view('owner/notifications', compact('notifications'));
    }

    public function markNotificationRead($id) {
        requireAuth('owner');
        $ownerId = $_SESSION['user_id'];

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/owner/notifications');
        }

        db()->execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$id, $ownerId]
        );

        flash('success', 'Notification marked as read');
        redirect('/owner/notifications');
    }
}
