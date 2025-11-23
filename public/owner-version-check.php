<?php
// Check if the updated OwnerController was uploaded correctly
session_start();

echo "<h1>OwnerController Version Check</h1>";
echo "<pre>";

// Read the actual file content from the server
$controllerPath = __DIR__ . '/../app/controllers/OwnerController.php';

if (file_exists($controllerPath)) {
    $content = file_get_contents($controllerPath);

    echo "File exists: YES\n";
    echo "File size: " . strlen($content) . " bytes\n\n";

    // Check for specific fixes we made
    $checks = [
        'Constructor logging' => strpos($content, 'OwnerController::__construct() - Start') !== false,
        'Dashboard try-catch' => strpos($content, 'OwnerController::dashboard() - Start') !== false,
        'PHP 8.2 fix (vehicleCount)' => strpos($content, '$vehicleCount = db()->fetch') !== false,
        'PHP 8.2 fix (safe access)' => strpos($content, "\$vehicleCount['count'] ?? 0") !== false,
    ];

    echo "Version Checks:\n";
    foreach ($checks as $check => $result) {
        echo "  " . $check . ": " . ($result ? "✓ FOUND" : "✗ MISSING") . "\n";
    }

    echo "\n";

    // Show the dashboard stats section
    echo "Dashboard Stats Code (lines 72-86):\n";
    echo "=====================================\n";
    $lines = explode("\n", $content);
    for ($i = 71; $i < 86 && $i < count($lines); $i++) {
        echo ($i + 1) . ": " . $lines[$i] . "\n";
    }

} else {
    echo "ERROR: OwnerController.php not found!\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><strong>What this means:</strong></p>";
echo "<ul>";
echo "<li>If all checks show ✓ FOUND, the file was uploaded correctly</li>";
echo "<li>If any show ✗ MISSING, you need to re-upload the file</li>";
echo "</ul>";
echo "<p><a href='/owner/dashboard'>Test Owner Dashboard</a></p>";
