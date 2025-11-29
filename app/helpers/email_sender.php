<?php
/**
 * Immediate Email Sender
 *
 * This script can be triggered to send emails immediately without queuing.
 * Used for critical notifications that need instant delivery.
 *
 * Call this from controllers using: sendEmailImmediately($to, $subject, $body)
 */

/**
 * Send email immediately using PHP mail()
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string|null $fromName Optional sender name
 * @param string|null $fromEmail Optional sender email
 * @return bool True if sent successfully, false otherwise
 */
function sendEmailImmediately($to, $subject, $htmlBody, $fromName = null, $fromEmail = null) {
    try {
        // Use config values if not provided
        $fromName = $fromName ?? config('email.from_name');
        $fromEmail = $fromEmail ?? config('email.from_address');

        // Validate email address
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email address: $to");
            return false;
        }

        // Prepare headers
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        // Send email
        $success = mail($to, $subject, $htmlBody, $headers);

        if ($success) {
            error_log("Email sent successfully to: $to - Subject: $subject");
            return true;
        } else {
            error_log("Failed to send email to: $to - Subject: $subject");
            return false;
        }

    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}

/**
 * Enhanced sendEmail function that sends immediately AND queues
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @param bool $immediate If true, send immediately. If false, just queue.
 * @return bool True if successful
 */
function sendEmailEnhanced($to, $subject, $body, $immediate = true) {
    $success = true;

    // Send immediately if requested
    if ($immediate) {
        $success = sendEmailImmediately($to, $subject, $body);
    }

    // Also queue for backup/record
    $sql = "INSERT INTO email_queue (to_email, to_name, subject, body_html, status) VALUES (?, ?, ?, ?, ?)";
    try {
        $status = $immediate && $success ? 'sent' : 'pending';
        db()->execute($sql, [$to, '', $subject, $body, $status]);

        if ($immediate && $success) {
            // Mark as sent with timestamp
            db()->execute(
                "UPDATE email_queue SET sent_at = NOW() WHERE to_email = ? AND subject = ? ORDER BY id DESC LIMIT 1",
                [$to, $subject]
            );
        }
    } catch (Exception $e) {
        error_log("Failed to queue email: " . $e->getMessage());
    }

    return $success;
}
