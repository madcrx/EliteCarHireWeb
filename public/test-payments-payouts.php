<?php
// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test Payments Query
echo "=== TESTING PAYMENTS QUERY ===\n\n";

require __DIR__ . '/../app/Database.php';

try {
    $sql = "SELECT p.*, b.booking_reference, u.first_name, u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as customer_name
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN users u ON b.customer_id = u.id
            WHERE 1=1
            LIMIT 5";

    $payments = db()->fetchAll($sql);

    echo "✓ Payments query successful!\n";
    echo "Found " . count($payments) . " payments\n\n";

    if (!empty($payments)) {
        echo "Sample payment:\n";
        print_r($payments[0]);
    }

} catch (Exception $e) {
    echo "✗ Payments query FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Check payouts table structure
echo "\n=== CHECKING PAYOUTS TABLE ===\n\n";

try {
    $result = db()->fetchAll("DESCRIBE payouts");
    echo "✓ Payouts table structure:\n";
    foreach ($result as $row) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }

    // Check if paid_at column exists
    $hasPaidAt = false;
    foreach ($result as $row) {
        if ($row['Field'] === 'paid_at') {
            $hasPaidAt = true;
            break;
        }
    }

    if ($hasPaidAt) {
        echo "\n✓ paid_at column EXISTS\n";
    } else {
        echo "\n✗ paid_at column MISSING!\n";
        echo "This is why Process button fails!\n";
    }

} catch (Exception $e) {
    echo "✗ Error checking payouts table: " . $e->getMessage() . "\n";
}

// Check payments table
echo "\n\n=== CHECKING PAYMENTS TABLE ===\n\n";

try {
    $count = db()->fetch("SELECT COUNT(*) as count FROM payments");
    echo "✓ Payments table has " . $count['count'] . " records\n";

    // Check for orphaned records
    $orphaned = db()->fetch("SELECT COUNT(*) as count FROM payments p
                            LEFT JOIN bookings b ON p.booking_id = b.id
                            WHERE b.id IS NULL");

    if ($orphaned['count'] > 0) {
        echo "⚠ WARNING: Found " . $orphaned['count'] . " payment(s) with invalid booking_id\n";
        echo "This will cause the JOIN to fail!\n";
    } else {
        echo "✓ All payments have valid booking_id\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== END DIAGNOSTICS ===\n";
