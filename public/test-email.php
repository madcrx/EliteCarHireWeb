<?php
/**
 * Email Testing Script
 *
 * This script tests email sending functionality.
 * Access it at: http://yoursite.com/test-email.php
 *
 * SECURITY: DELETE THIS FILE AFTER TESTING!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../helpers/email_sender.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Test - Elite Car Hire</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .box { border: 2px solid #dee2e6; padding: 20px; margin: 20px 0; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        input[type="email"], input[type="text"] { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>📧 Email Testing Tool</h1>
    <p><strong>WARNING:</strong> Delete this file after testing for security reasons!</p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $testEmail = $_POST['test_email'] ?? '';

        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            echo "<div class='box' style='background: #f8d7da; border-color: #dc3545;'>";
            echo "<p class='error'>Invalid email address!</p>";
            echo "</div>";
        } else {
            echo "<div class='box'>";
            echo "<h2>Test Results</h2>";

            // Test 1: Check mail() function availability
            echo "<h3>1. PHP mail() Function Check</h3>";
            if (function_exists('mail')) {
                echo "<p class='success'>✓ mail() function is available</p>";
            } else {
                echo "<p class='error'>✗ mail() function is NOT available</p>";
                echo "<p>Your server doesn't support PHP mail(). You need to configure SMTP.</p>";
            }

            // Test 2: Check email configuration
            echo "<h3>2. Email Configuration</h3>";
            echo "<pre>";
            echo "From Name: " . htmlspecialchars(config('email.from_name')) . "\n";
            echo "From Email: " . htmlspecialchars(config('email.from_address')) . "\n";
            echo "</pre>";

            // Test 3: Send a test email
            echo "<h3>3. Sending Test Email</h3>";
            echo "<p>Attempting to send email to: <strong>" . htmlspecialchars($testEmail) . "</strong></p>";

            $subject = "Test Email from Elite Car Hire";
            $body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #FFD700; padding: 30px 20px; text-align: center; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0; color: #FFD700;'>✓ Test Email</h1>
        </div>
        <div style='padding: 20px; background: white; border: 1px solid #ddd;'>
            <p>This is a test email from Elite Car Hire.</p>
            <p><strong>Sent at:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p>If you received this email, your email system is working correctly!</p>
        </div>
    </div>
</body>
</html>";

            // Try immediate sending
            $result = sendEmailImmediately($testEmail, $subject, $body);

            if ($result) {
                echo "<p class='success'>✓ Email sent successfully!</p>";
                echo "<p>Check the inbox of <strong>" . htmlspecialchars($testEmail) . "</strong></p>";
                echo "<p class='warning'>Note: The email might be in the spam folder.</p>";
            } else {
                echo "<p class='error'>✗ Email failed to send</p>";
                echo "<p>Possible issues:</p>";
                echo "<ul>";
                echo "<li>Server mail() function not configured</li>";
                echo "<li>SMTP not set up</li>";
                echo "<li>Firewall blocking outgoing emails</li>";
                echo "<li>Server doesn't allow sending emails</li>";
                echo "</ul>";
                echo "<p><strong>Solution:</strong> You may need to configure SMTP or contact your hosting provider.</p>";
            }

            // Test 4: Check email queue
            echo "<h3>4. Email Queue Status</h3>";
            try {
                $pendingCount = db()->fetch("SELECT COUNT(*) as count FROM email_queue WHERE status = 'pending'")['count'];
                $sentCount = db()->fetch("SELECT COUNT(*) as count FROM email_queue WHERE status = 'sent'")['count'];
                $failedCount = db()->fetch("SELECT COUNT(*) as count FROM email_queue WHERE status = 'failed'")['count'];

                echo "<pre>";
                echo "Pending: $pendingCount\n";
                echo "Sent: $sentCount\n";
                echo "Failed: $failedCount\n";
                echo "</pre>";

                if ($pendingCount > 0) {
                    echo "<p class='warning'>⚠ You have $pendingCount pending emails in the queue.</p>";
                    echo "<p>Run the email processor: <code>php public/process-email-queue.php</code></p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>Error checking queue: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            echo "</div>";
        }
    }
    ?>

    <div class='box'>
        <h2>Send Test Email</h2>
        <form method="POST">
            <label><strong>Your Email Address:</strong></label>
            <input type="email" name="test_email" placeholder="your@email.com" required>
            <p style="font-size: 0.9em; color: #666;">Enter your email to receive a test message</p>
            <button type="submit">Send Test Email</button>
        </form>
    </div>

    <div class='box'>
        <h2>Email Processor Information</h2>
        <p><strong>To process queued emails, run:</strong></p>
        <pre>php /path/to/public/process-email-queue.php</pre>

        <p><strong>To set up automatic processing with cron:</strong></p>
        <pre># Process email queue every 5 minutes
*/5 * * * * /usr/bin/php /path/to/public/process-email-queue.php</pre>

        <p><strong>Via cPanel Cron Jobs:</strong></p>
        <ol>
            <li>Log into cPanel</li>
            <li>Go to "Cron Jobs"</li>
            <li>Add new cron job</li>
            <li>Set to run every 5 minutes: <code>*/5 * * * *</code></li>
            <li>Command: <code>/usr/bin/php /home/username/public_html/public/process-email-queue.php</code></li>
        </ol>
    </div>

    <div class='box' style='background: #fff3cd;'>
        <h2>⚠️ IMPORTANT</h2>
        <p><strong>DELETE THIS FILE (test-email.php) after testing!</strong></p>
        <p>This file is for testing purposes only and should not be left accessible on a production server.</p>
    </div>
</body>
</html>
