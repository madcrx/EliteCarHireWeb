<?php
// Direct test of payments page to see actual error
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/helpers.php';

echo "<pre>";
echo "=== DIRECT PAYMENTS PAGE TEST ===\n\n";

try {
    // Simulate the payments() method
    $status = $_GET['status'] ?? 'all';

    $sql = "SELECT p.*, b.booking_reference, u.first_name, u.last_name,
            CONCAT(u.first_name, ' ', u.last_name) as customer_name
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN users u ON b.customer_id = u.id
            WHERE 1=1";
    $params = [];

    if ($status !== 'all') {
        $sql .= " AND p.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY p.created_at DESC";

    echo "Executing query...\n";
    $payments = db()->fetchAll($sql, $params);

    echo "✓ Query successful!\n";
    echo "Found " . count($payments) . " payments\n\n";

    // Now try to load the view
    echo "Attempting to load view...\n";

    // Check if view file exists
    $viewFile = __DIR__ . '/../app/views/admin/payments.php';
    if (!file_exists($viewFile)) {
        echo "✗ View file NOT found: $viewFile\n";
        exit;
    }

    echo "✓ View file exists\n";

    // Try to include it
    ob_start();
    include $viewFile;
    $output = ob_get_clean();

    echo "✓ View loaded successfully!\n";
    echo "Output length: " . strlen($output) . " bytes\n";

    // Show first 500 chars of output
    echo "\nFirst 500 chars of output:\n";
    echo substr($output, 0, 500) . "...\n";

} catch (Exception $e) {
    echo "✗ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== END TEST ===\n";
echo "</pre>";
