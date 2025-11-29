<?php
/**
 * Email Queue Processor
 *
 * Processes queued emails from the email_queue table and sends them using PHP mail()
 *
 * Usage:
 * 1. Run manually: php process-email-queue.php
 * 2. Set up cron job: */5 * * * * /usr/bin/php /path/to/process-email-queue.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/helpers.php';

echo "=== Email Queue Processor ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = db();

    // Get pending emails (limit to 50 per run to avoid timeout)
    $emails = $db->query("
        SELECT * FROM email_queue
        WHERE status = 'pending'
        AND (attempts < 3 OR attempts IS NULL)
        ORDER BY created_at ASC
        LIMIT 50
    ");

    if (empty($emails)) {
        echo "No pending emails to process.\n";
        exit(0);
    }

    echo "Found " . count($emails) . " emails to process.\n\n";

    $sent = 0;
    $failed = 0;

    foreach ($emails as $email) {
        echo "Processing email ID: {$email['id']}\n";
        echo "  To: {$email['to_email']}\n";
        echo "  Subject: {$email['subject']}\n";

        // Update attempt count
        $db->execute(
            "UPDATE email_queue SET attempts = attempts + 1, last_attempt = NOW() WHERE id = ?",
            [$email['id']]
        );

        // Prepare headers
        $headers = "From: " . config('email.from_name') . " <" . config('email.from_address') . ">\r\n";
        $headers .= "Reply-To: " . config('email.from_address') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        // Send email using PHP mail()
        $success = mail(
            $email['to_email'],
            $email['subject'],
            $email['body_html'],
            $headers
        );

        if ($success) {
            // Mark as sent
            $db->execute(
                "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?",
                [$email['id']]
            );
            echo "  ✓ Email sent successfully\n";
            $sent++;
        } else {
            // Mark as failed if max attempts reached
            if ($email['attempts'] >= 2) { // Will be 3 after this attempt
                $db->execute(
                    "UPDATE email_queue SET status = 'failed' WHERE id = ?",
                    [$email['id']]
                );
                echo "  ✗ Email failed (max attempts reached)\n";
            } else {
                echo "  ⚠ Email failed (will retry)\n";
            }
            $failed++;
        }

        echo "\n";
    }

    echo "=== Summary ===\n";
    echo "Sent: $sent\n";
    echo "Failed: $failed\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
