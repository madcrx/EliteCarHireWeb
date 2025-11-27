#!/usr/bin/env php
<?php
/**
 * Email Reminders Cron Job
 *
 * This script checks for notification emails that were sent more than 12 hours ago
 * without a response, and sends reminder emails.
 *
 * Add to crontab to run every hour:
 * 0 * * * * /usr/bin/php /path/to/EliteCarHireWeb/cron/send_email_reminders.php >> /path/to/logs/email_reminders.log 2>&1
 */

// Include application bootstrap
require_once __DIR__ . '/../app/bootstrap.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting email reminder check...\n";

// Get all emails needing reminders
$reminders = getEmailsNeedingReminders();

if (empty($reminders)) {
    echo "[" . date('Y-m-d H:i:s') . "] No emails need reminders at this time.\n";
    exit(0);
}

echo "[" . date('Y-m-d H:i:s') . "] Found " . count($reminders) . " email(s) needing reminders.\n";

foreach ($reminders as $reminder) {
    echo "[" . date('Y-m-d H:i:s') . "] Processing reminder #{$reminder['id']} - {$reminder['email_type']} for {$reminder['entity_type']} #{$reminder['entity_id']}\n";

    try {
        // Check if entity still needs action
        $needsAction = checkIfEntityNeedsAction($reminder['entity_type'], $reminder['entity_id']);

        if (!$needsAction) {
            echo "[" . date('Y-m-d H:i:s') . "] Entity has been acted upon, marking as responded.\n";
            markEmailReminderResponded($reminder['entity_type'], $reminder['entity_id']);
            continue;
        }

        // Send reminder email based on type
        $sent = sendReminderEmail($reminder);

        if ($sent) {
            markReminderSent($reminder['id']);
            echo "[" . date('Y-m-d H:i:s') . "] Reminder sent successfully to {$reminder['recipient_email']}\n";
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] Failed to send reminder to {$reminder['recipient_email']}\n";
        }

    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] Error processing reminder: " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Email reminder check completed.\n";

/**
 * Check if an entity still needs action
 */
function checkIfEntityNeedsAction($entityType, $entityId) {
    switch ($entityType) {
        case 'booking':
            $booking = db()->fetch("SELECT status FROM bookings WHERE id = ?", [$entityId]);
            return $booking && $booking['status'] === 'pending';

        case 'vehicle':
            $vehicle = db()->fetch("SELECT status FROM vehicles WHERE id = ?", [$entityId]);
            return $vehicle && $vehicle['status'] === 'pending';

        case 'pending_change':
            $change = db()->fetch("SELECT status FROM pending_changes WHERE id = ?", [$entityId]);
            return $change && $change['status'] === 'pending';

        case 'contact_submission':
            $contact = db()->fetch("SELECT status FROM contact_submissions WHERE id = ?", [$entityId]);
            return $contact && $contact['status'] === 'new';

        default:
            return false;
    }
}

/**
 * Send reminder email
 */
function sendReminderEmail($reminder) {
    $subject = "REMINDER: " . $reminder['subject'];

    switch ($reminder['email_type']) {
        case 'booking_request':
            return sendBookingRequestReminder($reminder, $subject);

        case 'vehicle_approval':
            return sendVehicleApprovalReminder($reminder, $subject);

        case 'cancellation_request':
            return sendCancellationRequestReminder($reminder, $subject);

        case 'contact_form':
            return sendContactFormReminder($reminder, $subject);

        default:
            return false;
    }
}

/**
 * Send booking request reminder
 */
function sendBookingRequestReminder($reminder, $subject) {
    $booking = db()->fetch(
        "SELECT b.*, v.make, v.model, v.year, u.first_name, u.last_name
         FROM bookings b
         JOIN vehicles v ON b.vehicle_id = v.id
         JOIN users u ON b.customer_id = u.id
         WHERE b.id = ?",
        [$reminder['entity_id']]
    );

    if (!$booking) return false;

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #C5A253;'>⏰ REMINDER: New Booking Request</h2>
        <p><strong>This is a reminder about a booking request that needs your attention.</strong></p>

        <h3>Booking Details:</h3>
        <p><strong>Reference:</strong> {$booking['booking_reference']}</p>
        <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']} ({$booking['year']})</p>
        <p><strong>Customer:</strong> {$booking['first_name']} {$booking['last_name']}</p>
        <p><strong>Date:</strong> " . date('l, F d, Y', strtotime($booking['booking_date'])) . "</p>
        <p><strong>Time:</strong> " . date('g:i A', strtotime($booking['start_time'])) . " - " . date('g:i A', strtotime($booking['end_time'])) . "</p>
        <p><strong>Amount:</strong> $" . number_format($booking['total_amount'], 2) . " AUD</p>

        <p style='margin-top: 20px;'><strong>Please review and respond to this booking request as soon as possible.</strong></p>

        " . getEmailButton(url('/owner/bookings'), 'Review Booking', 'primary') ."

        <p style='color: #666; font-size: 14px; margin-top: 30px;'>This is an automated reminder. The original request was sent 12+ hours ago.</p>
    </div>
    ";

    return sendEmail($reminder['recipient_email'], $subject, $body);
}

/**
 * Send vehicle approval reminder
 */
function sendVehicleApprovalReminder($reminder, $subject) {
    $vehicle = db()->fetch(
        "SELECT v.*, u.first_name, u.last_name, u.email
         FROM vehicles v
         JOIN users u ON v.owner_id = u.id
         WHERE v.id = ?",
        [$reminder['entity_id']]
    );

    if (!$vehicle) return false;

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #C5A253;'>⏰ REMINDER: Vehicle Pending Approval</h2>
        <p><strong>This is a reminder about a vehicle listing that needs admin review.</strong></p>

        <h3>Vehicle Details:</h3>
        <p><strong>Vehicle:</strong> {$vehicle['make']} {$vehicle['model']} ({$vehicle['year']})</p>
        <p><strong>Owner:</strong> {$vehicle['first_name']} {$vehicle['last_name']}</p>
        <p><strong>Category:</strong> " . ucwords(str_replace('_', ' ', $vehicle['category'])) . "</p>
        <p><strong>Price:</strong> $" . number_format($vehicle['price_per_hour'], 2) . "/hour</p>

        <p style='margin-top: 20px;'><strong>Please review and approve or reject this listing.</strong></p>

        " . getEmailButton(url('/admin/vehicles'), 'Review Vehicle', 'primary') ."

        <p style='color: #666; font-size: 14px; margin-top: 30px;'>This is an automated reminder. The original submission was received 12+ hours ago.</p>
    </div>
    ";

    return sendEmail($reminder['recipient_email'], $subject, $body);
}

/**
 * Send cancellation request reminder
 */
function sendCancellationRequestReminder($reminder, $subject) {
    $change = db()->fetch(
        "SELECT pc.*, b.booking_reference, v.make, v.model
         FROM pending_changes pc
         JOIN bookings b ON pc.entity_id = b.id
         JOIN vehicles v ON b.vehicle_id = v.id
         WHERE pc.id = ? AND pc.change_type = 'cancellation'",
        [$reminder['entity_id']]
    );

    if (!$change) return false;

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #C5A253;'>⏰ REMINDER: Cancellation Request Pending</h2>
        <p><strong>This is a reminder about a booking cancellation request that needs your attention.</strong></p>

        <h3>Request Details:</h3>
        <p><strong>Booking:</strong> {$change['booking_reference']}</p>
        <p><strong>Vehicle:</strong> {$change['make']} {$change['model']}</p>
        <p><strong>Reason:</strong> {$change['reason']}</p>

        <p style='margin-top: 20px;'><strong>Please review and approve or reject this cancellation request.</strong></p>

        " . getEmailButton(url('/admin/pending-changes'), 'Review Request', 'primary') ."

        <p style='color: #666; font-size: 14px; margin-top: 30px;'>This is an automated reminder. The request was submitted 12+ hours ago.</p>
    </div>
    ";

    return sendEmail($reminder['recipient_email'], $subject, $body);
}

/**
 * Send contact form reminder
 */
function sendContactFormReminder($reminder, $subject) {
    $contact = db()->fetch(
        "SELECT * FROM contact_submissions WHERE id = ?",
        [$reminder['entity_id']]
    );

    if (!$contact) return false;

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #C5A253;'>⏰ REMINDER: Contact Form Submission</h2>
        <p><strong>This is a reminder about an unanswered contact form submission.</strong></p>

        <h3>Submission Details:</h3>
        <p><strong>From:</strong> {$contact['name']} ({$contact['email']})</p>
        <p><strong>Subject:</strong> {$contact['subject']}</p>
        <p><strong>Message:</strong></p>
        <p style='background: #f5f5f5; padding: 15px; border-left: 3px solid #C5A253;'>{$contact['message']}</p>

        <p style='margin-top: 20px;'><strong>Please respond to this inquiry.</strong></p>

        " . getEmailButton(url('/admin/contact-submissions'), 'View & Respond', 'primary') ."

        <p style='color: #666; font-size: 14px; margin-top: 30px;'>This is an automated reminder. The inquiry was submitted 12+ hours ago.</p>
    </div>
    ";

    return sendEmail($reminder['recipient_email'], $subject, $body);
}
