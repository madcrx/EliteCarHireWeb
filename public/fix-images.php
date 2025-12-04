<?php
/**
 * Image Loading Fix - Production Deployment Script
 *
 * Run this ONCE from your browser: https://elitecarhire.au/fix-images.php?secret=fixnow2024
 * Then DELETE this file for security.
 */

// Security check - require secret parameter
if (!isset($_GET['secret']) || $_GET['secret'] !== 'fixnow2024') {
    http_response_code(403);
    die('Access denied. This script requires a secret parameter.');
}

// Prevent timeout on slow servers
set_time_limit(300);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Loading Fix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            margin-right: 10px;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
        }
        .button {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
        .button:hover { background: #c82333; }
        ul { margin: 10px 0 10px 20px; }
        li { margin: 5px 0; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Image Loading Fix</h1>
            <p>Elite Car Hire Production Deployment</p>
        </div>
        <div class="content">

<?php
$projectRoot = __DIR__ . '/..';
$publicDir = __DIR__;
$storageDir = $projectRoot . '/storage';
$symlinkPath = $publicDir . '/storage';

$results = [];
$hasErrors = false;

// Step 1: Check if symlink exists and remove if wrong
echo '<div class="step">';
echo '<h3><span class="step-number">1</span>Remove Old Symlink</h3>';

if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        $currentTarget = readlink($symlinkPath);
        echo '<div class="info">Found existing symlink pointing to: <code>' . htmlspecialchars($currentTarget) . '</code></div>';

        if ($currentTarget === '../storage') {
            echo '<div class="success">✅ Symlink already correct! No need to remove.</div>';
            $results['symlink_removed'] = true;
        } else {
            if (unlink($symlinkPath)) {
                echo '<div class="success">✅ Old symlink removed successfully</div>';
                $results['symlink_removed'] = true;
            } else {
                echo '<div class="error">❌ Failed to remove old symlink. Check file permissions.</div>';
                $hasErrors = true;
                $results['symlink_removed'] = false;
            }
        }
    } else {
        echo '<div class="warning">⚠️ A file/directory named "storage" exists but is not a symlink.</div>';
        echo '<div class="info">You need to manually delete/rename: <code>' . $symlinkPath . '</code></div>';
        $hasErrors = true;
        $results['symlink_removed'] = false;
    }
} else {
    echo '<div class="info">No existing symlink found. Ready to create new one.</div>';
    $results['symlink_removed'] = true;
}

echo '</div>';

// Step 2: Create correct symlink
echo '<div class="step">';
echo '<h3><span class="step-number">2</span>Create Correct Symlink</h3>';

if ($results['symlink_removed']) {
    if (file_exists($symlinkPath) && is_link($symlinkPath) && readlink($symlinkPath) === '../storage') {
        echo '<div class="success">✅ Symlink already exists and is correct!</div>';
        $results['symlink_created'] = true;
    } else if (!file_exists($symlinkPath)) {
        if (symlink('../storage', $symlinkPath)) {
            echo '<div class="success">✅ Symlink created successfully!</div>';
            echo '<div class="info">Created: <code>public/storage</code> → <code>../storage</code></div>';
            $results['symlink_created'] = true;
        } else {
            echo '<div class="error">❌ Failed to create symlink. Your server may not allow symlinks.</div>';
            echo '<div class="warning">';
            echo '<strong>Alternative solutions:</strong><br>';
            echo '1. Contact your hosting provider to enable symlink creation<br>';
            echo '2. Or use FTP to copy the storage directory into public/ (not recommended)';
            echo '</div>';
            $hasErrors = true;
            $results['symlink_created'] = false;
        }
    }
} else {
    echo '<div class="warning">⚠️ Skipped - old symlink not removed</div>';
    $results['symlink_created'] = false;
}

echo '</div>';

// Step 3: Create directories
echo '<div class="step">';
echo '<h3><span class="step-number">3</span>Create Image Directories</h3>';

$directories = [
    'storage/uploads/logo' => $storageDir . '/uploads/logo',
    'storage/uploads/site-images' => $storageDir . '/uploads/site-images',
    'storage/vehicles' => $storageDir . '/vehicles'
];

$allCreated = true;
foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        echo '<div class="success">✅ Directory exists: <code>' . htmlspecialchars($name) . '</code></div>';
    } else {
        if (mkdir($path, 0755, true)) {
            echo '<div class="success">✅ Created: <code>' . htmlspecialchars($name) . '</code></div>';
        } else {
            echo '<div class="error">❌ Failed to create: <code>' . htmlspecialchars($name) . '</code></div>';
            $hasErrors = true;
            $allCreated = false;
        }
    }
}

$results['directories_created'] = $allCreated;
echo '</div>';

// Step 4: Set permissions
echo '<div class="step">';
echo '<h3><span class="step-number">4</span>Set Directory Permissions</h3>';

$allPermissionsSet = true;
foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        if (chmod($path, 0755)) {
            echo '<div class="success">✅ Permissions set (755): <code>' . htmlspecialchars($name) . '</code></div>';
        } else {
            echo '<div class="warning">⚠️ Could not set permissions for: <code>' . htmlspecialchars($name) . '</code></div>';
            $allPermissionsSet = false;
        }
    }
}

$results['permissions_set'] = $allPermissionsSet;
echo '</div>';

// Step 5: Verify symlink access
echo '<div class="step">';
echo '<h3><span class="step-number">5</span>Verify Symlink Access</h3>';

if ($results['symlink_created']) {
    $testPaths = [
        '/storage/uploads/logo' => $publicDir . '/storage/uploads/logo',
        '/storage/uploads/site-images' => $publicDir . '/storage/uploads/site-images',
        '/storage/vehicles' => $publicDir . '/storage/vehicles'
    ];

    $allAccessible = true;
    foreach ($testPaths as $url => $fsPath) {
        if (is_dir($fsPath)) {
            echo '<div class="success">✅ Accessible: <code>' . htmlspecialchars($url) . '</code></div>';
        } else {
            echo '<div class="error">❌ Not accessible: <code>' . htmlspecialchars($url) . '</code></div>';
            $allAccessible = false;
        }
    }

    $results['symlink_accessible'] = $allAccessible;
} else {
    echo '<div class="warning">⚠️ Skipped - symlink not created</div>';
    $results['symlink_accessible'] = false;
}

echo '</div>';

// Final status
echo '<div class="step">';
if (!$hasErrors && $results['symlink_created'] && $results['directories_created'] && $results['symlink_accessible']) {
    echo '<h3 style="color: #28a745;">🎉 SUCCESS! Image Loading Fix Complete</h3>';
    echo '<div class="success">';
    echo '<strong>All steps completed successfully!</strong><br><br>';
    echo '<strong>Next steps:</strong><br>';
    echo '1. Visit <a href="/" target="_blank">your homepage</a> and clear cache (Ctrl+Shift+R)<br>';
    echo '2. Logo and vehicle images should now display correctly<br>';
    echo '3. <strong>DELETE THIS FILE immediately for security:</strong> <code>public/fix-images.php</code>';
    echo '</div>';

    echo '<div class="info" style="margin-top: 20px;">';
    echo '<strong>Test URLs (should show 403 Forbidden or image files):</strong><br>';
    echo '• <a href="/storage/uploads/logo/" target="_blank">/storage/uploads/logo/</a><br>';
    echo '• <a href="/storage/uploads/site-images/" target="_blank">/storage/uploads/site-images/</a><br>';
    echo '• <a href="/storage/vehicles/" target="_blank">/storage/vehicles/</a>';
    echo '</div>';

    echo '<div class="warning" style="margin-top: 20px;">';
    echo '<strong>⚠️ IMPORTANT:</strong> Delete this file now! You can delete it via:<br>';
    echo '• cPanel File Manager: Navigate to public/ and delete <code>fix-images.php</code><br>';
    echo '• FTP: Delete <code>/public/fix-images.php</code>';
    echo '</div>';
} else {
    echo '<h3 style="color: #dc3545;">⚠️ Issues Detected</h3>';
    echo '<div class="error">';
    echo '<strong>Some steps failed. Review the errors above.</strong><br><br>';
    echo '<strong>Common solutions:</strong><br>';
    echo '1. Check file/directory permissions via cPanel File Manager<br>';
    echo '2. Contact hosting support if symlinks are not allowed<br>';
    echo '3. Ensure the storage/ directory exists in your project root';
    echo '</div>';

    echo '<div class="info" style="margin-top: 20px;">';
    echo '<strong>Manual alternative (if symlinks disabled):</strong><br><br>';
    echo 'Via cPanel File Manager:<br>';
    echo '1. Go to <code>/home/cp825575/EliteCarHireWeb/storage/</code><br>';
    echo '2. Select the <code>storage</code> folder<br>';
    echo '3. Copy it<br>';
    echo '4. Navigate to <code>/home/cp825575/EliteCarHireWeb/public/</code><br>';
    echo '5. Paste the storage folder here<br><br>';
    echo '<strong>Note:</strong> With this method, you\'ll need to re-copy after uploading new images.';
    echo '</div>';
}
echo '</div>';

// Debug information
echo '<div class="step">';
echo '<h3><span class="step-number">ℹ️</span>System Information</h3>';
echo '<div class="code-block">';
echo 'PHP Version: ' . PHP_VERSION . '<br>';
echo 'Project Root: ' . htmlspecialchars($projectRoot) . '<br>';
echo 'Public Directory: ' . htmlspecialchars($publicDir) . '<br>';
echo 'Storage Directory: ' . htmlspecialchars($storageDir) . '<br>';
echo 'Storage Exists: ' . (is_dir($storageDir) ? 'Yes' : 'No') . '<br>';
echo 'Symlink Functions Available: ' . (function_exists('symlink') ? 'Yes' : 'No');
echo '</div>';
echo '</div>';

?>

        </div>
    </div>
</body>
</html>
