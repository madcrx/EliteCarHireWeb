<?php
namespace controllers;

class CalendarController {
    
    public function getEvents() {
        requireAuth();
        
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        $sql = "SELECT * FROM calendar_events WHERE user_id = ? ORDER BY start_datetime ASC";
        $events = db()->fetchAll($sql, [$userId]);
        
        // Format for calendar
        $formatted = [];
        foreach ($events as $event) {
            $formatted[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'description' => $event['description'],
                'location' => $event['location'],
                'color' => $event['color'],
                'type' => $event['event_type']
            ];
        }
        
        json(['success' => true, 'events' => $formatted]);
    }
}
