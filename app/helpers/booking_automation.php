<?php
/**
 * Booking Automation Helper Functions
 * Automatically updates booking statuses based on dates/times and payment status
 */

// Note: notifications.php should be loaded by the controller before this file
// to avoid potential circular dependency or redefinition issues

/**
 * Auto-update booking statuses based on current date/time
 * Should be called via cron job or on page loads
 */
function autoUpdateBookingStatuses() {
    $updated = 0;

    // Update confirmed bookings to "in_progress" if payment is made and booking time has started
    $confirmedBookings = db()->fetchAll(
        "SELECT b.*, v.make, v.model, u.first_name as customer_first_name, u.last_name as customer_last_name
         FROM bookings b
         JOIN vehicles v ON b.vehicle_id = v.id
         JOIN users u ON b.customer_id = u.id
         WHERE b.status = 'confirmed'
           AND b.payment_status = 'paid'
           AND CONCAT(b.booking_date, ' ', b.start_time) <= NOW()"
    );

    foreach ($confirmedBookings as $booking) {
        db()->execute(
            "UPDATE bookings SET status = 'in_progress', updated_at = NOW()
             WHERE id = ?",
            [$booking['id']]
        );

        // Create notifications
        $vehicleName = $booking['make'] . ' ' . $booking['model'];
        notifyBookingInProgress(
            $booking['customer_id'],
            $booking['owner_id'],
            $booking['booking_reference'],
            $vehicleName
        );

        logAudit('booking_auto_started', 'bookings', $booking['id'], [
            'from_status' => 'confirmed',
            'to_status' => 'in_progress'
        ]);

        $updated++;
    }

    // Update in_progress bookings to "completed" if end time has passed
    $inProgressBookings = db()->fetchAll(
        "SELECT b.*, v.make, v.model
         FROM bookings b
         JOIN vehicles v ON b.vehicle_id = v.id
         WHERE b.status = 'in_progress'
           AND CONCAT(b.booking_date, ' ', b.end_time) <= NOW()"
    );

    foreach ($inProgressBookings as $booking) {
        db()->execute(
            "UPDATE bookings SET status = 'completed', updated_at = NOW()
             WHERE id = ?",
            [$booking['id']]
        );

        // Create notifications
        $vehicleName = $booking['make'] . ' ' . $booking['model'];
        notifyBookingCompleted(
            $booking['customer_id'],
            $booking['owner_id'],
            $booking['booking_reference'],
            $vehicleName
        );

        logAudit('booking_auto_completed', 'bookings', $booking['id'], [
            'from_status' => 'in_progress',
            'to_status' => 'completed'
        ]);

        $updated++;
    }

    return $updated;
}

/**
 * Check if a booking can transition to in_progress
 */
function canTransitionToInProgress($bookingId) {
    $booking = db()->fetch(
        "SELECT status, payment_status, CONCAT(booking_date, ' ', start_time) as start_datetime
         FROM bookings
         WHERE id = ?",
        [$bookingId]
    );

    if (!$booking) {
        return false;
    }

    return $booking['status'] === 'confirmed'
        && $booking['payment_status'] === 'paid'
        && strtotime($booking['start_datetime']) <= time();
}

/**
 * Check if a booking should be automatically completed
 */
function shouldAutoComplete($bookingId) {
    $booking = db()->fetch(
        "SELECT status, CONCAT(booking_date, ' ', end_time) as end_datetime
         FROM bookings
         WHERE id = ?",
        [$bookingId]
    );

    if (!$booking) {
        return false;
    }

    return $booking['status'] === 'in_progress'
        && strtotime($booking['end_datetime']) <= time();
}

/**
 * Manually transition booking to in_progress (if conditions met)
 */
function transitionBookingToInProgress($bookingId) {
    if (!canTransitionToInProgress($bookingId)) {
        return false;
    }

    $booking = db()->fetch(
        "SELECT b.*, v.make, v.model
         FROM bookings b
         JOIN vehicles v ON b.vehicle_id = v.id
         WHERE b.id = ?",
        [$bookingId]
    );

    db()->execute(
        "UPDATE bookings SET status = 'in_progress', updated_at = NOW()
         WHERE id = ?",
        [$bookingId]
    );

    $vehicleName = $booking['make'] . ' ' . $booking['model'];
    notifyBookingInProgress(
        $booking['customer_id'],
        $booking['owner_id'],
        $booking['booking_reference'],
        $vehicleName
    );

    return true;
}

/**
 * Manually complete a booking (if conditions met)
 */
function completeBooking($bookingId) {
    if (!shouldAutoComplete($bookingId)) {
        return false;
    }

    $booking = db()->fetch(
        "SELECT b.*, v.make, v.model
         FROM bookings b
         JOIN vehicles v ON b.vehicle_id = v.id
         WHERE b.id = ?",
        [$bookingId]
    );

    db()->execute(
        "UPDATE bookings SET status = 'completed', updated_at = NOW()
         WHERE id = ?",
        [$bookingId]
    );

    $vehicleName = $booking['make'] . ' ' . $booking['model'];
    notifyBookingCompleted(
        $booking['customer_id'],
        $booking['owner_id'],
        $booking['booking_reference'],
        $vehicleName
    );

    return true;
}
