<?php
/**
 * Comprehensive Email Notification System
 *
 * This file contains all email templates and notification functions for the booking workflow.
 * Includes company logo integration and professional HTML formatting.
 */

/**
 * Get the company logo URL for email templates
 *
 * @return string Logo URL or empty string if no logo set
 */
function getEmailLogo() {
    try {
        // Get active logo from settings
        $activeLogo = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = 'active_logo_id'");
        $activeLogoId = $activeLogo['setting_value'] ?? null;

        if ($activeLogoId) {
            $logo = db()->fetch("SELECT image_path FROM site_images WHERE id = ? AND image_type = 'logo'", [$activeLogoId]);
            if ($logo) {
                // Return full URL
                return config('app.url') . $logo['image_path'];
            }
        }

        return '';
    } catch (Exception $e) {
        error_log("Error getting email logo: " . $e->getMessage());
        return '';
    }
}

/**
 * Get email header HTML with logo
 *
 * @param string $title Email title
 * @return string HTML header
 */
function getEmailHeader($title) {
    $logoUrl = getEmailLogo();
    $logoHtml = '';

    if ($logoUrl) {
        $logoHtml = "<img src='{$logoUrl}' alt='Elite Car Hire' style='max-height: 60px; margin-bottom: 15px;'>";
    } else {
        $logoHtml = "<h1 style='margin: 0; color: #FFD700; font-size: 1.8rem;'>Elite Car Hire</h1>";
    }

    return "
    <div class='header' style='background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #FFD700; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;'>
        {$logoHtml}
        <h2 style='margin: 10px 0 0 0; color: #ffffff; font-size: 1.5rem;'>{$title}</h2>
    </div>";
}

/**
 * Get email footer HTML
 *
 * @return string HTML footer
 */
function getEmailFooter() {
    $siteUrl = config('app.url');
    return "
    <div class='footer' style='background: #f8f9fa; padding: 20px; text-align: center; font-size: 0.9em; color: #666; border-radius: 0 0 8px 8px;'>
        <p style='margin: 5px 0;'>This is an automated message from Elite Car Hire.</p>
        <p style='margin: 5px 0;'>Please do not reply to this email.</p>
        <p style='margin: 5px 0;'><a href='{$siteUrl}' style='color: #FFD700; text-decoration: none;'>Visit our website</a></p>
    </div>";
}

/**
 * Get base email styles
 */
function getEmailStyles() {
    return "
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #dee2e6; border-top: none; }
        .alert { padding: 15px; margin: 20px 0; border-left: 4px solid; }
        .alert-info { background: #d1ecf1; border-color: #0c5460; color: #0c5460; }
        .alert-success { background: #d4edda; border-color: #28a745; color: #155724; }
        .alert-warning { background: #fff3cd; border-color: #ff9800; color: #856404; }
        .alert-danger { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .button { display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; font-weight: bold; }
        .button-primary { background: #FFD700; color: #1a1a1a; }
        .button-success { background: #28a745; }
        .button-warning { background: #ff9800; }
        .details { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .price-box { background: #d4edda; border: 2px solid #28a745; padding: 15px; border-radius: 4px; text-align: center; font-size: 1.3rem; font-weight: bold; color: #155724; margin: 15px 0; }
    </style>";
}

// ===========================
// CUSTOMER EMAIL NOTIFICATIONS
// ===========================

/**
 * Send email to customer when new booking is created
 */
function emailCustomerBookingCreated($customerId, $bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, v.year, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Booking Request Received (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Request Received') . "
        <div class='content'>
            <div class='alert alert-success'>
                <strong>Thank you!</strong> Your booking request has been received and is awaiting owner confirmation.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>We've received your booking request for <strong>{$booking['make']} {$booking['model']}</strong>.</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']} ({$booking['year']})</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Time:</strong> " . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
            </div>

            <div class='price-box'>
                Estimated Amount: $" . number_format($booking['base_amount'], 2) . "
            </div>

            <p><strong>What happens next:</strong></p>
            <ol>
                <li>The vehicle owner will review your booking request</li>
                <li>You'll receive an email once the owner confirms</li>
                <li>You can then proceed with payment to secure your booking</li>
            </ol>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings/{$booking['id']}' class='button button-primary'>View Booking Details</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'>You'll be notified by email when there's an update on your booking.</p>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending customer booking created email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send payment reminder to customer
 */
function emailCustomerPaymentReminder($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ? AND b.status = 'confirmed' AND b.payment_status = 'pending'
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Payment Required - Booking Confirmed (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Payment Required') . "
        <div class='content'>
            <div class='alert alert-warning'>
                <strong>Action Required:</strong> Your booking has been confirmed. Please complete payment to secure your reservation.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Great news! Your booking for <strong>{$booking['make']} {$booking['model']}</strong> has been confirmed by the vehicle owner.</p>

            <p>To secure your booking, please complete the payment as soon as possible.</p>

            <div class='price-box'>
                Total Amount Due: $" . number_format($booking['total_amount'], 2) . "
            </div>

            <div class='details'>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']}</p>
            </div>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings/{$booking['id']}' class='button button-success'>Make Payment Now</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'><strong>Note:</strong> Your booking is not secured until payment is received.</p>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending payment reminder: " . $e->getMessage());
        return false;
    }
}

// ===========================
// OWNER EMAIL NOTIFICATIONS
// ===========================

/**
 * Send email to owner when new booking is received
 */
function emailOwnerNewBooking($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model,
                   c.first_name as customer_first_name, c.last_name as customer_last_name,
                   o.first_name as owner_first_name, o.last_name as owner_last_name, o.email as owner_email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users c ON b.customer_id = c.id
            JOIN users o ON b.owner_id = o.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "New Booking Request (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('New Booking Request') . "
        <div class='content'>
            <div class='alert alert-info'>
                <strong>New Booking!</strong> You have received a new booking request for your vehicle.
            </div>

            <p>Dear {$booking['owner_first_name']} {$booking['owner_last_name']},</p>

            <p>You have a new booking request for your <strong>{$booking['make']} {$booking['model']}</strong>.</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Customer:</strong> {$booking['customer_first_name']} {$booking['customer_last_name']}</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Time:</strong> " . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
                <p><strong>Amount:</strong> $" . number_format($booking['base_amount'], 2) . "</p>
            </div>

            <p><strong>Action Required:</strong></p>
            <p>Please review this booking request and confirm or add any additional charges for excess travel if needed.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/owner/bookings' class='button button-warning'>Review & Confirm Booking</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['owner_email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending owner new booking email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to owner when customer approves additional charges
 */
function emailOwnerCustomerApproved($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model,
                   o.first_name as owner_first_name, o.last_name as owner_last_name, o.email as owner_email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users o ON b.owner_id = o.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Customer Approved Charges (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Customer Approved') . "
        <div class='content'>
            <div class='alert alert-success'>
                <strong>Good News!</strong> The customer has approved the additional charges.
            </div>

            <p>Dear {$booking['owner_first_name']} {$booking['owner_last_name']},</p>

            <p>The customer has approved the updated booking amount for <strong>{$booking['make']} {$booking['model']}</strong>.</p>

            <div class='price-box' style='background: #d1ecf1; border-color: #0c5460; color: #0c5460;'>
                Approved Amount: $" . number_format($booking['total_amount'], 2) . "
            </div>

            <div class='details'>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Base Amount:</strong> $" . number_format($booking['base_amount'], 2) . "</p>
                <p><strong>Additional Charges:</strong> $" . number_format($booking['additional_charges'], 2) . "</p>
            </div>

            <p><strong>Next Step:</strong> Awaiting customer payment. You'll be notified once payment is received.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/owner/bookings' class='button button-primary'>View Booking</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['owner_email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending owner approval email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to customer when booking is confirmed (owner confirmed)
 */
function emailCustomerBookingConfirmed($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, v.year, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Booking Confirmed - Payment Required (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Confirmed!') . "
        <div class='content'>
            <div class='alert alert-success'>
                <strong>Great News!</strong> Your booking has been confirmed by the vehicle owner.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Your booking for <strong>{$booking['make']} {$booking['model']}</strong> has been confirmed!</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']} ({$booking['year']})</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Time:</strong> " . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
            </div>

            <div class='price-box'>
                Total Amount: $" . number_format($booking['total_amount'], 2) . "
            </div>

            <p><strong>Next Step:</strong> Please complete your payment to secure this booking.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings/{$booking['id']}' class='button button-success'>Make Payment Now</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'>Your booking is not secured until payment is received.</p>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending customer booking confirmed email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to customer when booking is cancelled
 */
function emailCustomerBookingCancelled($bookingId, $cancelledBy = 'owner') {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $cancelReason = $cancelledBy === 'customer'
            ? 'You have cancelled this booking.'
            : 'This booking has been cancelled by the vehicle owner.';

        $subject = "Booking Cancelled (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Cancelled') . "
        <div class='content'>
            <div class='alert alert-danger'>
                <strong>Cancellation Notice:</strong> Booking has been cancelled.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>{$cancelReason}</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Cancelled Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']}</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Status:</strong> Cancelled</p>
            </div>";

        if ($booking['payment_status'] === 'paid') {
            $body .= "
            <div class='alert alert-info'>
                <strong>Refund Information:</strong> If you have already paid, a refund will be processed according to our cancellation policy.
            </div>";
        }

        $body .= "
            <p>We're sorry this booking didn't work out. Feel free to browse our other available vehicles!</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/vehicles' class='button button-primary'>Browse Vehicles</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending customer cancellation email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to owner when booking is cancelled
 */
function emailOwnerBookingCancelled($bookingId, $cancelledBy = 'customer') {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model,
                   c.first_name as customer_first_name, c.last_name as customer_last_name,
                   o.first_name as owner_first_name, o.last_name as owner_last_name, o.email as owner_email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users c ON b.customer_id = c.id
            JOIN users o ON b.owner_id = o.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $cancelReason = $cancelledBy === 'owner'
            ? 'You have cancelled this booking.'
            : 'This booking has been cancelled by the customer.';

        $subject = "Booking Cancelled (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Cancelled') . "
        <div class='content'>
            <div class='alert alert-warning'>
                <strong>Cancellation Notice:</strong> Booking has been cancelled.
            </div>

            <p>Dear {$booking['owner_first_name']} {$booking['owner_last_name']},</p>

            <p>{$cancelReason}</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Cancelled Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']}</p>
                <p><strong>Customer:</strong> {$booking['customer_first_name']} {$booking['customer_last_name']}</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Status:</strong> Cancelled</p>
            </div>

            <p>Your vehicle is now available for other bookings on this date.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/owner/bookings' class='button button-primary'>View All Bookings</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['owner_email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending owner cancellation email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to customer when payment is received
 */
function emailCustomerPaymentReceived($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, v.year, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Payment Received - Booking Secured (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Payment Received!') . "
        <div class='content'>
            <div class='alert alert-success'>
                <strong>Success!</strong> Your payment has been received and your booking is now secured.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Thank you! We've received your payment for <strong>{$booking['make']} {$booking['model']}</strong>.</p>

            <div class='price-box' style='background: #d4edda; border-color: #28a745;'>
                Payment Received: $" . number_format($booking['total_amount'], 2) . "
            </div>

            <div class='details'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']} ({$booking['year']})</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Time:</strong> " . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
            </div>

            <p><strong>What's Next:</strong></p>
            <ul>
                <li>You'll receive a reminder email closer to your booking date</li>
                <li>Make sure to arrive on time for pickup</li>
            </ul>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings/{$booking['id']}' class='button button-primary'>View Booking Details</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'>We look forward to serving you!</p>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending customer payment received email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to owner when payment is received
 */
function emailOwnerPaymentReceived($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model,
                   c.first_name as customer_first_name, c.last_name as customer_last_name,
                   o.first_name as owner_first_name, o.last_name as owner_last_name, o.email as owner_email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users c ON b.customer_id = c.id
            JOIN users o ON b.owner_id = o.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Payment Received for Booking (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Payment Received') . "
        <div class='content'>
            <div class='alert alert-success'>
                <strong>Good News!</strong> Payment has been received for your booking.
            </div>

            <p>Dear {$booking['owner_first_name']} {$booking['owner_last_name']},</p>

            <p>The customer has completed payment for the booking of your <strong>{$booking['make']} {$booking['model']}</strong>.</p>

            <div class='price-box' style='background: #d4edda; border-color: #28a745;'>
                Payment Received: $" . number_format($booking['total_amount'], 2) . "
            </div>

            <div class='details'>
                <h3 style='margin-top: 0;'>Booking Details</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Customer:</strong> {$booking['customer_first_name']} {$booking['customer_last_name']}</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Time:</strong> " . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</p>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Prepare your vehicle for the booking date</li>
                <li>Ensure the vehicle is clean and fueled</li>
                <li>Be ready for customer pickup at the scheduled time</li>
            </ul>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/owner/bookings' class='button button-primary'>View Booking Details</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['owner_email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending owner payment received email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to customer when booking status changes to in_progress
 */
function emailCustomerBookingStarted($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Your Booking Has Started (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking In Progress') . "
        <div class='content'>
            <div class='alert alert-info'>
                <strong>Enjoy Your Ride!</strong> Your booking is now in progress.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Your booking for <strong>{$booking['make']} {$booking['model']}</strong> has started. We hope you have a great experience!</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Active Booking</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']}</p>
                <p><strong>End Time:</strong> " . date('g:i A', strtotime($booking['end_time'])) . "</p>
            </div>

            <p><strong>Important Reminders:</strong></p>
            <ul>
                <li>Return the vehicle on time</li>
                <li>Keep the vehicle clean</li>
                <li>Drive safely and follow all traffic rules</li>
                <li>Contact the owner immediately if any issues arise</li>
            </ul>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings/{$booking['id']}' class='button button-primary'>View Booking Details</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending booking started email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to customer when booking is completed
 */
function emailCustomerBookingCompleted($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Booking Completed - Thank You! (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Completed') . "
        <div class='content'>
            <div class='alert alert-success'>
                <strong>Thank You!</strong> Your booking has been completed.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Thank you for using Elite Car Hire! Your booking for <strong>{$booking['make']} {$booking['model']}</strong> has been completed.</p>

            <div class='details'>
                <h3 style='margin-top: 0;'>Completed Booking</h3>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']}</p>
                <p><strong>Date:</strong> " . date('l, F j, Y', strtotime($booking['booking_date'])) . "</p>
                <p><strong>Duration:</strong> {$booking['duration_hours']} hours</p>
            </div>

            <p>We hope you had a wonderful experience! Your feedback is important to us.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/vehicles' class='button button-primary'>Book Another Vehicle</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'>We look forward to serving you again soon!</p>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending booking completed email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to customer when they reject additional charges
 */
function emailCustomerBookingRejected($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model, u.first_name, u.last_name, u.email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Booking Cancelled - Additional Charges Rejected (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Cancelled') . "
        <div class='content'>
            <div class='alert alert-warning'>
                <strong>Booking Cancelled:</strong> You rejected the additional charges.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>Your booking for <strong>{$booking['make']} {$booking['model']}</strong> has been cancelled as you did not accept the additional charges.</p>

            <div class='details'>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Status:</strong> Cancelled</p>
            </div>

            <p>We understand that the additional charges didn't work for you. Feel free to browse our other available vehicles!</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/vehicles' class='button button-primary'>Browse Vehicles</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending customer rejection email: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email to owner when customer rejects additional charges
 */
function emailOwnerBookingRejected($bookingId) {
    try {
        $booking = db()->fetch("
            SELECT b.*, v.make, v.model,
                   c.first_name as customer_first_name, c.last_name as customer_last_name,
                   o.first_name as owner_first_name, o.last_name as owner_last_name, o.email as owner_email
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN users c ON b.customer_id = c.id
            JOIN users o ON b.owner_id = o.id
            WHERE b.id = ?
        ", [$bookingId]);

        if (!$booking) return false;

        $subject = "Booking Cancelled - Customer Rejected Charges (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Booking Cancelled') . "
        <div class='content'>
            <div class='alert alert-warning'>
                <strong>Booking Cancelled:</strong> Customer rejected the additional charges.
            </div>

            <p>Dear {$booking['owner_first_name']} {$booking['owner_last_name']},</p>

            <p>The customer has rejected the additional charges for the booking of your <strong>{$booking['make']} {$booking['model']}</strong>, and the booking has been cancelled.</p>

            <div class='details'>
                <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Customer:</strong> {$booking['customer_first_name']} {$booking['customer_last_name']}</p>
                <p><strong>Status:</strong> Cancelled</p>
            </div>

            <p>Your vehicle is now available for other bookings on this date.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/owner/bookings' class='button button-primary'>View All Bookings</a>
            </div>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        return sendEmailEnhanced($booking['owner_email'], $subject, $body, true);
    } catch (Exception $e) {
        error_log("Error sending owner rejection email: " . $e->getMessage());
        return false;
    }
}

/**
 * Log email reminder for tracking
 */
function logEmailReminder($bookingId, $type, $recipientEmail) {
    try {
        db()->execute("
            INSERT INTO email_reminders (booking_id, reminder_type, recipient_email, sent_at, status)
            VALUES (?, ?, ?, NOW(), 'sent')
        ", [$bookingId, $type, $recipientEmail]);
    } catch (Exception $e) {
        error_log("Error logging email reminder: " . $e->getMessage());
    }
}
