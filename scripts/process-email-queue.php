#!/usr/bin/env php
<?php
/**
 * Email Queue Processor
 *
 * This script processes pending emails from the email_queue table
 * and sends them via SMTP. Should be run via cron job every minute.
 *
 * Usage: php scripts/process-email-queue.php
 */

// Load application
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../app/Database.php';

$config = require __DIR__ . '/../config/app.php';

// Initialize database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    echo "[ERROR] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Get pending emails (max 50 per run, oldest first)
$pendingEmails = $db->fetchAll("
    SELECT * FROM email_queue
    WHERE status = 'pending' AND attempts < 3
    ORDER BY created_at ASC
    LIMIT 50
");

if (empty($pendingEmails)) {
    echo "[INFO] No pending emails to process.\n";
    exit(0);
}

echo "[INFO] Found " . count($pendingEmails) . " pending email(s) to process.\n";

$successCount = 0;
$failCount = 0;

foreach ($pendingEmails as $email) {
    echo "[PROCESSING] Email ID: {$email['id']} to {$email['to_email']}\n";

    // Update attempt count
    $db->execute("UPDATE email_queue SET attempts = attempts + 1, last_attempt = NOW() WHERE id = ?", [$email['id']]);

    try {
        // Send email via SMTP
        $sent = sendViaSMTP(
            $email['to_email'],
            $email['to_name'],
            $email['subject'],
            $email['body_html'],
            $config['email']
        );

        if ($sent) {
            // Mark as sent
            $db->execute("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?", [$email['id']]);
            echo "[SUCCESS] Email ID: {$email['id']} sent successfully.\n";
            $successCount++;
        } else {
            throw new Exception("SMTP send returned false");
        }

    } catch (Exception $e) {
        // Log error
        $errorMsg = $e->getMessage();
        echo "[ERROR] Email ID: {$email['id']} failed: {$errorMsg}\n";

        // Mark as failed if max attempts reached
        if ($email['attempts'] + 1 >= 3) {
            $db->execute("UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?",
                        [$errorMsg, $email['id']]);
            echo "[FAILED] Email ID: {$email['id']} marked as failed after 3 attempts.\n";
        } else {
            $db->execute("UPDATE email_queue SET error_message = ? WHERE id = ?",
                        [$errorMsg, $email['id']]);
        }

        $failCount++;
    }

    // Small delay to avoid overwhelming SMTP server
    usleep(100000); // 0.1 seconds
}

echo "\n[SUMMARY] Processed: " . count($pendingEmails) . " | Success: {$successCount} | Failed: {$failCount}\n";
exit(0);

/**
 * Send email via SMTP
 */
function sendViaSMTP($toEmail, $toName, $subject, $bodyHtml, $emailConfig) {
    // Use PHPMailer if available, or fall back to PHP mail() with SMTP headers

    // Check if PHPMailer is available
    $phpMailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';

    if (file_exists($phpMailerPath)) {
        // Use PHPMailer
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $emailConfig['smtp_host'];
            $mail->SMTPAuth   = !empty($emailConfig['smtp_username']);
            $mail->Username   = $emailConfig['smtp_username'];
            $mail->Password   = $emailConfig['smtp_password'];
            $mail->SMTPSecure = $emailConfig['smtp_encryption'];
            $mail->Port       = $emailConfig['smtp_port'];

            // Recipients
            $mail->setFrom($emailConfig['from_address'], $emailConfig['from_name']);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($emailConfig['from_address'], $emailConfig['from_name']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = strip_tags($bodyHtml);

            $mail->send();
            return true;

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            throw new Exception("PHPMailer Error: " . $mail->ErrorInfo);
        }

    } else {
        // Fallback to PHP mail() function
        $headers  = "From: {$emailConfig['from_name']} <{$emailConfig['from_address']}>\r\n";
        $headers .= "Reply-To: {$emailConfig['from_address']}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $fullToEmail = empty($toName) ? $toEmail : "$toName <$toEmail>";

        if (mail($fullToEmail, $subject, $bodyHtml, $headers)) {
            return true;
        } else {
            throw new Exception("PHP mail() function failed");
        }
    }
}
