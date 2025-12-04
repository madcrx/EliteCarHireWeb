<?php
// Simple symlink test - Delete after use
// Access: https://elitecarhire.au/test-symlink.php

header('Content-Type: text/plain');

echo "=== SYMLINK TEST ===\n\n";

$symlinkPath = __DIR__ . '/public/storage';
$targetPath = __DIR__ . '/storage';

echo "1. Checking symlink:\n";
echo "   Path: $symlinkPath\n";

if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        echo "   ✓ Symlink EXISTS\n";
        echo "   Target: " . readlink($symlinkPath) . "\n";
        echo "   Real path: " . realpath($symlinkPath) . "\n";
    } else {
        echo "   ✗ EXISTS but NOT a symlink (it's a " . (is_dir($symlinkPath) ? 'directory' : 'file') . ")\n";
    }
} else {
    echo "   ✗ Symlink DOES NOT EXIST\n";
}

echo "\n2. Checking if storage directory exists:\n";
echo "   Path: $targetPath\n";
if (is_dir($targetPath)) {
    echo "   ✓ Storage directory EXISTS\n";
} else {
    echo "   ✗ Storage directory DOES NOT EXIST\n";
}

echo "\n3. Testing specific image files:\n";

$testFiles = [
    '/storage/uploads/logo/logo-1764375936-692a3d807e627.png',
    '/storage/vehicles/6924f0ce86144_1764028622.jpg'
];

foreach ($testFiles as $file) {
    $fsPath = __DIR__ . $file;
    echo "   File: $file\n";
    echo "   Full path: $fsPath\n";

    if (file_exists($fsPath)) {
        echo "   ✓ EXISTS (" . number_format(filesize($fsPath)) . " bytes)\n";
    } else {
        echo "   ✗ DOES NOT EXIST\n";
    }
    echo "\n";
}

echo "\n4. Current directory: " . __DIR__ . "\n";
echo "5. Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

echo "\n=== END TEST ===\n";
echo "\nDELETE THIS FILE AFTER VIEWING!\n";
?>
