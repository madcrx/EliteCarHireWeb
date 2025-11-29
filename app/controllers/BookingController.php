<?php
namespace controllers;

// Include email notification functions
require_once __DIR__ . '/../helpers/email_sender.php';
require_once __DIR__ . '/../helpers/booking_emails.php';

class BookingController {
    public function create() {
        requireAuth();

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrf($token)) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/vehicles');
        }

        $vehicleId = $_POST['vehicle_id'] ?? 0;
        $bookingDate = $_POST['booking_date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $duration = $_POST['duration'] ?? 4;
        $pickupLocation = $_POST['pickup_location'] ?? '';
        $destination1 = $_POST['destination_1'] ?? null;
        $destination2 = $_POST['destination_2'] ?? null;
        $destination3 = $_POST['destination_3'] ?? null;
        $dropoffLocation = $_POST['drop_off_location'] ?? null;
        $eventType = $_POST['event_type'] ?? 'other';
        $specialRequirements = $_POST['special_requirements'] ?? '';

        $vehicle = db()->fetch("SELECT * FROM vehicles WHERE id = ?", [$vehicleId]);

        if (!$vehicle) {
            flash('error', 'Vehicle not found');
            redirect('/vehicles');
        }

        $baseAmount = $vehicle['hourly_rate'] * $duration;
        $commissionRate = 15.00;
        $commissionAmount = $baseAmount * ($commissionRate / 100);
        $totalAmount = $baseAmount;

        $endTime = date('H:i', strtotime($startTime) + ($duration * 3600));

        $bookingReference = generateBookingReference();

        $sql = "INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date,
                start_time, end_time, duration_hours, pickup_location, destination_1, destination_2, destination_3,
                dropoff_location, event_type, special_requirements,
                base_amount, total_amount, commission_rate, commission_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        db()->execute($sql, [
            $bookingReference,
            $_SESSION['user_id'],
            $vehicleId,
            $vehicle['owner_id'],
            $bookingDate,
            $startTime,
            $endTime,
            $duration,
            $pickupLocation,
            $destination1,
            $destination2,
            $destination3,
            $dropoffLocation,
            $eventType,
            $specialRequirements,
            $baseAmount,
            $totalAmount,
            $commissionRate,
            $commissionAmount
        ]);
        
        $bookingId = db()->lastInsertId();
        
        // Create calendar event
        db()->execute("INSERT INTO calendar_events (user_id, booking_id, title, start_datetime, end_datetime, event_type) 
                      VALUES (?, ?, ?, ?, ?, 'booking')", 
                     [$vehicle['owner_id'], $bookingId, "Booking: {$vehicle['make']} {$vehicle['model']}", 
                      "$bookingDate $startTime", "$bookingDate $endTime"]);
        
        createNotification($vehicle['owner_id'], 'new_booking', 'New Booking',
                          "You have a new booking for your {$vehicle['make']} {$vehicle['model']}");

        logAudit('create_booking', 'bookings', $bookingId);

        // Send email notifications
        emailCustomerBookingCreated($_SESSION['user_id'], $bookingId);
        emailOwnerNewBooking($bookingId);

        flash('success', 'Booking created successfully. Reference: ' . $bookingReference);
        redirect('/customer/bookings');
    }
}
