<?php
namespace controllers;

class HomeController {
    public function index() {
        // Auto-logout authenticated users when accessing public pages
        if (auth()) {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                logAudit('auto_logout_public_page', 'users', $userId, ['page' => 'home']);
            }

            // Clear all session variables
            $_SESSION = [];

            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            // Destroy the session
            session_destroy();

            // Restart session for the public page
            session_start();
        }

        $featuredVehicles = db()->fetchAll("SELECT v.*,
                                            (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) as primary_image
                                            FROM vehicles v
                                            WHERE v.status = 'approved'
                                            ORDER BY v.created_at DESC LIMIT 6");
        view('public/home', compact('featuredVehicles'));
    }
}
