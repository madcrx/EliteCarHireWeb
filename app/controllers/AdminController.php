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
    
    public function bookings() {
        $bookings = db()->fetchAll("SELECT b.*, u.first_name as customer_name, u.last_name as customer_last,
                                     v.make, v.model, o.first_name as owner_name, o.last_name as owner_last
                                     FROM bookings b
                                     JOIN users u ON b.customer_id = u.id
                                     JOIN vehicles v ON b.vehicle_id = v.id
                                     JOIN users o ON b.owner_id = o.id
                                     ORDER BY b.created_at DESC");
        view('admin/bookings', compact('bookings'));
    }
    
    public function payments() {
        $payments = db()->fetchAll("SELECT p.*, b.booking_reference FROM payments p 
                                     JOIN bookings b ON p.booking_id = b.id 
                                     ORDER BY p.created_at DESC");
        view('admin/payments', compact('payments'));
    }
    
    public function payouts() {
        $payouts = db()->fetchAll("SELECT p.*, u.first_name, u.last_name, b.booking_reference 
                                    FROM payouts p 
                                    JOIN users u ON p.owner_id = u.id 
                                    LEFT JOIN bookings b ON p.booking_id = b.id 
                                    ORDER BY p.created_at DESC");
        view('admin/payouts', compact('payouts'));
    }
    
    public function disputes() {
        $disputes = db()->fetchAll("SELECT d.*, b.booking_reference, u.first_name, u.last_name 
                                     FROM disputes d 
                                     JOIN bookings b ON d.booking_id = b.id 
                                     JOIN users u ON d.raised_by = u.id 
                                     ORDER BY d.created_at DESC");
        view('admin/disputes', compact('disputes'));
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
            db()->execute("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES ('company_logo', ?, NOW(), NOW())", [$webPath]);
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
        $submissions = db()->fetchAll("SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT 100");
        view('admin/contact-submissions', compact('submissions'));
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

        db()->execute("UPDATE contact_submissions SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);

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
}
