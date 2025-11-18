<?php
namespace controllers;

class AnalyticsController {
    
    public function getData() {
        requireAuth();
        
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        $type = $_GET['type'] ?? 'revenue';
        
        $data = [];
        
        switch ($type) {
            case 'revenue':
                if ($role === 'admin') {
                    $data = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                                            SUM(total_amount) as revenue 
                                            FROM bookings WHERE status='completed' 
                                            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                            ORDER BY month DESC LIMIT 12");
                } elseif ($role === 'owner') {
                    $data = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                                            SUM(total_amount - commission_amount) as revenue 
                                            FROM bookings WHERE owner_id = ? AND status='completed' 
                                            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                            ORDER BY month DESC LIMIT 12", [$userId]);
                }
                break;
                
            case 'bookings':
                if ($role === 'admin') {
                    $data = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                                            COUNT(*) as count 
                                            FROM bookings 
                                            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                            ORDER BY month DESC LIMIT 12");
                } elseif ($role === 'owner') {
                    $data = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                                            COUNT(*) as count 
                                            FROM bookings WHERE owner_id = ? 
                                            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                            ORDER BY month DESC LIMIT 12", [$userId]);
                }
                break;
        }
        
        json(['success' => true, 'data' => $data]);
    }
}
