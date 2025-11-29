<?php
/**
 * Email Reminders Migration Script
 *
 * This script applies the email_reminders table migration.
 * Access it at: http://yoursite.com/apply-email-reminders-migration.php
 *
 * DELETE THIS FILE after running the migration!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/helpers.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Reminders Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .box { border: 2px solid #dee2e6; padding: 20px; margin: 20px 0; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>📧 Email Reminders Table Migration</h1>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_migration'])) {
        echo "<div class='box'>";
        echo "<h2>Migration Results</h2>";

        try {
            $db = db();

            // Step 1: Create email_reminders table
            echo "<h3>Step 1: Creating email_reminders table</h3>";
            try {
                $sql1 = "CREATE TABLE IF NOT EXISTS email_reminders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    booking_id INT NOT NULL,
                    reminder_type ENUM('payment_reminder', 'approval_reminder', 'booking_confirmation', 'general') NOT NULL,
                    recipient_email VARCHAR(255) NOT NULL,
                    sent_at TIMESTAMP NULL,
                    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
                    attempts INT DEFAULT 0,
                    next_retry TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
                    INDEX idx_booking (booking_id),
                    INDEX idx_status (status),
                    INDEX idx_next_retry (next_retry)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $db->execute($sql1);
                echo "<p class='success'>✓ Table 'email_reminders' created successfully</p>";
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "<p class='warning'>⚠ Table 'email_reminders' already exists (skipping)</p>";
                } else {
                    throw $e;
                }
            }

            // Step 2: Add last_email_sent column to bookings
            echo "<h3>Step 2: Adding last_email_sent column to bookings</h3>";
            try {
                // Check if column already exists
                $columns = $db->query("SHOW COLUMNS FROM bookings LIKE 'last_email_sent'");

                if ($columns && count($columns) > 0) {
                    echo "<p class='warning'>⚠ Column 'last_email_sent' already exists (skipping)</p>";
                } else {
                    $sql2 = "ALTER TABLE bookings ADD COLUMN last_email_sent TIMESTAMP NULL AFTER updated_at";
                    $db->execute($sql2);
                    echo "<p class='success'>✓ Column 'last_email_sent' added successfully</p>";
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "<p class='warning'>⚠ Column 'last_email_sent' already exists (skipping)</p>";
                } else {
                    throw $e;
                }
            }

            echo "<div class='box' style='background: #d4edda; border-color: #28a745; margin-top: 30px;'>";
            echo "<h2 class='success'>✓ Migration Applied Successfully!</h2>";
            echo "<p>The email_reminders table and last_email_sent column have been added.</p>";
            echo "<p><strong>Next Steps:</strong></p>";
            echo "<ul>";
            echo "<li>Test the email notification system</li>";
            echo "<li>Set up cron jobs for email processors (see EMAIL_SETUP.md)</li>";
            echo "<li><strong>DELETE THIS FILE</strong> for security</li>";
            echo "</ul>";
            echo "</div>";

        } catch (Exception $e) {
            echo "<div class='box' style='background: #f8d7da; border-color: #dc3545;'>";
            echo "<p class='error'>✗ Migration Failed</p>";
            echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";
        }

        echo "</div>";

    } else {
        // Display information and migration button
        echo "<div class='box'>";
        echo "<h2>About This Migration</h2>";
        echo "<p>This migration will:</p>";
        echo "<ul>";
        echo "<li>Create the <code>email_reminders</code> table for tracking reminder emails</li>";
        echo "<li>Add <code>last_email_sent</code> column to the <code>bookings</code> table</li>";
        echo "</ul>";
        echo "<p><strong>This migration is required for:</strong></p>";
        echo "<ul>";
        echo "<li>6-hour reminder emails for unpaid bookings</li>";
        echo "<li>6-hour reminder emails for unapproved bookings</li>";
        echo "<li>Email notification tracking and spam prevention</li>";
        echo "</ul>";
        echo "</div>";

        echo "<div class='box' style='background: #fff3cd; border-color: #ffc107;'>";
        echo "<h2>⚠️ Before You Proceed</h2>";
        echo "<ul>";
        echo "<li>Make sure you have a database backup</li>";
        echo "<li>This migration can be run safely multiple times</li>";
        echo "<li>Review the migration SQL in <code>database/add_email_reminders.sql</code></li>";
        echo "</ul>";
        echo "</div>";

        echo "<form method='POST'>";
        echo "<p><button type='submit' name='apply_migration'>Apply Migration Now</button></p>";
        echo "</form>";
    }
    ?>

    <div class='box' style='background: #f8d7da; border-color: #dc3545;'>
        <h2>🔒 Security Warning</h2>
        <p><strong>DELETE THIS FILE (apply-email-reminders-migration.php) immediately after running the migration!</strong></p>
        <p>This file should not be accessible on a production server.</p>
    </div>
</body>
</html>
