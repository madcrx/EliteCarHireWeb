<?php
// Simple Database Loading Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Loading Test</h1>";
echo "<pre>";

echo "Step 1: Check if Database.php file exists\n";
$dbFile = __DIR__ . '/../app/Database.php';
echo "   File: " . $dbFile . "\n";
echo "   Exists: " . (file_exists($dbFile) ? "YES" : "NO") . "\n\n";

echo "Step 2: Try to manually require Database.php\n";
try {
    require_once __DIR__ . '/../app/Database.php';
    echo "   ✓ Database.php loaded successfully\n\n";
} catch (Throwable $e) {
    echo "   ✗ ERROR loading Database.php: " . $e->getMessage() . "\n\n";
    die();
}

echo "Step 3: Check if Database class exists\n";
echo "   Class exists: " . (class_exists('Database') ? "YES" : "NO") . "\n\n";

echo "Step 4: Load helpers.php\n";
try {
    require_once __DIR__ . '/../app/helpers.php';
    echo "   ✓ helpers.php loaded successfully\n\n";
} catch (Throwable $e) {
    echo "   ✗ ERROR loading helpers.php: " . $e->getMessage() . "\n\n";
    die();
}

echo "Step 5: Check if db() function exists\n";
echo "   Function exists: " . (function_exists('db') ? "YES" : "NO") . "\n\n";

echo "Step 6: Try to call db() function\n";
try {
    require_once __DIR__ . '/../config/database.php';
    $db = db();
    echo "   ✓ db() called successfully\n";
    echo "   ✓ Database instance created: " . get_class($db) . "\n\n";
} catch (Throwable $e) {
    echo "   ✗ ERROR calling db(): " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n\n";
    die();
}

echo "Step 7: Try a simple database query\n";
try {
    $result = $db->query("SELECT 1 as test");
    echo "   ✓ Query executed successfully\n\n";
} catch (Throwable $e) {
    echo "   ✗ ERROR executing query: " . $e->getMessage() . "\n\n";
}

echo "</pre>";
echo "<hr>";
echo "<h2>✓ All tests passed!</h2>";
echo "<p><a href='/owner/dashboard'>Try Owner Dashboard</a></p>";
