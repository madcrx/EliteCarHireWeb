<?php
namespace controllers;

class PublicController {
    public function vehicles() {
        // Get filter parameters
        $state = $_GET['state'] ?? '';
        $category = $_GET['category'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        // Build base query
        $sql = "SELECT DISTINCT v.*,
                (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM vehicles v
                WHERE v.status = 'approved'";

        $params = [];

        // Filter by state
        if (!empty($state)) {
            $sql .= " AND v.state = ?";
            $params[] = $state;
        }

        // Filter by category
        if (!empty($category)) {
            $sql .= " AND v.category = ?";
            $params[] = $category;
        }

        // Filter by date availability
        if (!empty($startDate) && !empty($endDate)) {
            // Exclude vehicles that are booked during the requested period
            $sql .= " AND v.id NOT IN (
                        SELECT vehicle_id FROM bookings
                        WHERE status IN ('confirmed', 'in_progress')
                        AND ((booking_date <= ? AND DATE_ADD(booking_date, INTERVAL duration_hours HOUR) >= ?)
                             OR (booking_date <= ? AND DATE_ADD(booking_date, INTERVAL duration_hours HOUR) >= ?)
                             OR (booking_date >= ? AND DATE_ADD(booking_date, INTERVAL duration_hours HOUR) <= ?))
                    )";
            $params[] = $endDate;
            $params[] = $startDate;
            $params[] = $startDate;
            $params[] = $startDate;
            $params[] = $startDate;
            $params[] = $endDate;

            // Exclude vehicles with blocked dates during the requested period
            $sql .= " AND v.id NOT IN (
                        SELECT vehicle_id FROM vehicle_blocked_dates
                        WHERE ((start_date <= ? AND end_date >= ?)
                               OR (start_date <= ? AND end_date >= ?)
                               OR (start_date >= ? AND end_date <= ?))
                    )";
            $params[] = $endDate;
            $params[] = $startDate;
            $params[] = $endDate;
            $params[] = $endDate;
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " ORDER BY v.created_at DESC";

        $vehicles = db()->fetchAll($sql, $params);

        // Get unique states for filter dropdown
        $states = db()->fetchAll("SELECT DISTINCT state FROM vehicles WHERE status = 'approved' AND state IS NOT NULL AND state != '' ORDER BY state");

        // Get categories for filter dropdown
        $categories = db()->fetchAll("SELECT DISTINCT category FROM vehicles WHERE status = 'approved' ORDER BY category");

        // Pass filters to view for maintaining selected values
        $filters = [
            'state' => $state,
            'category' => $category,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        view('public/vehicles', compact('vehicles', 'states', 'categories', 'filters'));
    }
    
    public function viewVehicle($id) {
        $vehicle = db()->fetch("SELECT v.*, u.first_name, u.last_name FROM vehicles v
                                JOIN users u ON v.owner_id = u.id
                                WHERE v.id = ? AND v.status = 'approved'", [$id]);
        
        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/vehicles');
        }
        
        $images = db()->fetchAll("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY display_order", [$id]);
        $reviews = db()->fetchAll("SELECT r.*, u.first_name, u.last_name FROM reviews r 
                                   JOIN users u ON r.customer_id = u.id 
                                   WHERE r.vehicle_id = ? AND r.status = 'approved' 
                                   ORDER BY r.created_at DESC", [$id]);
        
        view('public/vehicle-detail', compact('vehicle', 'images', 'reviews'));
    }
    
    public function terms() {
        $page = db()->fetch("SELECT * FROM cms_pages WHERE page_key = 'terms'");
        view('public/page', ['page' => $page, 'title' => 'Terms of Service']);
    }
    
    public function privacy() {
        $page = db()->fetch("SELECT * FROM cms_pages WHERE page_key = 'privacy'");
        view('public/page', ['page' => $page, 'title' => 'Privacy Policy']);
    }
    
    public function faq() {
        $page = db()->fetch("SELECT * FROM cms_pages WHERE page_key = 'faq'");
        view('public/page', ['page' => $page, 'title' => 'FAQ']);
    }
    
    public function contact() {
        view('public/contact');
    }
    
    public function submitContact() {
        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/contact');
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';

        $submissionId = db()->execute("INSERT INTO contact_submissions (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)",
                     [$name, $email, $phone, $subject, $message]);

        // Send notification to admin/inquiries team
        $this->sendContactNotificationToAdmin($name, $email, $phone, $subject, $message, $submissionId);

        // Send auto-reply to customer
        $this->sendContactAutoReply($name, $email, $subject);

        flash('success', 'Thank you for contacting us. We will respond soon.');
        redirect('/contact');
    }

    private function sendContactNotificationToAdmin($name, $email, $phone, $subject, $message, $submissionId) {
        $replyUrl = generateLoginUrl("/admin/contact-submissions");
        $replyButton = getEmailButton($replyUrl, 'View & Reply', 'primary');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #C5A253;'>📩 New Contact Form Submission</h2>
            <p>A new inquiry has been submitted through the contact form.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Contact Details</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>
                <p><strong>Phone:</strong> " . ($phone ?: 'Not provided') . "</p>
                <p><strong>Subject:</strong> {$subject}</p>
            </div>

            <div style='background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Message:</h3>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($message) . "</p>
            </div>

            {$replyButton}

            <p style='margin-top: 30px;'>- Elite Car Hire System</p>
        </div>
        ";

        $inquiriesEmail = config('email.contact_inquiries', 'inquiries@elitecarhire.au');
        sendEmail($inquiriesEmail, "New Contact Inquiry: {$subject}", $body);
    }

    private function sendContactAutoReply($name, $email, $subject) {
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #C5A253;'>Thank You for Contacting Us</h2>
            <p>Dear {$name},</p>
            <p>We have received your message and will get back to you as soon as possible.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Your Inquiry</h3>
                <p><strong>Subject:</strong> {$subject}</p>
                <p><strong>Status:</strong> <span style='color: #f39c12;'>Received - Pending Response</span></p>
            </div>

            <p>Our team typically responds within 24 hours during business days (Monday - Friday, 9 AM - 5 PM AEST).</p>


            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($email, "We've Received Your Message - Elite Car Hire", $body);
    }

    public function about() {
        $page = db()->fetch("SELECT * FROM cms_pages WHERE page_key = 'about'");
        view('public/page', ['page' => $page, 'title' => 'About Us']);
    }

    public function services() {
        $page = db()->fetch("SELECT * FROM cms_pages WHERE page_key = 'services'");
        view('public/page', ['page' => $page, 'title' => 'Our Services']);
    }

    public function support() {
        $page = db()->fetch("SELECT * FROM cms_pages WHERE page_key = 'support'");
        view('public/page', ['page' => $page, 'title' => 'Support']);
    }
}
