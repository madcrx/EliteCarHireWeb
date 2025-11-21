<?php
namespace controllers;

class PublicController {
    public function vehicles() {
        $vehicles = db()->fetchAll("SELECT v.*, 
                                     (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) as primary_image
                                     FROM vehicles v 
                                     WHERE v.status = 'approved' 
                                     ORDER BY v.created_at DESC");
        view('public/vehicles', compact('vehicles'));
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
