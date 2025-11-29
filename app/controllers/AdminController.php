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
        $vehicle = db()->fetch("SELECT owner_id FROM vehicles WHERE id = ?", [$id]);
        db()->execute("UPDATE vehicles SET status = 'approved' WHERE id = ?", [$id]);
        logAudit('approve_vehicle', 'vehicles', $id);
        createNotification($vehicle['owner_id'], 'approval', 'Vehicle Approved', 'Your vehicle listing has been approved!');
        flash('success', 'Vehicle approved');
        redirect('/admin/vehicles');
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
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
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

        $logoTitle = $_POST['logo_title'] ?? 'Company Logo';

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

        // Generate unique filename
        $extension = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
        $filename = 'logo-' . time() . '-' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $webPath = '/storage/uploads/logo/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadPath)) {
            flash('error', 'Failed to upload file');
            redirect('/admin/settings');
        }

        // Insert into site_images table
        $imageKey = 'logo-' . time();
        db()->execute("INSERT INTO site_images (image_key, title, image_path, image_type, uploaded_by)
                       VALUES (?, ?, ?, 'logo', ?)",
                     [$imageKey, $logoTitle, $webPath, $_SESSION['user_id']]);

        $logoId = db()->lastInsertId();

        // If this is the first logo, set it as active
        $logoCount = db()->fetch("SELECT COUNT(*) as count FROM site_images WHERE image_type = 'logo'");
        if ($logoCount['count'] == 1) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = 'active_logo_id'");
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_logo_id'", [$logoId]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value) VALUES ('active_logo_id', ?)", [$logoId]);
            }
        }

        logAudit('upload_company_logo', 'site_images', $logoId, ['title' => $logoTitle, 'path' => $webPath]);

        flash('success', 'Company logo uploaded successfully');
        redirect('/admin/settings');
    }

    public function setActiveLogo() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/settings');
        }

        $logoId = $_POST['logo_id'] ?? 0;

        // Verify logo exists
        $logo = db()->fetch("SELECT id FROM site_images WHERE id = ? AND image_type = 'logo'", [$logoId]);
        if (!$logo) {
            flash('error', 'Logo not found');
            redirect('/admin/settings');
        }

        // Update active logo setting
        $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = 'active_logo_id'");
        if ($existing) {
            db()->execute("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_logo_id'", [$logoId]);
        } else {
            db()->execute("INSERT INTO settings (setting_key, setting_value) VALUES ('active_logo_id', ?)", [$logoId]);
        }

        logAudit('set_active_logo', 'settings', null, ['logo_id' => $logoId]);

        flash('success', 'Active logo updated successfully');
        redirect('/admin/settings');
    }

    public function deleteLogo() {
        requireAuth('admin');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/admin/settings');
        }

        $logoId = $_POST['logo_id'] ?? 0;

        // Get logo
        $logo = db()->fetch("SELECT * FROM site_images WHERE id = ? AND image_type = 'logo'", [$logoId]);
        if (!$logo) {
            flash('error', 'Logo not found');
            redirect('/admin/settings');
        }

        // Check if this is the active logo
        $activeLogo = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'active_logo_id'");
        $isActive = ($activeLogo && $activeLogo['setting_value'] == $logoId);

        // Delete file
        $filePath = __DIR__ . '/../../..' . $logo['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Delete from database
        db()->execute("DELETE FROM site_images WHERE id = ?", [$logoId]);

        // If this was the active logo, set another one as active or clear the setting
        if ($isActive) {
            $newLogo = db()->fetch("SELECT id FROM site_images WHERE image_type = 'logo' ORDER BY created_at DESC LIMIT 1");
            if ($newLogo) {
                db()->execute("UPDATE settings SET setting_value = ? WHERE setting_key = 'active_logo_id'", [$newLogo['id']]);
            } else {
                db()->execute("DELETE FROM settings WHERE setting_key = 'active_logo_id'");
            }
        }

        logAudit('delete_company_logo', 'site_images', $logoId, ['image_path' => $logo['image_path']]);

        flash('success', 'Logo deleted successfully');
        redirect('/admin/settings');
    }

    public function removeLogo() {
        // Kept for backward compatibility - redirects to deleteLogo
        $this->deleteLogo();
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
            }
            
            db()->execute("UPDATE pending_changes SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?", 
                         [$_SESSION['user_id'], $id]);
            
            createNotification($change['owner_id'], 'approval', 'Change Approved', 'Your submitted change has been approved.');
            logAudit('approve_pending_change', 'pending_changes', $id);
            flash('success', 'Change approved successfully');
        }
        
        redirect('/admin/pending-changes');
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

    public function clearCache() {
        requireAuth('admin');

        $cleared = [];

        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $cleared[] = 'OPcache cleared';
        }

        // Clear file stat cache
        clearstatcache(true);
        $cleared[] = 'File stat cache cleared';

        // Clear realpath cache
        if (function_exists('clearstatcache')) {
            clearstatcache(true);
            $cleared[] = 'Realpath cache cleared';
        }

        logAudit('clear_cache', 'system', null, ['caches' => $cleared]);

        flash('success', 'System cache cleared successfully: ' . implode(', ', $cleared));
        redirect('/admin/dashboard');
    }

    // Stub pages for features under development
    public function emailSettings() {
        view('admin/email-settings');
    }

    public function emailQueue() {
        view('admin/email-queue');
    }

    public function revenueReports() {
        view('admin/reports/revenue');
    }

    public function bookingAnalytics() {
        view('admin/reports/bookings');
    }

    public function vehiclePerformance() {
        view('admin/reports/vehicles');
    }

    public function userStatistics() {
        view('admin/reports/users');
    }

    public function paymentSettings() {
        // Load payment-related settings
        $stripeTestKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_test_secret_key'")['setting_value'] ?? '';
        $stripeLiveKey = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_live_secret_key'")['setting_value'] ?? '';
        $stripeTestPublishable = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_test_publishable_key'")['setting_value'] ?? '';
        $stripeLivePublishable = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_live_publishable_key'")['setting_value'] ?? '';
        $stripeWebhookSecret = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_webhook_secret'")['setting_value'] ?? '';
        $stripeMode = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'stripe_mode'")['setting_value'] ?? 'test';
        $paymentCurrency = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'payment_currency'")['setting_value'] ?? 'AUD';

        view('admin/settings/payment', compact(
            'stripeTestKey', 'stripeLiveKey', 'stripeTestPublishable',
            'stripeLivePublishable', 'stripeWebhookSecret', 'stripeMode', 'paymentCurrency'
        ));
    }

    public function savePaymentSettings() {
        requireAuth('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/admin/settings/payment');
        }

        $settings = [
            'stripe_test_secret_key' => $_POST['stripe_test_secret_key'] ?? '',
            'stripe_live_secret_key' => $_POST['stripe_live_secret_key'] ?? '',
            'stripe_test_publishable_key' => $_POST['stripe_test_publishable_key'] ?? '',
            'stripe_live_publishable_key' => $_POST['stripe_live_publishable_key'] ?? '',
            'stripe_webhook_secret' => $_POST['stripe_webhook_secret'] ?? '',
            'stripe_mode' => $_POST['stripe_mode'] ?? 'test',
            'payment_currency' => $_POST['payment_currency'] ?? 'AUD'
        ];

        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$key, $value]);
            }
            logAudit('update_setting', 'settings', null, ['setting_key' => $key]);
        }

        flash('success', 'Payment settings saved successfully');
        redirect('/admin/settings/payment');
    }

    public function emailConfiguration() {
        // Load email settings
        $smtpHost = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'smtp_host'")['setting_value'] ?? '';
        $smtpPort = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'smtp_port'")['setting_value'] ?? '587';
        $smtpUsername = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'smtp_username'")['setting_value'] ?? '';
        $smtpPassword = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'smtp_password'")['setting_value'] ?? '';
        $smtpEncryption = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'smtp_encryption'")['setting_value'] ?? 'tls';
        $emailFrom = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'email_from_address'")['setting_value'] ?? '';
        $emailFromName = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'email_from_name'")['setting_value'] ?? '';

        view('admin/settings/email', compact(
            'smtpHost', 'smtpPort', 'smtpUsername', 'smtpPassword',
            'smtpEncryption', 'emailFrom', 'emailFromName'
        ));
    }

    public function saveEmailConfiguration() {
        requireAuth('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/admin/settings/email');
        }

        $settings = [
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '587',
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
            'email_from_address' => $_POST['email_from_address'] ?? '',
            'email_from_name' => $_POST['email_from_name'] ?? ''
        ];

        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$key, $value]);
            }
            logAudit('update_setting', 'settings', null, ['setting_key' => $key]);
        }

        flash('success', 'Email configuration saved successfully');
        redirect('/admin/settings/email');
    }

    public function commissionRates() {
        // Load commission rate settings
        $defaultCommissionRate = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'default_commission_rate'")['setting_value'] ?? '15';
        $premiumCommissionRate = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'premium_commission_rate'")['setting_value'] ?? '12';
        $standardCommissionRate = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'standard_commission_rate'")['setting_value'] ?? '15';
        $economyCommissionRate = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'economy_commission_rate'")['setting_value'] ?? '18';
        $minCommissionAmount = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'min_commission_amount'")['setting_value'] ?? '50';
        $commissionPaymentCycle = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'commission_payment_cycle'")['setting_value'] ?? 'monthly';

        view('admin/settings/commission', compact(
            'defaultCommissionRate', 'premiumCommissionRate', 'standardCommissionRate',
            'economyCommissionRate', 'minCommissionAmount', 'commissionPaymentCycle'
        ));
    }

    public function saveCommissionRates() {
        requireAuth('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/admin/settings/commission');
        }

        $settings = [
            'default_commission_rate' => $_POST['default_commission_rate'] ?? '15',
            'premium_commission_rate' => $_POST['premium_commission_rate'] ?? '12',
            'standard_commission_rate' => $_POST['standard_commission_rate'] ?? '15',
            'economy_commission_rate' => $_POST['economy_commission_rate'] ?? '18',
            'min_commission_amount' => $_POST['min_commission_amount'] ?? '50',
            'commission_payment_cycle' => $_POST['commission_payment_cycle'] ?? 'monthly'
        ];

        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$key, $value]);
            }
            logAudit('update_setting', 'settings', null, ['setting_key' => $key, 'setting_value' => $value]);
        }

        flash('success', 'Commission rates saved successfully');
        redirect('/admin/settings/commission');
    }

    public function bookingSettings() {
        // Load booking-related settings
        $minBookingHours = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'min_booking_hours'")['setting_value'] ?? '4';
        $maxBookingDays = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'max_booking_days'")['setting_value'] ?? '30';
        $advanceBookingDays = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'advance_booking_days'")['setting_value'] ?? '90';
        $cancellationHours = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'cancellation_hours'")['setting_value'] ?? '24';
        $autoConfirmBookings = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'auto_confirm_bookings'")['setting_value'] ?? '0';
        $requireDeposit = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'require_deposit'")['setting_value'] ?? '1';
        $depositPercentage = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'deposit_percentage'")['setting_value'] ?? '30';

        view('admin/settings/booking', compact(
            'minBookingHours', 'maxBookingDays', 'advanceBookingDays',
            'cancellationHours', 'autoConfirmBookings', 'requireDeposit', 'depositPercentage'
        ));
    }

    public function saveBookingSettings() {
        requireAuth('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/admin/settings/booking');
        }

        $settings = [
            'min_booking_hours' => $_POST['min_booking_hours'] ?? '4',
            'max_booking_days' => $_POST['max_booking_days'] ?? '30',
            'advance_booking_days' => $_POST['advance_booking_days'] ?? '90',
            'cancellation_hours' => $_POST['cancellation_hours'] ?? '24',
            'auto_confirm_bookings' => isset($_POST['auto_confirm_bookings']) ? '1' : '0',
            'require_deposit' => isset($_POST['require_deposit']) ? '1' : '0',
            'deposit_percentage' => $_POST['deposit_percentage'] ?? '30'
        ];

        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$key, $value]);
            }
            logAudit('update_setting', 'settings', null, ['setting_key' => $key, 'setting_value' => $value]);
        }

        flash('success', 'Booking settings saved successfully');
        redirect('/admin/settings/booking');
    }

    public function notificationSettings() {
        // Load notification settings
        $emailNotifications = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'email_notifications_enabled'")['setting_value'] ?? '1';
        $smsNotifications = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'sms_notifications_enabled'")['setting_value'] ?? '0';
        $notifyNewBooking = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'notify_new_booking'")['setting_value'] ?? '1';
        $notifyBookingConfirm = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'notify_booking_confirm'")['setting_value'] ?? '1';
        $notifyBookingCancel = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'notify_booking_cancel'")['setting_value'] ?? '1';
        $notifyPaymentReceived = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'notify_payment_received'")['setting_value'] ?? '1';
        $notifyNewVehicle = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'notify_new_vehicle'")['setting_value'] ?? '1';
        $adminNotificationEmail = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'admin_notification_email'")['setting_value'] ?? '';

        view('admin/settings/notifications', compact(
            'emailNotifications', 'smsNotifications', 'notifyNewBooking',
            'notifyBookingConfirm', 'notifyBookingCancel', 'notifyPaymentReceived',
            'notifyNewVehicle', 'adminNotificationEmail'
        ));
    }

    public function saveNotificationSettings() {
        requireAuth('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/admin/settings/notifications');
        }

        $settings = [
            'email_notifications_enabled' => isset($_POST['email_notifications_enabled']) ? '1' : '0',
            'sms_notifications_enabled' => isset($_POST['sms_notifications_enabled']) ? '1' : '0',
            'notify_new_booking' => isset($_POST['notify_new_booking']) ? '1' : '0',
            'notify_booking_confirm' => isset($_POST['notify_booking_confirm']) ? '1' : '0',
            'notify_booking_cancel' => isset($_POST['notify_booking_cancel']) ? '1' : '0',
            'notify_payment_received' => isset($_POST['notify_payment_received']) ? '1' : '0',
            'notify_new_vehicle' => isset($_POST['notify_new_vehicle']) ? '1' : '0',
            'admin_notification_email' => $_POST['admin_notification_email'] ?? ''
        ];

        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$key, $value]);
            }
            logAudit('update_setting', 'settings', null, ['setting_key' => $key, 'setting_value' => $value]);
        }

        flash('success', 'Notification settings saved successfully');
        redirect('/admin/settings/notifications');
    }

    public function systemConfiguration() {
        // Load system configuration settings
        $siteName = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'site_name'")['setting_value'] ?? 'Elite Car Hire';
        $siteUrl = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'site_url'")['setting_value'] ?? '';
        $maintenanceMode = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'")['setting_value'] ?? '0';
        $debugMode = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'debug_mode'")['setting_value'] ?? '0';
        $timezone = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'timezone'")['setting_value'] ?? 'Australia/Sydney';
        $sessionTimeout = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'session_timeout'")['setting_value'] ?? '3600';

        // Load database info (read-only, from config)
        $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
        $dbName = $_ENV['DB_NAME'] ?? '';
        $dbUser = $_ENV['DB_USER'] ?? '';

        view('admin/settings/system', compact(
            'siteName', 'siteUrl', 'maintenanceMode', 'debugMode',
            'timezone', 'sessionTimeout', 'dbHost', 'dbName', 'dbUser'
        ));
    }

    public function saveSystemConfiguration() {
        requireAuth('admin');

        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token.');
            redirect('/admin/settings/system');
        }

        $settings = [
            'site_name' => $_POST['site_name'] ?? 'Elite Car Hire',
            'site_url' => $_POST['site_url'] ?? '',
            'maintenance_mode' => $_POST['maintenance_mode'] ?? '0',
            'debug_mode' => $_POST['debug_mode'] ?? '0',
            'timezone' => $_POST['timezone'] ?? 'Australia/Sydney',
            'session_timeout' => $_POST['session_timeout'] ?? '3600'
        ];

        foreach ($settings as $key => $value) {
            $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                db()->execute("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
            } else {
                db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$key, $value]);
            }
            logAudit('update_setting', 'settings', null, ['setting_key' => $key]);
        }

        flash('success', 'System configuration saved successfully');
        redirect('/admin/settings/system');
    }

    public function paymentLogs() {
        view('admin/logs/payments');
    }

    public function emailLogs() {
        view('admin/logs/emails');
    }

    public function loginHistory() {
        view('admin/logs/login');
    }
}
