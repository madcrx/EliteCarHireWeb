<?php
// Test which version of AdminController is on the server
header('Content-Type: text/plain');

$file = __DIR__ . '/../app/controllers/AdminController.php';

echo "=== ADMIN CONTROLLER VERSION CHECK ===\n\n";

if (!file_exists($file)) {
    echo "✗ AdminController.php NOT FOUND!\n";
    exit;
}

echo "✓ AdminController.php exists\n\n";

$content = file_get_contents($file);

// Check for the updated payments query with customer_name
if (strpos($content, 'CONCAT(u.first_name') !== false && strpos($content, 'as customer_name') !== false) {
    echo "✓ CORRECT VERSION: Payments query includes customer_name\n";
    echo "✓ The updated AdminController IS on the server\n\n";
} else {
    echo "✗ OLD VERSION: Payments query missing customer_name\n";
    echo "✗ You need to upload the updated AdminController.php\n\n";
}

// Check for processPayout method with 'completed' status
if (strpos($content, "status = 'completed'") !== false) {
    echo "✓ CORRECT: ProcessPayout uses 'completed' status\n";
} else if (strpos($content, "status = 'paid'") !== false) {
    echo "✗ OLD: ProcessPayout still uses 'paid' status\n";
    echo "✗ Upload the latest version\n";
} else {
    echo "⚠ ProcessPayout method might be missing\n";
}

echo "\n\nFile last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
echo "File size: " . number_format(filesize($file)) . " bytes\n";

echo "\n=== END CHECK ===\n";
