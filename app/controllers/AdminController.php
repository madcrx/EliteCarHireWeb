<?php
namespace controllers;

class AdminController {
    public function __construct() {
        requireAuth('admin');
    }
    
    public function dashboard() {
        $stats = [
            'total_users' => db()->fetch("SELECT COUNT(*) as count FROM users")['count'],
            'pending_users' => db()->fetch("SELECT COUNT(*) as count FROM users WHERE status='pending'")['count'],
            'total_vehicles' => db()->fetch("SELECT COUNT(*) as count FROM vehicles")['count'],
            'pending_vehicles' => db()->fetch("SELECT COUNT(*) as count FROM vehicles WHERE status='pending'")['count'],
            'total_bookings' => db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE MONTH(created_at) = MONTH(NOW())")['count'],
            'total_revenue' => db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM bookings WHERE status='completed' AND MONTH(created_at) = MONTH(NOW())")['revenue'],
            'pending_changes' => db()->fetch("SELECT COUNT(*) as count FROM pending_changes WHERE status='pending'")['count'],
            'unread_contacts' => db()->fetch("SELECT COUNT(*) as count FROM contact_submissions WHERE status='new'")['count'],
        ];
        
        $recentBookings = db()->fetchAll("SELECT b.*, u.first_name, u.last_name, v.make, v.model FROM bookings b 
                                          JOIN users u ON b.customer_id = u.id 
                                          JOIN vehicles v ON b.vehicle_id = v.id 
                                          ORDER BY b.created_at DESC LIMIT 10");
        
        $recentUsers = db()->fetchAll("SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC LIMIT 5");
        
        view('admin/dashboard', compact('stats', 'recentBookings', 'recentUsers'));
    }
    
    public function users() {
        $role = $_GET['role'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($role !== 'all') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $users = db()->fetchAll($sql, $params);
        view('admin/users', compact('users', 'role', 'status'));
    }
    
    public function viewUser($id) {
        $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }
        
        $vehicles = db()->fetchAll("SELECT * FROM vehicles WHERE owner_id = ?", [$id]);
        $bookings = db()->fetchAll("SELECT * FROM bookings WHERE customer_id = ? OR owner_id = ? ORDER BY created_at DESC LIMIT 20", [$id, $id]);
        
        view('admin/user-detail', compact('user', 'vehicles', 'bookings'));
    }
    
    public function approveUser($id) {
        db()->execute("UPDATE users SET status = 'active' WHERE id = ?", [$id]);
        logAudit('approve_user', 'users', $id);
        createNotification($id, 'approval', 'Account Approved', 'Your account has been approved and is now active!');
        flash('success', 'User approved successfully');
        redirect('/admin/users');
    }
    
    public function rejectUser($id) {
        db()->execute("UPDATE users SET status = 'rejected' WHERE id = ?", [$id]);
        logAudit('reject_user', 'users', $id);
        createNotification($id, 'rejection', 'Account Rejected', 'Your account application has been rejected.');
        flash('success', 'User rejected');
        redirect('/admin/users');
    }

    public function editUser($id) {
        $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        view('admin/user-edit', compact('user'));
    }

    public function updateUser($id) {
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/users/' . $id);
        }

        $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        // Get common fields
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $status = $_POST['status'] ?? '';
        $role = $_POST['role'] ?? '';

        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email)) {
            flash('error', 'First name, last name, and email are required');
            redirect('/admin/users/' . $id . '/edit');
        }

        // Check if email is already taken by another user
        $existingUser = db()->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
        if ($existingUser) {
            flash('error', 'Email address is already in use by another user');
            redirect('/admin/users/' . $id . '/edit');
        }

        // Update user basic info
        db()->execute("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, status = ?, role = ?, updated_at = NOW() WHERE id = ?",
                     [$firstName, $lastName, $email, $phone, $status, $role, $id]);

        // Handle password change if provided
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                flash('error', 'Passwords do not match');
                redirect('/admin/users/' . $id . '/edit');
            }

            if (strlen($newPassword) < 8) {
                flash('error', 'Password must be at least 8 characters');
                redirect('/admin/users/' . $id . '/edit');
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            db()->execute("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $id]);
            logAudit('change_user_password', 'users', $id);
        }

        logAudit('update_user', 'users', $id, [
            'email' => $email,
            'role' => $role,
            'status' => $status
        ]);

        flash('success', 'User updated successfully');
        redirect('/admin/users');
    }

    public function changeUserStatus($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/users');
        }

        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['active', 'suspended', 'rejected', 'pending'];

        if (!in_array($status, $allowedStatuses)) {
            flash('error', 'Invalid status');
            redirect('/admin/users');
        }

        $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        db()->execute("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);

        // Notify user
        $statusMessages = [
            'active' => 'Your account has been activated',
            'suspended' => 'Your account has been suspended',
            'rejected' => 'Your account has been rejected',
            'pending' => 'Your account is pending review'
        ];
        createNotification($id, 'status_change', 'Account Status Changed', $statusMessages[$status]);

        logAudit('change_user_status', 'users', $id, ['old_status' => $user['status']], ['new_status' => $status]);

        flash('success', 'User status changed to ' . $status);
        redirect('/admin/users');
    }

    public function deleteUser($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/users');
        }

        $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'User not found');
            redirect('/admin/users');
        }

        // Prevent deleting your own account
        if ($id == $_SESSION['user_id']) {
            flash('error', 'You cannot delete your own account');
            redirect('/admin/users');
        }

        // Delete user (cascade delete will handle related records)
        db()->execute("DELETE FROM users WHERE id = ?", [$id]);

        logAudit('delete_user', 'users', $id, $user);

        flash('success', 'User deleted successfully');
        redirect('/admin/users');
    }

    public function vehicles() {
        $status = $_GET['status'] ?? 'all';
        
        $sql = "SELECT v.*, u.first_name, u.last_name FROM vehicles v 
                JOIN users u ON v.owner_id = u.id WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $sql .= " AND v.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY v.created_at DESC";
        
        $vehicles = db()->fetchAll($sql, $params);
        view('admin/vehicles', compact('vehicles', 'status'));
    }
    
    public function approveVehicle($id) {
        // Get full vehicle and owner details
        $vehicle = db()->fetch(
            "SELECT v.*, u.email as owner_email, u.first_name as owner_first_name, u.last_name as owner_last_name
             FROM vehicles v
             JOIN users u ON v.owner_id = u.id
             WHERE v.id = ?",
            [$id]
        );

        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        db()->execute("UPDATE vehicles SET status = 'approved' WHERE id = ?", [$id]);
        logAudit('approve_vehicle', 'vehicles', $id);
        createNotification($vehicle['owner_id'], 'approval', 'Vehicle Approved', 'Your vehicle listing has been approved!');

        // Send approval email
        $this->sendVehicleApprovalEmail($vehicle);

        flash('success', 'Vehicle approved');
        redirect('/admin/vehicles');
    }

    public function rejectVehicle($id) {
        // Get full vehicle and owner details
        $vehicle = db()->fetch(
            "SELECT v.*, u.email as owner_email, u.first_name as owner_first_name, u.last_name as owner_last_name
             FROM vehicles v
             JOIN users u ON v.owner_id = u.id
             WHERE v.id = ?",
            [$id]
        );

        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        // Get rejection reason from POST
        $reason = $_POST['rejection_reason'] ?? 'Your vehicle listing did not meet our approval criteria.';

        db()->execute("UPDATE vehicles SET status = 'rejected', rejection_reason = ? WHERE id = ?", [$reason, $id]);
        logAudit('reject_vehicle', 'vehicles', $id, null, ['reason' => $reason]);
        createNotification($vehicle['owner_id'], 'rejection', 'Vehicle Rejected', 'Your vehicle listing has been rejected.');

        // Send rejection email
        $this->sendVehicleRejectionEmail($vehicle, $reason);

        flash('success', 'Vehicle rejected');
        redirect('/admin/vehicles');
    }

    private function sendVehicleApprovalEmail($vehicle) {
        $vehicleName = "{$vehicle['year']} {$vehicle['make']} {$vehicle['model']}";
        $viewUrl = generateLoginUrl("/owner/listings");
        $viewButton = getEmailButton($viewUrl, 'View My Listings', 'success');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #4caf50;'>✓ Vehicle Listing Approved!</h2>
            <p>Dear {$vehicle['owner_first_name']},</p>
            <p>Great news! Your vehicle listing has been approved and is now live on Elite Car Hire.</p>

            <div style='background: #e8f5e9; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #2e7d32;'>Approved Vehicle</h3>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Color:</strong> {$vehicle['color']}</p>
                <p><strong>Category:</strong> {$vehicle['category']}</p>
                <p><strong>Hourly Rate:</strong> \$" . number_format($vehicle['hourly_rate'], 2) . " AUD</p>
                <p><strong>Registration:</strong> " . ($vehicle['registration_number'] ?? 'Not provided') . "</p>
                <p><strong>Status:</strong> <span style='color: #4caf50; font-weight: bold;'>APPROVED & LIVE</span></p>
            </div>

            <div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0;'>
                <p style='margin: 0;'><strong>📢 Your vehicle is now visible to customers!</strong> You can start receiving booking requests immediately. Make sure your calendar is up to date with any blocked dates.</p>
            </div>

            {$viewButton}

            <div style='margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;'>
                <h3 style='margin-top: 0;'>Next Steps:</h3>
                <ul style='margin: 10px 0; padding-left: 20px;'>
                    <li>Review your vehicle details and make any updates if needed</li>
                    <li>Block any dates when your vehicle is unavailable</li>
                    <li>Respond promptly to booking requests</li>
                    <li>Keep your vehicle well-maintained for customer satisfaction</li>
                </ul>
            </div>

            <p>If you have any questions, please contact us at vehicles@elitecarhire.au or call 0406 907 849.</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($vehicle['owner_email'], "Vehicle Approved - {$vehicleName}", $body);

        // Also notify admin at vehicles email
        $vehiclesEmail = config('email.vehicle_approvals', 'vehicles@elitecarhire.au');
        $adminBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #4caf50;'>🚗 Vehicle Listing Approved</h2>
            <p>A vehicle listing has been approved.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #4caf50; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Vehicle Details</h3>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Owner:</strong> {$vehicle['owner_first_name']} {$vehicle['owner_last_name']} ({$vehicle['owner_email']})</p>
                <p><strong>Color:</strong> {$vehicle['color']}</p>
                <p><strong>Category:</strong> {$vehicle['category']}</p>
                <p><strong>Hourly Rate:</strong> \$" . number_format($vehicle['hourly_rate'], 2) . " AUD</p>
                <p><strong>Registration:</strong> " . ($vehicle['registration_number'] ?? 'Not provided') . "</p>
            </div>

            <p style='margin-top: 30px;'>This is an automated notification from Elite Car Hire.</p>
        </div>
        ";

        sendEmail($vehiclesEmail, "Vehicle Approved - {$vehicleName}", $adminBody);
    }

    private function sendVehicleRejectionEmail($vehicle, $reason) {
        $vehicleName = "{$vehicle['year']} {$vehicle['make']} {$vehicle['model']}";
        $viewUrl = generateLoginUrl("/owner/listings");
        $viewButton = getEmailButton($viewUrl, 'View My Listings', 'primary');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #e74c3c;'>Vehicle Listing Not Approved</h2>
            <p>Dear {$vehicle['owner_first_name']},</p>
            <p>Thank you for submitting your vehicle listing to Elite Car Hire. Unfortunately, we are unable to approve your listing at this time.</p>

            <div style='background: #ffebee; padding: 20px; border-left: 4px solid #e74c3c; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Vehicle Details</h3>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Color:</strong> {$vehicle['color']}</p>
                <p><strong>Category:</strong> {$vehicle['category']}</p>
                <p><strong>Hourly Rate:</strong> \$" . number_format($vehicle['hourly_rate'], 2) . " AUD</p>
                <p><strong>Status:</strong> <span style='color: #e74c3c; font-weight: bold;'>REJECTED</span></p>
            </div>

            <div style='background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Rejection Reason:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>

            <div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0;'>
                <p style='margin: 0;'><strong>What you can do:</strong> You can update your listing to address the issues mentioned above and resubmit it for approval. Our team will review it again.</p>
            </div>

            {$viewButton}

            <p>If you have any questions about this decision or need clarification on the requirements, please contact us at vehicles@elitecarhire.au or call 0406 907 849.</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($vehicle['owner_email'], "Vehicle Listing Status - {$vehicleName}", $body);

        // Also notify admin at vehicles email
        $vehiclesEmail = config('email.vehicle_approvals', 'vehicles@elitecarhire.au');
        $adminBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #e74c3c;'>🚗 Vehicle Listing Rejected</h2>
            <p>A vehicle listing has been rejected.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #e74c3c; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Vehicle Details</h3>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Owner:</strong> {$vehicle['owner_first_name']} {$vehicle['owner_last_name']} ({$vehicle['owner_email']})</p>
                <p><strong>Color:</strong> {$vehicle['color']}</p>
                <p><strong>Category:</strong> {$vehicle['category']}</p>
                <p><strong>Hourly Rate:</strong> \$" . number_format($vehicle['hourly_rate'], 2) . " AUD</p>
            </div>

            <div style='background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Rejection Reason:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>

            <p style='margin-top: 30px;'>This is an automated notification from Elite Car Hire.</p>
        </div>
        ";

        sendEmail($vehiclesEmail, "Vehicle Rejected - {$vehicleName}", $adminBody);
    }

    public function editVehicle($id) {
        requireAuth('admin');

        $vehicle = db()->fetch("SELECT v.*, u.first_name, u.last_name, u.email
                                FROM vehicles v
                                JOIN users u ON v.owner_id = u.id
                                WHERE v.id = ?", [$id]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        view('admin/edit-vehicle', compact('vehicle'));
    }

    public function updateVehicle($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/vehicles/' . $id . '/edit');
        }

        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
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
        $status = $_POST['status'] ?? 'pending';

        // Validate required fields
        if (empty($make) || empty($model) || empty($year) || empty($category) || empty($hourlyRate)) {
            flash('error', 'Make, model, year, category, and hourly rate are required');
            redirect('/admin/vehicles/' . $id . '/edit');
        }

        // Update vehicle
        db()->execute("UPDATE vehicles SET
                      make = ?, model = ?, year = ?, color = ?, category = ?,
                      description = ?, hourly_rate = ?, max_passengers = ?,
                      registration_number = ?, status = ?, updated_at = NOW()
                      WHERE id = ?",
                     [$make, $model, $year, $color, $category, $description,
                      $hourlyRate, $maxPassengers, $registrationNumber, $status, $id]);

        // Notify owner if status changed
        if ($vehicle['status'] !== $status) {
            $statusMessages = [
                'approved' => 'Your vehicle listing has been approved and is now visible to customers',
                'pending' => 'Your vehicle listing status has been changed to pending',
                'rejected' => 'Your vehicle listing has been rejected',
                'inactive' => 'Your vehicle listing has been deactivated'
            ];
            if (isset($statusMessages[$status])) {
                createNotification($vehicle['owner_id'], 'status_change', 'Vehicle Status Updated', $statusMessages[$status]);
            }
        }

        logAudit('update_vehicle', 'vehicles', $id, [
            'make' => $make,
            'model' => $model,
            'status' => $status
        ]);

        flash('success', 'Vehicle updated successfully');
        redirect('/admin/vehicles');
    }

    public function deleteVehicle($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/vehicles');
        }

        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        // Delete vehicle (cascade will handle related records)
        db()->execute("DELETE FROM vehicles WHERE id = ?", [$id]);

        // Notify owner
        createNotification($vehicle['owner_id'], 'notification', 'Vehicle Deleted',
                          'Your vehicle listing (' . $vehicle['make'] . ' ' . $vehicle['model'] . ') has been removed by an administrator.');

        logAudit('delete_vehicle', 'vehicles', $id, $vehicle);

        flash('success', 'Vehicle deleted successfully');
        redirect('/admin/vehicles');
    }
    
    public function bookings() {
        $status = $_GET['status'] ?? 'all';
        $paymentStatus = $_GET['payment_status'] ?? 'all';

        $sql = "SELECT b.*, u.first_name as customer_name, u.last_name as customer_last,
                v.make, v.model, o.first_name as owner_name, o.last_name as owner_last
                FROM bookings b
                JOIN users u ON b.customer_id = u.id
                JOIN vehicles v ON b.vehicle_id = v.id
                JOIN users o ON b.owner_id = o.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }

        if ($paymentStatus !== 'all') {
            $sql .= " AND b.payment_status = ?";
            $params[] = $paymentStatus;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $bookings = db()->fetchAll($sql, $params);
        view('admin/bookings', compact('bookings', 'status', 'paymentStatus'));
    }
    
    public function payments() {
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT p.*, b.booking_reference FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $payments = db()->fetchAll($sql, $params);
        view('admin/payments', compact('payments', 'status'));
    }
    
    public function payouts() {
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT p.*, u.first_name, u.last_name, b.booking_reference
                FROM payouts p
                JOIN users u ON p.owner_id = u.id
                LEFT JOIN bookings b ON p.booking_id = b.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $payouts = db()->fetchAll($sql, $params);
        view('admin/payouts', compact('payouts', 'status'));
    }
    
    public function disputes() {
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT d.*, b.booking_reference, u.first_name, u.last_name
                FROM disputes d
                JOIN bookings b ON d.booking_id = b.id
                JOIN users u ON d.raised_by = u.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY d.created_at DESC";

        $disputes = db()->fetchAll($sql, $params);
        view('admin/disputes', compact('disputes', 'status'));
    }
    
    public function analytics() {
        $monthlyRevenue = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                                          SUM(total_amount) as revenue, COUNT(*) as bookings 
                                          FROM bookings WHERE status='completed' 
                                          GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                          ORDER BY month DESC LIMIT 12");
        
        $topVehicles = db()->fetchAll("SELECT v.make, v.model, COUNT(b.id) as booking_count, 
                                        SUM(b.total_amount) as revenue 
                                        FROM vehicles v 
                                        LEFT JOIN bookings b ON v.id = b.vehicle_id 
                                        WHERE b.status='completed' 
                                        GROUP BY v.id 
                                        ORDER BY booking_count DESC LIMIT 10");
        
        view('admin/analytics', compact('monthlyRevenue', 'topVehicles'));
    }
    
    public function security() {
        $alerts = db()->fetchAll("SELECT * FROM security_alerts ORDER BY created_at DESC LIMIT 50");
        view('admin/security', compact('alerts'));
    }
    
    public function auditLogs() {
        $logs = db()->fetchAll("SELECT a.*, u.email FROM audit_logs a 
                                LEFT JOIN users u ON a.user_id = u.id 
                                ORDER BY a.created_at DESC LIMIT 100");
        view('admin/audit-logs', compact('logs'));
    }
    
    public function cms() {
        $pages = db()->fetchAll("SELECT * FROM cms_pages ORDER BY page_key");
        view('admin/cms', compact('pages'));
    }
    
    public function saveCms() {
        $pageId = $_POST['page_id'] ?? null;
        $content = $_POST['content'] ?? '';
        
        if ($pageId) {
            db()->execute("UPDATE cms_pages SET content = ?, updated_by = ? WHERE id = ?", 
                         [$content, $_SESSION['user_id'], $pageId]);
            logAudit('update_cms', 'cms_pages', $pageId);
            flash('success', 'Content updated successfully');
        }
        redirect('/admin/cms');
    }
    
    public function settings() {
        $settings = db()->fetchAll("SELECT * FROM settings ORDER BY setting_key");
        view('admin/settings', compact('settings'));
    }

    public function saveSettings() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/settings');
        }

        // Get all settings from the form
        $settingKeys = $_POST['setting_keys'] ?? [];
        $settingValues = $_POST['setting_values'] ?? [];

        if (empty($settingKeys)) {
            flash('error', 'No settings to save');
            redirect('/admin/settings');
        }

        // Update each setting
        foreach ($settingKeys as $index => $key) {
            $value = $settingValues[$index] ?? '';

            // Update or insert the setting
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);

            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                             [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)",
                             [$key, $value]);
            }

            logAudit('update_setting', 'settings', null, [
                'setting_key' => $key,
                'setting_value' => $value
            ]);
        }

        flash('success', 'Settings saved successfully');
        redirect('/admin/settings');
    }

    public function uploadLogo() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/settings');
        }

        // Check if file was uploaded
        if (!isset($_FILES['logo_file']) || $_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'No file uploaded or upload error occurred');
            redirect('/admin/settings');
        }

        // Validate file type
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
        $fileType = $_FILES['logo_file']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            flash('error', 'Invalid file type. Only PNG, JPG, and SVG allowed');
            redirect('/admin/settings');
        }

        // Validate file size (2MB max)
        $maxSize = 2 * 1024 * 1024;
        if ($_FILES['logo_file']['size'] > $maxSize) {
            flash('error', 'File too large. Maximum size is 2MB');
            redirect('/admin/settings');
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../storage/uploads/logo/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate filename
        $extension = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
        $filename = 'company-logo.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $webPath = '/storage/uploads/logo/' . $filename;

        // Delete old logo if exists
        $currentLogo = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
        if ($currentLogo && $currentLogo['setting_value']) {
            $oldPath = __DIR__ . '/../../..' . $currentLogo['setting_value'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadPath)) {
            flash('error', 'Failed to upload file');
            redirect('/admin/settings');
        }

        // Update database
        $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = 'company_logo'");
        if ($existing) {
            db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'company_logo'", [$webPath]);
        } else {
            db()->execute("INSERT INTO settings (setting_key, setting_value) VALUES ('company_logo', ?)", [$webPath]);
        }

        logAudit('upload_company_logo', 'settings', null, ['logo_path' => $webPath]);

        flash('success', 'Company logo uploaded successfully');
        redirect('/admin/settings');
    }

    public function removeLogo() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/settings');
        }

        // Get current logo
        $currentLogo = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
        if ($currentLogo && $currentLogo['setting_value']) {
            // Delete file
            $oldPath = __DIR__ . '/../../..' . $currentLogo['setting_value'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }

            // Remove from database
            db()->execute("DELETE FROM settings WHERE setting_key = 'company_logo'");

            logAudit('remove_company_logo', 'settings', null);

            flash('success', 'Company logo removed successfully');
        } else {
            flash('error', 'No logo to remove');
        }

        redirect('/admin/settings');
    }

    public function pendingChanges() {
        $changes = db()->fetchAll("SELECT pc.*, u.first_name, u.last_name 
                                    FROM pending_changes pc 
                                    JOIN users u ON pc.owner_id = u.id 
                                    WHERE pc.status = 'pending' 
                                    ORDER BY pc.created_at DESC");
        view('admin/pending-changes', compact('changes'));
    }
    
    public function approvePendingChange($id) {
        $change = db()->fetch("SELECT * FROM pending_changes WHERE id = ?", [$id]);

        if ($change && $change['status'] === 'pending') {
            $newData = json_decode($change['new_data'], true);

            // Apply the change based on entity type
            if ($change['entity_type'] === 'vehicle') {
                $fields = [];
                $values = [];
                foreach ($newData as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $change['entity_id'];

                $sql = "UPDATE vehicles SET " . implode(', ', $fields) . " WHERE id = ?";
                db()->execute($sql, $values);
            } elseif ($change['entity_type'] === 'booking' && $change['change_type'] === 'cancellation') {
                // Get full booking details before cancellation
                $booking = db()->fetch(
                    "SELECT b.*, v.year, v.make, v.model, v.hourly_rate,
                            c.email as customer_email, c.first_name as customer_first_name, c.last_name as customer_last_name,
                            o.email as owner_email, o.first_name as owner_first_name, o.last_name as owner_last_name
                     FROM bookings b
                     JOIN vehicles v ON b.vehicle_id = v.id
                     JOIN users c ON b.customer_id = c.id
                     JOIN users o ON b.owner_id = o.id
                     WHERE b.id = ?",
                    [$change['entity_id']]
                );

                if ($booking) {
                    // Calculate refund if payment was made
                    // NEW POLICY: 50% cancellation fee applies to all cancellations regardless of timing
                    $refundAmount = 0;
                    $refundStatus = 'not_applicable';
                    $cancellationFee = 0;

                    if ($booking['payment_status'] === 'paid') {
                        // 50% cancellation fee applies to all paid bookings
                        $cancellationFee = $booking['total_amount'] * 0.5;
                        $refundAmount = $booking['total_amount'] * 0.5;
                        $refundStatus = 'partial_refund';
                    }

                    // Update booking status
                    db()->execute(
                        "UPDATE bookings SET status = 'cancelled', cancellation_reason = ?, cancelled_at = NOW(), refund_amount = ?, refund_status = ?, cancellation_fee = ? WHERE id = ?",
                        [$newData['cancellation_reason'] ?? $change['reason'], $refundAmount, $refundStatus, $cancellationFee, $change['entity_id']]
                    );

                    // Send cancellation emails
                    $this->sendCancellationEmails($booking, $change['reason'], $refundAmount, $refundStatus, $cancellationFee);
                }
            }

            db()->execute("UPDATE pending_changes SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
                         [$_SESSION['user_id'], $id]);

            createNotification($change['owner_id'], 'approval', 'Change Approved', 'Your submitted change has been approved.');
            logAudit('approve_pending_change', 'pending_changes', $id);
            flash('success', 'Change approved successfully');
        }

        redirect('/admin/pending-changes');
    }

    private function sendCancellationEmails($booking, $reason, $refundAmount, $refundStatus, $cancellationFee) {
        $vehicleName = "{$booking['year']} {$booking['make']} {$booking['model']}";

        // Send email to customer
        $this->sendCustomerCancellationEmail($booking, $vehicleName, $reason, $refundAmount, $refundStatus, $cancellationFee);

        // Send email to owner
        $this->sendOwnerCancellationEmail($booking, $vehicleName, $reason);

        // Send email to admin
        $this->sendAdminCancellationEmail($booking, $vehicleName, $reason, $refundAmount, $refundStatus, $cancellationFee);
    }

    private function sendCustomerCancellationEmail($booking, $vehicleName, $reason, $refundAmount, $refundStatus, $cancellationFee) {
        $viewUrl = generateLoginUrl("/customer/bookings");
        $viewButton = getEmailButton($viewUrl, 'View My Bookings', 'primary');

        // Build refund message based on new 50% cancellation fee policy
        $refundMessage = '';
        if ($refundStatus === 'partial_refund' && $refundAmount > 0) {
            $refundMessage = "
            <div style='background: #fff3cd; padding: 20px; border-left: 4px solid #f39c12; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #f39c12;'>💰 Refund Information</h3>
                <p><strong>Original Booking Amount:</strong> \$" . number_format($booking['total_amount'], 2) . " AUD</p>
                <p><strong>Cancellation Fee (50%):</strong> \$" . number_format($cancellationFee, 2) . " AUD</p>
                <p><strong>Refund Amount (50%):</strong> <span style='color: #4caf50; font-weight: bold;'>\$" . number_format($refundAmount, 2) . " AUD</span></p>
                <p style='margin: 10px 0 0 0; font-size: 14px;'><em>Your refund will be processed to your original payment method within 5-7 business days.</em></p>
            </div>

            <div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0;'>
                <p style='margin: 0;'><strong>📋 Cancellation Policy:</strong> A 50% cancellation fee applies to all booking cancellations, regardless of when the cancellation is made. The remaining 50% is refunded to your original payment method.</p>
            </div>";
        } elseif ($refundStatus === 'not_applicable') {
            $refundMessage = "
            <div style='background: #f5f5f5; padding: 15px; border-left: 4px solid #9e9e9e; margin: 20px 0;'>
                <p style='margin: 0;'><strong>No Payment:</strong> This booking was not paid, so no refund is applicable.</p>
            </div>";
        }

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #e74c3c;'>Booking Cancelled</h2>
            <p>Dear {$booking['customer_first_name']},</p>
            <p>Your booking has been cancelled as requested.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #e74c3c; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Cancelled Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Date:</strong> {$booking['booking_date']}</p>
                <p><strong>Time:</strong> {$booking['start_time']} - {$booking['end_time']}</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
                <p><strong>Total Amount:</strong> \$" . number_format($booking['total_amount'], 2) . " AUD</p>
                <p><strong>Status:</strong> <span style='color: #e74c3c; font-weight: bold;'>CANCELLED</span></p>
            </div>

            <div style='background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Cancellation Reason:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>

            {$refundMessage}

            {$viewButton}

            <p>We're sorry this booking didn't work out. We hope to serve you again in the future.</p>

            <p>If you have any questions about this cancellation, please contact us at support@elitecarhire.au or call 0406 907 849.</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($booking['customer_email'], "Booking Cancelled - {$booking['booking_reference']}", $body);
    }

    private function sendOwnerCancellationEmail($booking, $vehicleName, $reason) {
        $viewUrl = generateLoginUrl("/owner/bookings");
        $viewButton = getEmailButton($viewUrl, 'View My Bookings', 'primary');

        $earningsLost = $booking['payment_status'] === 'paid'
            ? $booking['total_amount'] - $booking['commission_amount']
            : 0;

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #e74c3c;'>Booking Cancelled</h2>
            <p>Dear {$booking['owner_first_name']},</p>
            <p>A booking for your vehicle has been cancelled.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #e74c3c; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Cancelled Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Customer:</strong> {$booking['customer_first_name']} {$booking['customer_last_name']}</p>
                <p><strong>Date:</strong> {$booking['booking_date']}</p>
                <p><strong>Time:</strong> {$booking['start_time']} - {$booking['end_time']}</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
                <p><strong>Payment Status:</strong> {$booking['payment_status']}</p>
                " . ($earningsLost > 0 ? "<p><strong>Earnings Lost:</strong> \$" . number_format($earningsLost, 2) . " AUD</p>" : "") . "
                <p><strong>Status:</strong> <span style='color: #e74c3c; font-weight: bold;'>CANCELLED</span></p>
            </div>

            <div style='background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Cancellation Reason:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>

            <div style='background: #e8f5e9; padding: 15px; border-left: 4px solid #4caf50; margin: 20px 0;'>
                <p style='margin: 0;'><strong>Good News:</strong> Your vehicle is now available for this time slot. It may receive new booking requests.</p>
            </div>

            {$viewButton}

            <p>If you have any questions about this cancellation, please contact us at support@elitecarhire.au or call 0406 907 849.</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($booking['owner_email'], "Booking Cancelled - {$vehicleName}", $body);
    }

    private function sendAdminCancellationEmail($booking, $vehicleName, $reason, $refundAmount, $refundStatus, $cancellationFee) {
        $viewUrl = generateLoginUrl("/admin/bookings");
        $viewButton = getEmailButton($viewUrl, 'View All Bookings', 'primary');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #e74c3c;'>📅 Booking Cancelled</h2>
            <p>A booking cancellation has been processed.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #e74c3c; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Cancelled Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Owner:</strong> {$booking['owner_first_name']} {$booking['owner_last_name']} ({$booking['owner_email']})</p>
                <p><strong>Customer:</strong> {$booking['customer_first_name']} {$booking['customer_last_name']} ({$booking['customer_email']})</p>
                <p><strong>Date:</strong> {$booking['booking_date']}</p>
                <p><strong>Time:</strong> {$booking['start_time']} - {$booking['end_time']}</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
                <p><strong>Total Amount:</strong> \$" . number_format($booking['total_amount'], 2) . " AUD</p>
                <p><strong>Payment Status:</strong> {$booking['payment_status']}</p>
                <p><strong>Refund Status:</strong> {$refundStatus}</p>
                " . ($cancellationFee > 0 ? "<p><strong>Cancellation Fee (50%):</strong> \$" . number_format($cancellationFee, 2) . " AUD</p>" : "") . "
                " . ($refundAmount > 0 ? "<p><strong>Refund Amount (50%):</strong> \$" . number_format($refundAmount, 2) . " AUD</p>" : "") . "
            </div>

            <div style='background: #fff; padding: 15px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Cancellation Reason:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($reason) . "</p>
            </div>

            {$viewButton}

            <p style='margin-top: 30px;'>This is an automated notification from Elite Car Hire.</p>
        </div>
        ";

        $cancellationsEmail = config('email.cancellations', 'cancellations@elitecarhire.au');
        sendEmail($cancellationsEmail, "Booking Cancelled - {$booking['booking_reference']}", $body);
    }
    
    public function contactSubmissions() {
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT * FROM contact_submissions WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";

        $submissions = db()->fetchAll($sql, $params);
        view('admin/contact-submissions', compact('submissions', 'status'));
    }

    public function replyToContact($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/contact-submissions');
        }

        $reply = $_POST['reply'] ?? '';

        if (empty($reply)) {
            flash('error', 'Reply message is required');
            redirect('/admin/contact-submissions');
        }

        // Get contact submission
        $submission = db()->fetch("SELECT * FROM contact_submissions WHERE id = ?", [$id]);
        if (!$submission) {
            flash('error', 'Contact submission not found');
            redirect('/admin/contact-submissions');
        }

        // Update submission with reply
        db()->execute("UPDATE contact_submissions SET response_text = ?, responded_at = NOW(), responded_by = ?, status = 'responded' WHERE id = ?",
                     [$reply, $_SESSION['user_id'], $id]);

        // Send email to user
        $emailBody = "
            <h2>Reply to your inquiry</h2>
            <p>Dear {$submission['name']},</p>
            <p>Thank you for contacting Elite Car Hire. Here is our response:</p>
            <div style='background: #f5f5f5; padding: 15px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                " . nl2br(e($reply)) . "
            </div>
            <p>If you have any further questions, please don't hesitate to contact us.</p>
            <p>Best regards,<br>Elite Car Hire Team<br>Phone: 0406 907 849<br>Email: support@elitecarhire.au</p>
        ";

        sendEmail($submission['email'], 'Re: ' . ($submission['subject'] ?? 'Your inquiry'), $emailBody);

        logAudit('reply_contact_submission', 'contact_submissions', $id, null, ['reply' => $reply]);

        flash('success', 'Reply sent successfully');
        redirect('/admin/contact-submissions');
    }

    public function updateContactStatus($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/contact-submissions');
        }

        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['new', 'read', 'responded', 'archived'];

        if (!in_array($status, $allowedStatuses)) {
            flash('error', 'Invalid status');
            redirect('/admin/contact-submissions');
        }

        db()->execute("UPDATE contact_submissions SET status = ? WHERE id = ?", [$status, $id]);

        logAudit('update_contact_status', 'contact_submissions', $id, null, ['status' => $status]);

        flash('success', 'Status updated successfully');
        redirect('/admin/contact-submissions');
    }

    public function deleteContactSubmission($id) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/contact-submissions');
        }

        $submission = db()->fetch("SELECT * FROM contact_submissions WHERE id = ?", [$id]);
        if (!$submission) {
            flash('error', 'Contact submission not found');
            redirect('/admin/contact-submissions');
        }

        db()->execute("DELETE FROM contact_submissions WHERE id = ?", [$id]);

        logAudit('delete_contact_submission', 'contact_submissions', $id, $submission);

        flash('success', 'Contact submission deleted successfully');
        redirect('/admin/contact-submissions');
    }

    // Vehicle Image Management Methods

    public function uploadVehicleImages($vehicleId) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/vehicles/' . $vehicleId . '/edit');
        }

        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$vehicleId]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        // Check if images exist
        $hasExistingImages = db()->fetch("SELECT COUNT(*) as count FROM vehicle_images WHERE vehicle_id = ?", [$vehicleId])['count'] > 0;

        // Handle image uploads
        if (empty($_FILES['images']['name'][0])) {
            flash('error', 'No images selected');
            redirect('/admin/vehicles/' . $vehicleId . '/edit');
        }

        $uploadedCount = 0;
        $errors = [];

        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error for {$name}";
                continue;
            }

            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key],
            ];

            // Validate file type by MIME type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array(strtolower($file['type']), $allowedTypes)) {
                $errors[] = "{$name}: Invalid file type ({$file['type']})";
                continue;
            }

            // Validate file size - use PHP's upload limit (currently 2MB)
            $phpMaxSize = $this->parseSize(ini_get('upload_max_filesize'));
            $configMaxSize = 5 * 1024 * 1024; // 5MB from config
            $maxSize = min($phpMaxSize, $configMaxSize);

            if ($file['size'] > $maxSize) {
                $maxMB = round($maxSize / 1024 / 1024, 1);
                $actualMB = round($file['size'] / 1024 / 1024, 2);
                $errors[] = "{$name}: File too large ({$actualMB}MB, max {$maxMB}MB - server limit)";
                continue;
            }

            $path = uploadFile($file, 'vehicles');
            if ($path) {
                // Set first image as primary if no existing images
                $isPrimary = (!$hasExistingImages && $key === 0) ? 1 : 0;

                db()->execute("INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, display_order) VALUES (?, ?, ?, ?)",
                             [$vehicleId, $path, $isPrimary, $key]);
                $uploadedCount++;
            } else {
                $errors[] = "{$name}: Upload failed (check server permissions)";
            }
        }

        if ($uploadedCount > 0) {
            logAudit('upload_vehicle_images', 'vehicles', $vehicleId, ['count' => $uploadedCount]);
            flash('success', $uploadedCount . ' image(s) uploaded successfully');

            if (!empty($errors)) {
                flash('warning', 'Some images failed: ' . implode(', ', $errors));
            }
        } else {
            if (!empty($errors)) {
                flash('error', 'Upload failed: ' . implode('; ', $errors));
            } else {
                flash('error', 'No valid images were uploaded. Please ensure files are JPG, PNG, or WebP format and under 5MB.');
            }
        }

        redirect('/admin/vehicles/' . $vehicleId . '/edit');
    }

    public function setVehicleImagePrimary($vehicleId, $imageId) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/vehicles/' . $vehicleId . '/edit');
        }

        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$vehicleId]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        $image = db()->fetch("SELECT * FROM vehicle_images WHERE id = ? AND vehicle_id = ?", [$imageId, $vehicleId]);
        if (!$image) {
            flash('error', 'Image not found');
            redirect('/admin/vehicles/' . $vehicleId . '/edit');
        }

        // Remove primary status from all images for this vehicle
        db()->execute("UPDATE vehicle_images SET is_primary = 0 WHERE vehicle_id = ?", [$vehicleId]);

        // Set this image as primary
        db()->execute("UPDATE vehicle_images SET is_primary = 1 WHERE id = ?", [$imageId]);

        logAudit('set_primary_vehicle_image', 'vehicle_images', $imageId, ['vehicle_id' => $vehicleId]);

        flash('success', 'Primary image updated successfully');
        redirect('/admin/vehicles/' . $vehicleId . '/edit');
    }

    public function deleteVehicleImage($vehicleId, $imageId) {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/vehicles/' . $vehicleId . '/edit');
        }

        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$vehicleId]);
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/admin/vehicles');
        }

        $image = db()->fetch("SELECT * FROM vehicle_images WHERE id = ? AND vehicle_id = ?", [$imageId, $vehicleId]);
        if (!$image) {
            flash('error', 'Image not found');
            redirect('/admin/vehicles/' . $vehicleId . '/edit');
        }

        $wasPrimary = $image['is_primary'];

        // Delete the image record
        db()->execute("DELETE FROM vehicle_images WHERE id = ?", [$imageId]);

        // Delete the physical file
        $filePath = __DIR__ . '/../../' . $image['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // If this was the primary image, set the first remaining image as primary
        if ($wasPrimary) {
            $firstImage = db()->fetch("SELECT id FROM vehicle_images WHERE vehicle_id = ? ORDER BY display_order LIMIT 1", [$vehicleId]);
            if ($firstImage) {
                db()->execute("UPDATE vehicle_images SET is_primary = 1 WHERE id = ?", [$firstImage['id']]);
            }
        }

        logAudit('delete_vehicle_image', 'vehicle_images', $imageId, ['vehicle_id' => $vehicleId]);

        flash('success', 'Image deleted successfully');
        redirect('/admin/vehicles/' . $vehicleId . '/edit');
    }

    /**
     * Parse PHP size values like "2M", "512K" to bytes
     */
    private function parseSize($size) {
        $unit = strtoupper(substr($size, -1));
        $value = (int) $size;

        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }

        return $value;
    }

    // ===== Communication Management =====

    public function emailSettings() {

        $pageTitle = 'Email Settings - SMTP Configuration';
        $emailConfig = [
            'smtp_host' => getenv('SMTP_HOST') ?: '',
            'smtp_port' => getenv('SMTP_PORT') ?: '587',
            'smtp_user' => getenv('SMTP_USER') ?: '',
            'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        ];

        view('admin/email-settings', compact('pageTitle', 'emailConfig'));
    }

    public function saveEmailSettings() {
        // TODO: Implement email settings save functionality
        flash('info', 'Email settings functionality coming soon');
        redirect('/admin/email-settings');
    }

    public function emailQueue() {

        $pageTitle = 'Email Queue';
        $status = $_GET['status'] ?? 'all';

        $query = "SELECT * FROM email_queue WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY created_at DESC LIMIT 100";

        $emails = db()->fetchAll($query, $params);

        view('admin/email-queue', compact('pageTitle', 'emails', 'status'));
    }

    // ===== Analytics =====

    public function analyticsRevenue() {

        $pageTitle = 'Revenue Reports';

        // Get revenue statistics
        $totalRevenue = db()->fetch("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")['total'] ?? 0;
        $monthlyRevenue = db()->fetch("SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())")['total'] ?? 0;

        view('admin/analytics-revenue', compact('pageTitle', 'totalRevenue', 'monthlyRevenue'));
    }

    public function analyticsBookings() {

        $pageTitle = 'Booking Analytics';

        // Get booking statistics
        $totalBookings = db()->fetch("SELECT COUNT(*) as count FROM bookings")['count'] ?? 0;
        $completedBookings = db()->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'")['count'] ?? 0;

        view('admin/analytics-bookings', compact('pageTitle', 'totalBookings', 'completedBookings'));
    }

    public function analyticsVehicles() {

        $pageTitle = 'Vehicle Performance';

        // Get vehicle statistics
        $topVehicles = db()->fetchAll("
            SELECT v.*, COUNT(b.id) as booking_count
            FROM vehicles v
            LEFT JOIN bookings b ON v.id = b.vehicle_id
            GROUP BY v.id
            ORDER BY booking_count DESC
            LIMIT 10
        ");

        view('admin/analytics-vehicles', compact('pageTitle', 'topVehicles'));
    }

    public function analyticsUsers() {

        $pageTitle = 'User Statistics';

        // Get user statistics
        $totalUsers = db()->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        $customerCount = db()->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'] ?? 0;
        $ownerCount = db()->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'owner'")['count'] ?? 0;

        view('admin/analytics-users', compact('pageTitle', 'totalUsers', 'customerCount', 'ownerCount'));
    }

    // ===== Settings Management =====

    public function settingsPayment() {

        $pageTitle = 'Payment Settings - Stripe Configuration';

        $stripeConfig = [
            'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
            'has_secret_key' => !empty(getenv('STRIPE_SECRET_KEY')),
            'webhook_configured' => !empty(getenv('STRIPE_WEBHOOK_SECRET')),
        ];

        view('admin/settings-payment', compact('pageTitle', 'stripeConfig'));
    }

    public function saveSettingsPayment() {
        // TODO: Implement payment settings save functionality
        flash('info', 'Payment settings functionality coming soon');
        redirect('/admin/settings/payment');
    }

    public function settingsEmail() {

        $pageTitle = 'Email Configuration - SMTP & Templates';
        view('admin/settings-email', compact('pageTitle'));
    }

    public function saveSettingsEmail() {
        // TODO: Implement email settings save functionality
        flash('info', 'Email configuration functionality coming soon');
        redirect('/admin/settings/email');
    }

    public function settingsCommission() {

        $pageTitle = 'Commission Rates - Platform Fees';

        $currentRate = config('payment.commission_rate', 15.00);

        view('admin/settings-commission', compact('pageTitle', 'currentRate'));
    }

    public function saveSettingsCommission() {
        // TODO: Implement commission settings save functionality
        flash('info', 'Commission settings functionality coming soon');
        redirect('/admin/settings/commission');
    }

    public function settingsBooking() {

        $pageTitle = 'Booking Settings - Rules & Policies';
        view('admin/settings-booking', compact('pageTitle'));
    }

    public function saveSettingsBooking() {
        // TODO: Implement booking settings save functionality
        flash('info', 'Booking settings functionality coming soon');
        redirect('/admin/settings/booking');
    }

    public function settingsNotifications() {

        $pageTitle = 'Notification Settings - Email Notifications';
        view('admin/settings-notifications', compact('pageTitle'));
    }

    public function saveSettingsNotifications() {
        // TODO: Implement notification settings save functionality
        flash('info', 'Notification settings functionality coming soon');
        redirect('/admin/settings/notifications');
    }

    // ===== Logs Management =====

    public function logsPayment() {

        $pageTitle = 'Payment Logs';

        // Get recent payment transactions
        $paymentLogs = db()->fetchAll("
            SELECT p.*, b.booking_reference, u.email as customer_email
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.id
            LEFT JOIN users u ON b.customer_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 100
        ");

        view('admin/logs-payment', compact('pageTitle', 'paymentLogs'));
    }

    public function logsEmail() {

        $pageTitle = 'Email Logs';

        // Get recent email activity
        $emailLogs = db()->fetchAll("
            SELECT * FROM email_queue
            ORDER BY created_at DESC
            LIMIT 100
        ");

        view('admin/logs-email', compact('pageTitle', 'emailLogs'));
    }

    public function logsLogin() {

        $pageTitle = 'Login History';

        // Get recent login attempts from audit logs
        $loginLogs = db()->fetchAll("
            SELECT a.*, u.email, u.first_name, u.last_name
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.action IN ('login', 'logout', 'login_failed')
            ORDER BY a.created_at DESC
            LIMIT 100
        ");

        view('admin/logs-login', compact('pageTitle', 'loginLogs'));
    }

    // ===== API Methods =====

    public function clearCache() {

        // Clear various cache types
        $cleared = [];

        // Clear session cache (if using file-based sessions)
        if (session_status() === PHP_SESSION_ACTIVE) {
            $cleared[] = 'session';
        }

        // Clear opcode cache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $cleared[] = 'opcache';
        }

        // Clear temp files
        $tempDir = __DIR__ . '/../../storage/cache';
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $cleared[] = 'temp_files';
        }

        logAudit('clear_cache', 'system', 0, ['cleared' => $cleared]);

        json(['success' => true, 'message' => 'Cache cleared successfully', 'cleared' => $cleared]);
    }

    // ===== System Configuration =====

    public function systemConfig() {
        $pageTitle = 'System Configuration';
        view('admin/system-config', compact('pageTitle'));
    }

    public function saveSystemConfig() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/system-config');
        }

        // Build configuration array from POST data
        $config = [];

        // Database Configuration
        if (!empty($_POST['db_host'])) {
            $config['database']['host'] = $_POST['db_host'];
        }
        if (!empty($_POST['db_port'])) {
            $config['database']['port'] = (int)$_POST['db_port'];
        }
        if (!empty($_POST['db_name'])) {
            $config['database']['name'] = $_POST['db_name'];
        }
        if (!empty($_POST['db_user'])) {
            $config['database']['username'] = $_POST['db_user'];
        }
        if (!empty($_POST['db_password'])) {
            $config['database']['password'] = $_POST['db_password'];
        }
        if (!empty($_POST['db_charset'])) {
            $config['database']['charset'] = $_POST['db_charset'];
        }

        // Email Configuration
        if (isset($_POST['smtp_enabled'])) {
            $config['email']['smtp_enabled'] = $_POST['smtp_enabled'] === '1';
        }
        if (!empty($_POST['smtp_host'])) {
            $config['email']['smtp_host'] = $_POST['smtp_host'];
        }
        if (!empty($_POST['smtp_port'])) {
            $config['email']['smtp_port'] = (int)$_POST['smtp_port'];
        }
        if (!empty($_POST['smtp_username'])) {
            $config['email']['smtp_username'] = $_POST['smtp_username'];
        }
        if (!empty($_POST['smtp_password'])) {
            $config['email']['smtp_password'] = $_POST['smtp_password'];
        }
        if (!empty($_POST['smtp_encryption'])) {
            $config['email']['smtp_encryption'] = $_POST['smtp_encryption'];
        }
        if (!empty($_POST['from_email'])) {
            $config['email']['from_email'] = $_POST['from_email'];
        }
        if (!empty($_POST['from_name'])) {
            $config['email']['from_name'] = $_POST['from_name'];
        }

        // Payment Configuration
        if (!empty($_POST['stripe_mode'])) {
            $config['payment']['stripe_mode'] = $_POST['stripe_mode'];
        }
        if (!empty($_POST['stripe_test_pk'])) {
            $config['payment']['stripe_test_publishable_key'] = $_POST['stripe_test_pk'];
        }
        if (!empty($_POST['stripe_test_sk'])) {
            $config['payment']['stripe_test_secret_key'] = $_POST['stripe_test_sk'];
        }
        if (!empty($_POST['stripe_live_pk'])) {
            $config['payment']['stripe_live_publishable_key'] = $_POST['stripe_live_pk'];
        }
        if (!empty($_POST['stripe_live_sk'])) {
            $config['payment']['stripe_live_secret_key'] = $_POST['stripe_live_sk'];
        }
        if (!empty($_POST['currency'])) {
            $config['payment']['currency'] = $_POST['currency'];
        }

        // Application Configuration
        if (!empty($_POST['app_name'])) {
            $config['app']['name'] = $_POST['app_name'];
        }
        if (!empty($_POST['app_url'])) {
            $config['app']['url'] = rtrim($_POST['app_url'], '/');
        }
        if (!empty($_POST['app_env'])) {
            $config['app']['environment'] = $_POST['app_env'];
        }
        if (isset($_POST['app_debug'])) {
            $config['app']['debug'] = $_POST['app_debug'] === '1';
        }
        if (!empty($_POST['app_timezone'])) {
            $config['app']['timezone'] = $_POST['app_timezone'];
        }
        if (isset($_POST['commission_rate'])) {
            $config['app']['commission_rate'] = (float)$_POST['commission_rate'];
        }

        // Security Configuration
        if (!empty($_POST['session_secret'])) {
            $config['security']['session_secret'] = $_POST['session_secret'];
        }
        if (!empty($_POST['session_lifetime'])) {
            $config['security']['session_lifetime'] = (int)$_POST['session_lifetime'];
        }
        if (isset($_POST['csrf_enabled'])) {
            $config['security']['csrf_protection'] = $_POST['csrf_enabled'] === '1';
        }
        if (!empty($_POST['max_login_attempts'])) {
            $config['security']['max_login_attempts'] = (int)$_POST['max_login_attempts'];
        }
        if (!empty($_POST['lockout_duration'])) {
            $config['security']['lockout_duration'] = (int)$_POST['lockout_duration'];
        }

        // Write configuration to custom.php file
        $configFile = __DIR__ . '/../../config/custom.php';
        $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($configFile, $configContent)) {
            logAudit('update_system_config', 'system', 0, ['sections' => array_keys($config)]);
            flash('success', 'System configuration updated successfully. Some changes may require a server restart.');
        } else {
            flash('error', 'Failed to save configuration file. Please check file permissions.');
        }

        redirect('/admin/system-config');
    }

    public function testDatabaseConnection() {
        header('Content-Type: application/json');

        // Get database credentials from POST
        $host = $_POST['host'] ?? config('database.host');
        $port = $_POST['port'] ?? config('database.port', 3306);
        $database = $_POST['database'] ?? config('database.name');
        $username = $_POST['username'] ?? config('database.username');
        $password = $_POST['password'] ?? config('database.password');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_TIMEOUT => 5
            ]);

            // Test query
            $pdo->query("SELECT 1");

            echo json_encode([
                'success' => true,
                'message' => 'Database connection successful!'
            ]);
        } catch (\PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function testEmailConnection() {
        header('Content-Type: application/json');

        // Get email from POST or use admin email
        $testEmail = $_POST['test_email'] ?? authUser()['email'];

        if (empty($testEmail)) {
            echo json_encode([
                'success' => false,
                'message' => 'No email address provided'
            ]);
            exit;
        }

        // Send test email
        $subject = 'Elite Car Hire - Email Configuration Test';
        $body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #C5A253;">✓ Email Test Successful</h2>
            <p>This is a test email from Elite Car Hire system configuration.</p>
            <p>If you received this email, your SMTP configuration is working correctly.</p>
            <p style="margin-top: 30px;">
                <strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '<br>
                <strong>Server:</strong> ' . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . '
            </p>
        </div>
        ';

        try {
            $result = sendEmail($testEmail, $subject, $body);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => "Test email sent successfully to {$testEmail}"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send test email. Check email configuration and logs.'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
