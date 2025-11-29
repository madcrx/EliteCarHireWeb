<?php
/**
 * Email Reminder Processor
 *
 * Sends reminder emails for:
 * - Customers who haven't approved additional charges (6 hours)
 * - Customers who haven't paid (6 hours)
 * - Any other pending reminders
 *
 * Usage:
 * 1. Run manually: php process-email-reminders.php
 * 2. Set up cron: */30 * * * * /usr/bin/php /path/to/process-email-reminders.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/helpers/email_sender.php';
require_once __DIR__ . '/../app/helpers/booking_emails.php';

echo "=== Email Reminder Processor ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = db();
    $sentCount = 0;
    $skippedCount = 0;

    // =====================================================
    // 1. APPROVAL REMINDERS (6 hours after status change)
    // =====================================================
    echo "Checking for approval reminders...\n";

    $approvalReminders = $db->query("
        SELECT b.id, b.booking_reference, b.updated_at,
               u.email, u.first_name, u.last_name,
               v.make, v.model
        FROM bookings b
        JOIN users u ON b.customer_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.status = 'awaiting_approval'
        AND b.payment_status = 'pending'
        AND TIMESTAMPDIFF(HOUR, b.updated_at, NOW()) >= 6
        AND NOT EXISTS (
            SELECT 1 FROM email_reminders er
            WHERE er.booking_id = b.id
            AND er.reminder_type = 'approval_reminder'
            AND er.sent_at > DATE_SUB(NOW(), INTERVAL 24 HOURS)
        )
        LIMIT 20
    ");

    foreach ($approvalReminders as $booking) {
        echo "  Sending approval reminder for booking {$booking['booking_reference']}...\n";

        $subject = "Reminder: Booking Approval Required (Ref: {$booking['booking_reference']})";
        $body = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'>" . getEmailStyles() . "</head>
<body>
    <div class='container'>
        " . getEmailHeader('Reminder: Action Required') . "
        <div class='content'>
            <div class='alert alert-warning'>
                <strong>Reminder:</strong> Your booking is awaiting your approval.
            </div>

            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>

            <p>This is a friendly reminder that your booking for <strong>{$booking['make']} {$booking['model']}</strong> is still awaiting your approval.</p>

            <p>The vehicle owner has updated your booking with additional charges. Please review and approve or reject the changes.</p>

            <div class='details'>
                <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                <p><strong>Vehicle:</strong> {$booking['make']} {$booking['model']}</p>
            </div>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . config('app.url') . "/customer/bookings?status=awaiting_approval' class='button button-warning'>Review Booking Now</a>
            </div>

            <p style='font-size: 0.9em; color: #666;'><strong>Note:</strong> Please respond as soon as possible to avoid booking cancellation.</p>
        </div>
        " . getEmailFooter() . "
    </div>
</body>
</html>";

        if (sendEmailEnhanced($booking['email'], $subject, $body, true)) {
            logEmailReminder($booking['id'], 'approval_reminder', $booking['email']);
            $sentCount++;
            echo "  ✓ Sent\n";
        } else {
            echo "  ✗ Failed\n";
        }
    }

    // =====================================================
    // 2. PAYMENT REMINDERS (6 hours after confirmation)
    // =====================================================
    echo "\nChecking for payment reminders...\n";

    $paymentReminders = $db->query("
        SELECT b.id, b.booking_reference, b.total_amount, b.updated_at,
               u.email, u.first_name, u.last_name,
               v.make, v.model
        FROM bookings b
        JOIN users u ON b.customer_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.status = 'confirmed'
        AND b.payment_status = 'pending'
        AND TIMESTAMPDIFF(HOUR, b.updated_at, NOW()) >= 6
        AND NOT EXISTS (
            SELECT 1 FROM email_reminders er
            WHERE er.booking_id = b.id
            AND er.reminder_type = 'payment_reminder'
            AND er.sent_at > DATE_SUB(NOW(), INTERVAL 24 HOURS)
        )
        LIMIT 20
    ");

    foreach ($paymentReminders as $booking) {
        echo "  Sending payment reminder for booking {$booking['booking_reference']}...\n";

        // Use the existing payment reminder function
        if (emailCustomerPaymentReminder($booking['id'])) {
            logEmailReminder($booking['id'], 'payment_reminder', $booking['email']);
            $sentCount++;
            echo "  ✓ Sent\n";
        } else {
            echo "  ✗ Failed\n";
        }
    }

    // =====================================================
    // SUMMARY
    // =====================================================
    echo "\n=== Summary ===\n";
    echo "Reminders sent: $sentCount\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
