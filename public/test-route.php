<?php
// Simple route testing diagnostic
header('Content-Type: text/plain');

echo "=== ROUTE DIAGNOSTIC TEST ===\n\n";

echo "PHP is working: YES\n\n";

echo "SERVER VARIABLES:\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'not set') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n\n";

echo "PARSED PATH:\n";
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
echo "Parsed path: '$path'\n";
echo "After rtrim: '" . rtrim($path, '/') . "'\n\n";

echo "FILE CHECKS:\n";
echo "index.php exists: " . (file_exists(__DIR__ . '/index.php') ? 'YES' : 'NO') . "\n";
echo "Router.php exists: " . (file_exists(__DIR__ . '/../app/Router.php') ? 'YES' : 'NO') . "\n";
echo "AuthController exists: " . (file_exists(__DIR__ . '/../app/controllers/AuthController.php') ? 'YES' : 'NO') . "\n\n";

echo ".htaccess CHECK:\n";
echo ".htaccess exists: " . (file_exists(__DIR__ . '/.htaccess') ? 'YES' : 'NO') . "\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo ".htaccess readable: " . (is_readable(__DIR__ . '/.htaccess') ? 'YES' : 'NO') . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
