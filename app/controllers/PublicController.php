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

        db()->execute("INSERT INTO contact_submissions (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)",
                     [$name, $email, $phone, $subject, $message]);

        flash('success', 'Thank you for contacting us. We will respond soon.');
        redirect('/contact');
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
