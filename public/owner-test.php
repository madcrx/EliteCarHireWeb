<?php
// Simple diagnostic test for Owner Dashboard
session_start();

echo "<h1>Owner Dashboard Diagnostic Test</h1>";
echo "<pre>";

// Check 1: PHP Version
echo "1. PHP Version: " . phpversion() . "\n\n";

// Check 2: Session Data
echo "2. Session Data:\n";
echo "   User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "   Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "   Email: " . ($_SESSION['email'] ?? 'NOT SET') . "\n\n";

// Check 3: File Paths
echo "3. File Existence Checks:\n";
$files = [
    'helpers.php' => __DIR__ . '/../app/helpers.php',
    'OwnerController.php' => __DIR__ . '/../app/controllers/OwnerController.php',
    'owner/dashboard.php' => __DIR__ . '/../app/views/owner/dashboard.php',
    'notifications.php' => __DIR__ . '/../app/helpers/notifications.php',
    'booking_automation.php' => __DIR__ . '/../app/helpers/booking_automation.php',
];

foreach ($files as $name => $path) {
    echo "   " . $name . ": " . (file_exists($path) ? "EXISTS" : "MISSING") . "\n";
}
echo "\n";

// Check 4: Load helpers and test functions
echo "4. Helper Functions:\n";
try {
    require_once __DIR__ . '/../app/helpers.php';
    echo "   helpers.php loaded: OK\n";
    echo "   timeAgo exists: " . (function_exists('timeAgo') ? "YES" : "NO") . "\n";
    echo "   db exists: " . (function_exists('db') ? "YES" : "NO") . "\n";
    echo "   requireAuth exists: " . (function_exists('requireAuth') ? "YES" : "NO") . "\n";
} catch (Exception $e) {
    echo "   ERROR loading helpers: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 5: Database Connection
echo "5. Database Connection:\n";
try {
    require_once __DIR__ . '/../config/app.php';
    $db = db();
    echo "   Database connected: OK\n";

    // Try a simple query
    if (isset($_SESSION['user_id'])) {
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        echo "   User found: " . ($user ? "YES (ID: {$user['id']}, Role: {$user['role']})" : "NO") . "\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 6: Try loading OwnerController
echo "6. OwnerController Loading Test:\n";
try {
    // Set up autoloader
    spl_autoload_register(function ($class) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });

    echo "   Autoloader registered: OK\n";

    if (class_exists('controllers\OwnerController')) {
        echo "   OwnerController class found: YES\n";
    } else {
        echo "   OwnerController class found: NO\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 7: PHP Error Log Location
echo "7. PHP Configuration:\n";
echo "   display_errors: " . ini_get('display_errors') . "\n";
echo "   log_errors: " . ini_get('log_errors') . "\n";
echo "   error_log: " . (ini_get('error_log') ?: 'default location') . "\n";
echo "   Error reporting: " . error_reporting() . "\n";

echo "</pre>";

echo "<hr>";
echo "<p><a href='/owner/dashboard'>Try accessing /owner/dashboard</a></p>";
echo "<p><a href='/login'>Back to Login</a></p>";
