<?php
// Storage structure check
// Access: https://elitecarhire.au/check-storage.php

header('Content-Type: text/plain');

echo "=== STORAGE STRUCTURE CHECK ===\n\n";

$publicDir = __DIR__;
$rootDir = dirname($publicDir);

echo "1. Directory structure:\n";
echo "   Public dir: $publicDir\n";
echo "   Root dir: $rootDir\n\n";

echo "2. Checking for storage at ROOT level:\n";
$rootStorage = $rootDir . '/storage';
echo "   Path: $rootStorage\n";
if (is_dir($rootStorage)) {
    echo "   ✓ EXISTS\n";

    // List contents
    echo "   Contents:\n";
    $items = scandir($rootStorage);
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            $path = $rootStorage . '/' . $item;
            $type = is_dir($path) ? 'DIR' : 'FILE';
            echo "      - $item ($type)\n";
        }
    }
} else {
    echo "   ✗ DOES NOT EXIST\n";
}

echo "\n3. Checking for symlink in public/:\n";
$publicStorage = $publicDir . '/storage';
echo "   Path: $publicStorage\n";

if (file_exists($publicStorage)) {
    if (is_link($publicStorage)) {
        echo "   ✓ SYMLINK EXISTS\n";
        echo "   Points to: " . readlink($publicStorage) . "\n";
        $real = realpath($publicStorage);
        echo "   Resolves to: $real\n";

        if ($real === $rootStorage) {
            echo "   ✓ CORRECTLY POINTS TO ROOT STORAGE\n";
        } else {
            echo "   ✗ INCORRECT TARGET\n";
        }
    } else {
        echo "   ✗ EXISTS BUT NOT A SYMLINK\n";
        echo "   Type: " . (is_dir($publicStorage) ? 'DIRECTORY' : 'FILE') . "\n";
    }
} else {
    echo "   ✗ DOES NOT EXIST\n";
}

echo "\n4. Checking image files in ROOT storage:\n";
if (is_dir($rootStorage)) {
    $logoDir = $rootStorage . '/uploads/logo';
    $vehiclesDir = $rootStorage . '/vehicles';

    echo "   Logo directory: $logoDir\n";
    if (is_dir($logoDir)) {
        $files = array_diff(scandir($logoDir), ['.', '..']);
        echo "   ✓ EXISTS (" . count($files) . " files)\n";
        foreach (array_slice($files, 0, 3) as $file) {
            echo "      - $file\n";
        }
    } else {
        echo "   ✗ DOES NOT EXIST\n";
    }

    echo "\n   Vehicles directory: $vehiclesDir\n";
    if (is_dir($vehiclesDir)) {
        $files = array_diff(scandir($vehiclesDir), ['.', '..']);
        echo "   ✓ EXISTS (" . count($files) . " files)\n";
        foreach (array_slice($files, 0, 3) as $file) {
            echo "      - $file\n";
        }
    } else {
        echo "   ✗ DOES NOT EXIST\n";
    }
}

echo "\n5. RECOMMENDATION:\n";
if (!is_dir($rootStorage)) {
    echo "   CREATE: mkdir -p $rootStorage/uploads/logo $rootStorage/vehicles\n";
}
if (!is_link($publicStorage)) {
    if (file_exists($publicStorage)) {
        echo "   DELETE: rm -rf $publicStorage\n";
    }
    echo "   CREATE SYMLINK: ln -s ../storage $publicStorage\n";
}

echo "\n=== END ===\n";
?>
