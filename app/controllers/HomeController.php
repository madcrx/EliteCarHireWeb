<?php
namespace controllers;

class HomeController {
    public function index() {
        $featuredVehicles = db()->fetchAll("SELECT v.*, 
                                            (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) as primary_image
                                            FROM vehicles v 
                                            WHERE v.status = 'approved' 
                                            ORDER BY v.created_at DESC LIMIT 6");
        view('public/home', compact('featuredVehicles'));
    }
}
