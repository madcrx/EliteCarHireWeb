<?php
namespace controllers;

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
                start_time, end_time, duration_hours, pickup_location, event_type, special_requirements, 
                base_amount, total_amount, commission_rate, commission_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
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

        // Get customer and owner details
        $customer = db()->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $owner = db()->fetch("SELECT * FROM users WHERE id = ?", [$vehicle['owner_id']]);

        // Send email to customer - booking created, awaiting confirmation
        $this->sendCustomerBookingCreatedEmail($customer, $vehicle, $bookingReference, $bookingDate, $startTime, $endTime, $duration, $totalAmount, $bookingId);

        // Send email to owner - new booking request
        $this->sendOwnerNewBookingEmail($owner, $customer, $vehicle, $bookingReference, $bookingDate, $startTime, $endTime, $duration, $totalAmount, $bookingId);

        // Send email to admin - new booking notification
        $this->sendAdminNewBookingEmail($customer, $owner, $vehicle, $bookingReference, $bookingDate, $totalAmount, $bookingId);

        logAudit('create_booking', 'bookings', $bookingId);

        flash('success', 'Booking created successfully. Reference: ' . $bookingReference);
        redirect('/customer/bookings');
    }

    private function sendCustomerBookingCreatedEmail($customer, $vehicle, $reference, $date, $startTime, $endTime, $duration, $amount, $bookingId) {
        $vehicleName = "{$vehicle['year']} {$vehicle['make']} {$vehicle['model']}";
        $viewUrl = generateLoginUrl("/customer/bookings");
        $viewButton = getEmailButton($viewUrl, 'View My Bookings', 'primary');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #C5A253;'>Booking Created Successfully!</h2>
            <p>Dear {$customer['first_name']},</p>
            <p>Your booking request has been created. The vehicle owner will review and confirm your booking shortly.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$reference}</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Date:</strong> {$date}</p>
                <p><strong>Time:</strong> {$startTime} - {$endTime}</p>
                <p><strong>Duration:</strong> {$duration} hours</p>
                <p><strong>Total Amount:</strong> \$" . number_format($amount, 2) . " AUD</p>
                <p><strong>Status:</strong> <span style='color: #f39c12; font-weight: bold;'>PENDING CONFIRMATION</span></p>
            </div>

            <div style='background: #fff3cd; padding: 15px; border-left: 4px solid #f39c12; margin: 20px 0;'>
                <p style='margin: 0;'><strong>⏳ Next Steps:</strong> Once the owner confirms your booking, you'll receive a payment link to complete your reservation.</p>
            </div>

            {$viewButton}

            <p>If you have any questions, please contact us at support@elitecarhire.au</p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong><br>
            Melbourne, Australia</p>
        </div>
        ";

        sendEmail($customer['email'], "Booking Created - {$reference}", $body);
    }

    private function sendOwnerNewBookingEmail($owner, $customer, $vehicle, $reference, $date, $startTime, $endTime, $duration, $amount, $bookingId) {
        $vehicleName = "{$vehicle['year']} {$vehicle['make']} {$vehicle['model']}";
        $confirmUrl = generateActionUrl('confirm_booking', '/owner/bookings/confirm-action', $owner['id'], 'booking', $bookingId);
        $viewUrl = generateLoginUrl("/owner/bookings");

        $confirmButton = getEmailButton($confirmUrl, 'Confirm Booking', 'success');
        $viewButton = getEmailButton($viewUrl, 'View All Bookings', 'primary');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #C5A253;'>New Booking Request!</h2>
            <p>Hi {$owner['first_name']},</p>
            <p>You have received a new booking request for your <strong>{$vehicleName}</strong>.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Booking Reference:</strong> {$reference}</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Customer:</strong> {$customer['first_name']} {$customer['last_name']}</p>
                <p><strong>Date:</strong> {$date}</p>
                <p><strong>Time:</strong> {$startTime} - {$endTime}</p>
                <p><strong>Duration:</strong> {$duration} hours</p>
                <p><strong>Amount:</strong> \$" . number_format($amount, 2) . " AUD</p>
            </div>

            <p><strong>Please review and confirm this booking:</strong></p>

            {$confirmButton}
            {$viewButton}

            <p style='color: #666; font-size: 14px;'><em>Note: Once confirmed, the customer will receive a payment link. After payment, your payout will be processed automatically.</em></p>

            <p style='margin-top: 30px;'>Best regards,<br>
            <strong>Elite Car Hire Team</strong></p>
        </div>
        ";

        sendEmail($owner['email'], "New Booking Request - {$vehicleName}", $body);
    }

    private function sendAdminNewBookingEmail($customer, $owner, $vehicle, $reference, $date, $amount, $bookingId) {
        $vehicleName = "{$vehicle['year']} {$vehicle['make']} {$vehicle['model']}";
        $viewUrl = generateLoginUrl("/admin/bookings");
        $viewButton = getEmailButton($viewUrl, 'View in Admin Panel', 'primary');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #C5A253;'>New Booking Created</h2>
            <p>A new booking has been created in the system.</p>

            <div style='background: #f5f5f5; padding: 20px; border-left: 4px solid #C5A253; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Booking Summary</h3>
                <p><strong>Reference:</strong> {$reference}</p>
                <p><strong>Customer:</strong> {$customer['first_name']} {$customer['last_name']} ({$customer['email']})</p>
                <p><strong>Owner:</strong> {$owner['first_name']} {$owner['last_name']} ({$owner['email']})</p>
                <p><strong>Vehicle:</strong> {$vehicleName}</p>
                <p><strong>Date:</strong> {$date}</p>
                <p><strong>Amount:</strong> \$" . number_format($amount, 2) . " AUD</p>
                <p><strong>Status:</strong> <span style='color: #f39c12;'>Pending Confirmation</span></p>
            </div>

            {$viewButton}

            <p style='margin-top: 30px;'>- Elite Car Hire System</p>
        </div>
        ";

        // Send to admin email (get from config or database)
        $adminEmail = config('email.booking_confirmations', 'bookings_confirmations@elitecarhire.au');
        sendEmail($adminEmail, "New Booking - {$reference}", $body);
    }
}
