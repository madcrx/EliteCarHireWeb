<?php
/**
 * Notifications Helper Functions
 * Creates and manages user notifications
 */

/**
 * Create a notification for a user
 */
function createNotification($userId, $type, $title, $message, $link = null) {
    try {
        db()->execute(
            "INSERT INTO notifications (user_id, type, title, message, link, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $type, $title, $message, $link]
        );
        return true;
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notifications for a user
 */
function getUnreadNotifications($userId, $limit = 10) {
    return db()->fetchAll(
        "SELECT * FROM notifications
         WHERE user_id = ? AND is_read = FALSE
         ORDER BY created_at DESC
         LIMIT ?",
        [$userId, $limit]
    );
}

/**
 * Get all notifications for a user
 */
function getAllNotifications($userId, $limit = 50) {
    return db()->fetchAll(
        "SELECT * FROM notifications
         WHERE user_id = ?
         ORDER BY created_at DESC
         LIMIT ?",
        [$userId, $limit]
    );
}

/**
 * Mark notification as read
 */
function markNotificationRead($notificationId, $userId) {
    db()->execute(
        "UPDATE notifications
         SET is_read = TRUE, read_at = NOW()
         WHERE id = ? AND user_id = ?",
        [$notificationId, $userId]
    );
}

/**
 * Mark all notifications as read for a user
 */
function markAllNotificationsRead($userId) {
    db()->execute(
        "UPDATE notifications
         SET is_read = TRUE, read_at = NOW()
         WHERE user_id = ? AND is_read = FALSE",
        [$userId]
    );
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId) {
    $result = db()->fetch(
        "SELECT COUNT(*) as count FROM notifications
         WHERE user_id = ? AND is_read = FALSE",
        [$userId]
    );
    return $result['count'] ?? 0;
}

/**
 * Delete old notifications (older than 30 days)
 */
function deleteOldNotifications() {
    db()->execute(
        "DELETE FROM notifications
         WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
}

// Notification type helpers

function notifyBookingConfirmed($customerId, $bookingRef, $vehicleName) {
    createNotification(
        $customerId,
        'booking_confirmed',
        'Booking Confirmed',
        "Your booking {$bookingRef} for {$vehicleName} has been confirmed by the owner.",
        "/customer/bookings?ref={$bookingRef}"
    );
}

function notifyBookingInProgress($customerId, $ownerId, $bookingRef, $vehicleName) {
    // Notify customer
    createNotification(
        $customerId,
        'booking_started',
        'Booking Started',
        "Your booking {$bookingRef} for {$vehicleName} is now in progress. Enjoy your ride!",
        "/customer/bookings?ref={$bookingRef}"
    );

    // Notify owner
    createNotification(
        $ownerId,
        'booking_started',
        'Booking Started',
        "Booking {$bookingRef} for your {$vehicleName} is now in progress.",
        "/owner/bookings"
    );
}

function notifyBookingCompleted($customerId, $ownerId, $bookingRef, $vehicleName) {
    // Notify customer
    createNotification(
        $customerId,
        'booking_completed',
        'Booking Completed',
        "Your booking {$bookingRef} for {$vehicleName} has been completed. Please leave a review!",
        "/customer/bookings?ref={$bookingRef}"
    );

    // Notify owner
    createNotification(
        $ownerId,
        'booking_completed',
        'Booking Completed',
        "Booking {$bookingRef} for your {$vehicleName} has been completed successfully.",
        "/owner/bookings"
    );
}

function notifyBookingCancellationPending($adminId, $bookingRef, $vehicleName, $reason) {
    createNotification(
        $adminId,
        'cancellation_pending',
        'Cancellation Approval Needed',
        "Owner has requested to cancel booking {$bookingRef} for {$vehicleName}. Reason: {$reason}",
        "/admin/bookings?ref={$bookingRef}"
    );
}

function notifyBookingCancelled($customerId, $bookingRef, $vehicleName, $reason) {
    createNotification(
        $customerId,
        'booking_cancelled',
        'Booking Cancelled',
        "Your booking {$bookingRef} for {$vehicleName} has been cancelled. Reason: {$reason}",
        "/customer/bookings?ref={$bookingRef}"
    );
}

function notifyPaymentReceived($ownerId, $bookingRef, $amount) {
    createNotification(
        $ownerId,
        'payment_received',
        'Payment Received',
        "Payment of $" . number_format($amount, 2) . " received for booking {$bookingRef}.",
        "/owner/payouts"
    );
}

function notifyNewBooking($ownerId, $bookingRef, $vehicleName, $customerName) {
    createNotification(
        $ownerId,
        'new_booking',
        'New Booking Request',
        "New booking request {$bookingRef} from {$customerName} for your {$vehicleName}.",
        "/owner/bookings"
    );
}
