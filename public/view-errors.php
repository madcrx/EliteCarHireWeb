<?php
// Show latest error log entries
$logFile = __DIR__ . '/../storage/logs/error.log';

echo "<h1>Latest Error Log Entries</h1>";

if (!file_exists($logFile)) {
    echo "<p style='color: red;'>Error log file not found at: " . htmlspecialchars($logFile) . "</p>";
    echo "<p>Check your hosting control panel for PHP error logs.</p>";
    exit;
}

echo "<p>Log file: <code>" . htmlspecialchars($logFile) . "</code></p>";
echo "<p>File size: " . number_format(filesize($logFile)) . " bytes</p>";

// Get last 50 lines
$lines = file($logFile);
if ($lines === false) {
    echo "<p style='color: red;'>Could not read error log file.</p>";
    exit;
}

$lastLines = array_slice($lines, -50);

echo "<h2>Last 50 Entries:</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow-x: auto;'>";
echo htmlspecialchars(implode('', $lastLines));
echo "</pre>";

echo "<hr>";
echo "<p><a href='/owner/dashboard'>Try Owner Dashboard</a> | <a href='/database-test.php'>Database Test</a></p>";
