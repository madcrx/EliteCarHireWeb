<?php
/**
 * Migration Check Script
 *
 * This script checks if the booking approval workflow migration has been applied.
 * Access it at: http://yoursite.com/check-migration.php
 *
 * DELETE THIS FILE after confirming migration is complete!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/helpers.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Migration Check - Booking Approval Workflow</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .box { border: 2px solid #dee2e6; padding: 20px; margin: 20px 0; border-radius: 8px; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>🔍 Booking Approval Workflow - Migration Check</h1>
    <p><strong>This script checks if the database migration has been applied successfully.</strong></p>
";

try {
    $db = db();

    // Check 1: Does additional_charges_reason column exist?
    echo "<div class='box'>";
    echo "<h2>Check 1: Column 'additional_charges_reason'</h2>";

    $columns = $db->query("SHOW COLUMNS FROM bookings LIKE 'additional_charges_reason'");

    if ($columns && count($columns) > 0) {
        echo "<p class='success'>✓ Column 'additional_charges_reason' EXISTS</p>";
        echo "<pre>" . print_r($columns[0], true) . "</pre>";
    } else {
        echo "<p class='error'>✗ Column 'additional_charges_reason' DOES NOT EXIST</p>";
        echo "<p>You need to run this SQL:</p>";
        echo "<pre>ALTER TABLE bookings
ADD COLUMN additional_charges_reason TEXT NULL AFTER additional_charges;</pre>";
    }
    echo "</div>";

    // Check 2: Does 'awaiting_approval' status exist?
    echo "<div class='box'>";
    echo "<h2>Check 2: Status 'awaiting_approval'</h2>";

    $statusColumn = $db->query("SHOW COLUMNS FROM bookings LIKE 'status'");

    if ($statusColumn && count($statusColumn) > 0) {
        $type = $statusColumn[0]['Type'];

        if (strpos($type, 'awaiting_approval') !== false) {
            echo "<p class='success'>✓ Status 'awaiting_approval' EXISTS in ENUM</p>";
            echo "<pre>Current ENUM values: " . htmlspecialchars($type) . "</pre>";
        } else {
            echo "<p class='error'>✗ Status 'awaiting_approval' DOES NOT EXIST in ENUM</p>";
            echo "<pre>Current ENUM values: " . htmlspecialchars($type) . "</pre>";
            echo "<p>You need to run this SQL:</p>";
            echo "<pre>ALTER TABLE bookings
MODIFY COLUMN status ENUM('pending', 'awaiting_approval', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';</pre>";
        }
    }
    echo "</div>";

    // Check 3: Routes check
    echo "<div class='box'>";
    echo "<h2>Check 3: Application Files</h2>";

    $files = [
        'Owner Confirmation Modal' => __DIR__ . '/../app/views/owner/bookings.php',
        'Customer Approval Modal' => __DIR__ . '/../app/views/customer/bookings.php',
        'Owner Controller' => __DIR__ . '/../app/controllers/OwnerController.php',
        'Customer Controller' => __DIR__ . '/../app/controllers/CustomerController.php',
        'Routes File' => __DIR__ . '/../public/index.php'
    ];

    foreach ($files as $name => $path) {
        if (file_exists($path)) {
            echo "<p class='success'>✓ {$name}: File exists</p>";
        } else {
            echo "<p class='error'>✗ {$name}: File NOT found</p>";
        }
    }
    echo "</div>";

    // Check 4: Check if routes exist
    echo "<div class='box'>";
    echo "<h2>Check 4: Routes Configuration</h2>";

    $indexContent = file_get_contents(__DIR__ . '/../public/index.php');

    $routes = [
        '/customer/bookings/approve' => strpos($indexContent, '/customer/bookings/approve') !== false,
        '/customer/bookings/reject' => strpos($indexContent, '/customer/bookings/reject') !== false,
        '/owner/bookings/confirm' => strpos($indexContent, '/owner/bookings/confirm') !== false
    ];

    foreach ($routes as $route => $exists) {
        if ($exists) {
            echo "<p class='success'>✓ Route '{$route}' is configured</p>";
        } else {
            echo "<p class='error'>✗ Route '{$route}' is NOT configured</p>";
        }
    }
    echo "</div>";

    // Final Summary
    echo "<div class='box' style='background: #e7f3ff; border-color: #0066cc;'>";
    echo "<h2>Summary</h2>";

    $allGood = ($columns && count($columns) > 0) &&
               (strpos($statusColumn[0]['Type'], 'awaiting_approval') !== false);

    if ($allGood) {
        echo "<p class='success' style='font-size: 1.2rem;'>✓ ALL CHECKS PASSED! Migration is complete.</p>";
        echo "<p>You can now:</p>";
        echo "<ol>";
        echo "<li>Test the owner booking confirmation with extra charges</li>";
        echo "<li>Test the customer approval workflow</li>";
        echo "<li><strong>DELETE THIS FILE (check-migration.php)</strong> for security</li>";
        echo "</ol>";
    } else {
        echo "<p class='error' style='font-size: 1.2rem;'>✗ MIGRATION INCOMPLETE</p>";
        echo "<p>Please apply the SQL migration from: <code>database/add_booking_approval_workflow.sql</code></p>";
        echo "<p>See <code>APPLY_MIGRATION.md</code> for detailed instructions.</p>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='box' style='background: #f8d7da; border-color: #dc3545;'>";
    echo "<p class='error'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</body></html>";
