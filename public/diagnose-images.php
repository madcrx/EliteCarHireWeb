<?php
/**
 * Image Diagnostics Tool
 * Checks why images aren't showing
 *
 * ⚠️ DELETE THIS FILE AFTER USE - Contains sensitive info
 */

// Prevent unauthorized access (basic security)
$secret = $_GET['secret'] ?? '';
if ($secret !== 'diagnose123') {
    die('Access denied. Add ?secret=diagnose123 to URL');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Diagnostics</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .result { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .pass { color: #28a745; font-weight: bold; }
        .fail { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; }
        code { background: #f4f4f4; padding: 2px 5px; }
        pre { background: #f4f4f4; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Image Diagnostics Report</h1>

    <?php
    // Test 1: Check if symlink exists
    echo '<div class="result">';
    echo '<h2>1. Symlink Check</h2>';

    $publicStorageLink = __DIR__ . '/storage';

    if (file_exists($publicStorageLink)) {
        if (is_link($publicStorageLink)) {
            $target = readlink($publicStorageLink);
            echo '<span class="pass">✅ PASS:</span> Symlink exists<br>';
            echo 'Link: <code>' . $publicStorageLink . '</code><br>';
            echo 'Points to: <code>' . $target . '</code><br>';

            // Check if target exists
            if (file_exists($publicStorageLink . '/site-images')) {
                echo '<span class="pass">✅ PASS:</span> Target directory accessible<br>';
            } else {
                echo '<span class="fail">❌ FAIL:</span> Target directory not found at <code>' . $target . '</code><br>';
            }
        } else {
            echo '<span class="fail">❌ FAIL:</span> "storage" exists but is NOT a symlink (it\'s a real directory/file)<br>';
            echo 'This will prevent images from loading. Delete it and recreate as symlink.<br>';
        }
    } else {
        echo '<span class="fail">❌ FAIL:</span> Symlink does not exist at <code>' . $publicStorageLink . '</code><br>';
        echo 'Run create-storage-link.php to create it.<br>';
    }
    echo '</div>';

    // Test 2: Check actual image directories
    echo '<div class="result">';
    echo '<h2>2. Image Directory Check</h2>';

    $storageBase = __DIR__ . '/../storage';
    $siteImagesDir = $storageBase . '/uploads/site-images';
    $vehiclesDir = $storageBase . '/vehicles';

    // Check site-images
    if (is_dir($siteImagesDir)) {
        $files = scandir($siteImagesDir);
        $imageFiles = array_filter($files, function($f) use ($siteImagesDir) {
            return is_file($siteImagesDir . '/' . $f) && preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $f);
        });

        if (count($imageFiles) > 0) {
            echo '<span class="pass">✅ PASS:</span> Site images directory exists with ' . count($imageFiles) . ' image(s)<br>';
            echo 'Location: <code>' . $siteImagesDir . '</code><br>';
            echo 'Images found:<br><pre>' . implode("\n", array_slice($imageFiles, 0, 10)) . '</pre>';
        } else {
            echo '<span class="warning">⚠️ WARNING:</span> Site images directory exists but is empty<br>';
            echo 'Upload logo images via Admin → Images<br>';
        }
    } else {
        echo '<span class="fail">❌ FAIL:</span> Site images directory does not exist<br>';
        echo 'Expected at: <code>' . $siteImagesDir . '</code><br>';
    }

    // Check vehicles
    if (is_dir($vehiclesDir)) {
        $files = scandir($vehiclesDir);
        $imageFiles = array_filter($files, function($f) use ($vehiclesDir) {
            return is_file($vehiclesDir . '/' . $f) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f);
        });

        if (count($imageFiles) > 0) {
            echo '<span class="pass">✅ PASS:</span> Vehicles directory exists with ' . count($imageFiles) . ' image(s)<br>';
            echo 'Location: <code>' . $vehiclesDir . '</code><br>';
        } else {
            echo '<span class="warning">⚠️ WARNING:</span> Vehicles directory exists but is empty<br>';
        }
    } else {
        echo '<span class="warning">⚠️ WARNING:</span> Vehicles directory does not exist<br>';
        echo 'Expected at: <code>' . $vehiclesDir . '</code><br>';
    }
    echo '</div>';

    // Test 3: Check database image paths
    echo '<div class="result">';
    echo '<h2>3. Database Check</h2>';

    require_once __DIR__ . '/../config/database.php';
    $dbConfig = require __DIR__ . '/../config/database.php';

    try {
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
            $dbConfig['username'],
            $dbConfig['password']
        );

        echo '<span class="pass">✅ PASS:</span> Database connection successful<br><br>';

        // Check site_images table
        try {
            $stmt = $pdo->query("SELECT * FROM site_images WHERE image_type = 'logo' LIMIT 5");
            $logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($logos) > 0) {
                echo '<strong>Logo records in database:</strong><br>';
                foreach ($logos as $logo) {
                    $path = $logo['image_path'] ?? 'N/A';
                    $fullPath = __DIR__ . '/..' . $path;
                    $exists = file_exists($fullPath);

                    echo 'ID: ' . $logo['id'] . ' | ';
                    echo 'Key: <code>' . ($logo['image_key'] ?? 'N/A') . '</code> | ';
                    echo 'Path: <code>' . $path . '</code> | ';
                    echo 'File exists: ' . ($exists ? '<span class="pass">✅ YES</span>' : '<span class="fail">❌ NO</span>');
                    echo '<br>';
                }

                // Check active logo setting
                $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'active_logo_id'");
                $activeLogo = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($activeLogo && !empty($activeLogo['setting_value'])) {
                    echo '<br>Active logo ID: <code>' . $activeLogo['setting_value'] . '</code><br>';
                } else {
                    echo '<br><span class="warning">⚠️ WARNING:</span> No active logo set in settings<br>';
                    echo 'Set via Admin → Images or add setting: <code>active_logo_id</code><br>';
                }
            } else {
                echo '<span class="warning">⚠️ WARNING:</span> No logo records found in site_images table<br>';
                echo 'Upload logo via Admin → Images<br>';
            }
        } catch (PDOException $e) {
            echo '<span class="fail">❌ FAIL:</span> site_images table does not exist<br>';
            echo 'Error: ' . $e->getMessage() . '<br>';
            echo 'Run Phase 2 database migration to create this table<br>';
        }

        echo '<br>';

        // Check vehicle_images table
        try {
            $stmt = $pdo->query("SELECT image_path FROM vehicle_images LIMIT 10");
            $vehicleImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($vehicleImages) > 0) {
                echo '<strong>Vehicle image paths (first 10):</strong><br><pre>';
                foreach ($vehicleImages as $img) {
                    $path = $img['image_path'];
                    $fullPath = __DIR__ . '/..' . $path;
                    $exists = file_exists($fullPath);
                    echo $path . ' - ' . ($exists ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
                }
                echo '</pre>';
            } else {
                echo '<span class="warning">⚠️ INFO:</span> No vehicle images in database yet<br>';
            }
        } catch (PDOException $e) {
            echo '<span class="warning">⚠️ WARNING:</span> Could not check vehicle_images table<br>';
        }

    } catch (PDOException $e) {
        echo '<span class="fail">❌ FAIL:</span> Database connection failed<br>';
        echo 'Error: ' . $e->getMessage() . '<br>';
    }
    echo '</div>';

    // Test 4: Test direct URL access
    echo '<div class="result">';
    echo '<h2>4. URL Access Test</h2>';

    echo '<p>Test these URLs directly in your browser:</p>';

    $testUrls = [
        '/storage/uploads/site-images/' => 'Site images directory',
        '/storage/vehicles/' => 'Vehicles directory',
        '/assets/css/style.css' => 'CSS file (should work)',
    ];

    foreach ($testUrls as $url => $desc) {
        $fullUrl = 'https://' . $_SERVER['HTTP_HOST'] . $url;
        echo '<a href="' . $url . '" target="_blank">' . $fullUrl . '</a> - ' . $desc . '<br>';
    }

    echo '<br><strong>Expected results:</strong><br>';
    echo '• If you see directory listing or images → <span class="pass">✅ Working</span><br>';
    echo '• If you see 403 Forbidden → Might be OK (directory browsing disabled)<br>';
    echo '• If you see 404 Not Found → <span class="fail">❌ Not working - symlink or document root issue</span><br>';
    echo '</div>';

    // Test 5: Document root check
    echo '<div class="result">';
    echo '<h2>5. Document Root Check</h2>';

    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $currentScript = __DIR__;

    echo 'Document Root: <code>' . $docRoot . '</code><br>';
    echo 'This script location: <code>' . $currentScript . '</code><br><br>';

    if (strpos($currentScript, '/public') !== false) {
        echo '<span class="pass">✅ PASS:</span> Document root appears to be pointing to /public/ directory<br>';
    } else {
        echo '<span class="warning">⚠️ WARNING:</span> Document root may not be pointing to /public/ directory<br>';
        echo 'This could cause issues with asset loading.<br>';
        echo 'See DOCUMENT_ROOT_FIX.md for solutions.<br>';
    }
    echo '</div>';

    // Test 6: File permissions
    echo '<div class="result">';
    echo '<h2>6. File Permissions Check</h2>';

    $checkPaths = [
        __DIR__ . '/storage' => 'Symlink',
        __DIR__ . '/../storage/uploads' => 'Storage uploads dir',
        __DIR__ . '/../storage/uploads/site-images' => 'Site images dir',
    ];

    foreach ($checkPaths as $path => $desc) {
        if (file_exists($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $readable = is_readable($path);
            echo $desc . ': <code>' . $path . '</code><br>';
            echo 'Permissions: <code>' . $perms . '</code> | ';
            echo 'Readable: ' . ($readable ? '<span class="pass">✅ YES</span>' : '<span class="fail">❌ NO</span>');
            echo '<br><br>';
        } else {
            echo $desc . ': <span class="fail">❌ NOT FOUND</span><br><br>';
        }
    }
    echo '</div>';

    // Summary and recommendations
    echo '<div class="result">';
    echo '<h2>📋 Summary & Next Steps</h2>';
    echo '<ol>';
    echo '<li>Check all test results above for ❌ FAIL markers</li>';
    echo '<li>Test the URLs in section 4 directly in your browser</li>';
    echo '<li>If URLs give 404, symlink is not working - recreate it via SSH</li>';
    echo '<li>If no images in directories, upload them via cPanel or FTP</li>';
    echo '<li>If site_images table missing, run Phase 2 database migration</li>';
    echo '<li><strong>DELETE THIS FILE</strong> after reviewing results (security risk)</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <div class="result" style="background: #fff3cd; border: 2px solid #ffc107;">
        <h2>⚠️ SECURITY WARNING</h2>
        <p><strong>DELETE THIS FILE IMMEDIATELY after reviewing!</strong></p>
        <p>This file exposes sensitive server information.</p>
        <form method="get" style="display: inline;">
            <input type="hidden" name="secret" value="diagnose123">
            <input type="hidden" name="delete" value="yes">
            <button type="submit" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
                🗑️ Delete This File Now
            </button>
        </form>
    </div>

    <?php
    // Self-delete
    if (isset($_GET['delete']) && $_GET['delete'] === 'yes') {
        if (@unlink(__FILE__)) {
            echo '<div class="result" style="background: #d4edda;">';
            echo '<h2>✅ File Deleted</h2>';
            echo '<p>Redirecting to homepage...</p>';
            echo '<script>setTimeout(function(){ window.location.href = "/"; }, 2000);</script>';
            echo '</div>';
        } else {
            echo '<div class="result" style="background: #f8d7da;">';
            echo '<h2>❌ Could Not Delete</h2>';
            echo '<p>Please delete manually: <code>' . __FILE__ . '</code></p>';
            echo '</div>';
        }
    }
    ?>
</body>
</html>
