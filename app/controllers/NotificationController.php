<?php
namespace controllers;

class NotificationController {
    
    public function markAsRead() {
        requireAuth();
        
        $notificationId = $_POST['id'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        // Verify notification belongs to user
        $notification = db()->fetch("SELECT * FROM notifications WHERE id = ? AND user_id = ?", 
                                     [$notificationId, $userId]);
        
        if ($notification) {
            db()->execute("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?", 
                         [$notificationId]);
            json(['success' => true]);
        } else {
            json(['success' => false, 'message' => 'Notification not found'], 404);
        }
    }
    
    public function markAllAsRead() {
        requireAuth();
        
        $userId = $_SESSION['user_id'];
        db()->execute("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0", 
                     [$userId]);
        
        json(['success' => true]);
    }
}
