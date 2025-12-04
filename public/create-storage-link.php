<?php
/**
 * Storage Symlink Creator
 *
 * This script creates a symbolic link from public/storage to ../storage/uploads
 * so that images in /storage/uploads/ are accessible via /storage/ URLs
 *
 * ⚠️ SECURITY: DELETE THIS FILE IMMEDIATELY AFTER RUNNING
 */

// Prevent running if symlink already exists
$link = __DIR__ . '/storage';
$target = __DIR__ . '/../storage';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Storage Symlink Creator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-danger {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔗 Storage Symlink Creator</h1>
        <p>This script creates a symbolic link so your uploaded images are accessible.</p>

        <?php
        // Check if symlink already exists
        if (file_exists($link)) {
            if (is_link($link)) {
                echo '<div class="success">';
                echo '✅ <strong>Symlink already exists!</strong><br>';
                echo 'Link: <code>' . $link . '</code><br>';
                echo 'Target: <code>' . readlink($link) . '</code>';
                echo '</div>';

                echo '<div class="info">';
                echo '<strong>Test it:</strong><br>';
                echo '1. Visit: <a href="/storage/uploads/site-images/" target="_blank">Site Images Directory</a><br>';
                echo '2. Visit: <a href="/storage/vehicles/" target="_blank">Vehicle Images Directory</a>';
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '❌ <strong>Problem:</strong> A file/directory named "storage" already exists but it\'s not a symlink.<br>';
                echo 'Please delete or rename <code>' . $link . '</code> first.';
                echo '</div>';
            }
        } else {
            // Try to create symlink
            if (symlink($target, $link)) {
                echo '<div class="success">';
                echo '✅ <strong>Success! Storage symlink created!</strong><br><br>';
                echo 'Link created: <code>' . $link . '</code><br>';
                echo 'Points to: <code>' . $target . '</code>';
                echo '</div>';

                echo '<div class="info">';
                echo '<strong>Test it now:</strong><br>';
                echo '1. Visit: <a href="/storage/uploads/site-images/" target="_blank">Site Images Directory</a><br>';
                echo '2. Visit: <a href="/storage/vehicles/" target="_blank">Vehicle Images Directory</a><br>';
                echo '3. Check your homepage - logo should now appear!';
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '❌ <strong>Failed to create symlink</strong><br><br>';
                echo 'Your hosting provider may not support symbolic links.<br><br>';
                echo '<strong>Alternative solutions:</strong><br>';
                echo '1. Contact your hosting support to enable symlinks<br>';
                echo '2. Or copy the storage directory instead (see documentation)<br>';
                echo '3. Or use the PHP serve-storage script';
                echo '</div>';
            }
        }
        ?>

        <div class="warning">
            ⚠️ <strong>SECURITY WARNING:</strong> DELETE THIS FILE NOW!<br>
            This file should not remain on your live server.
        </div>

        <div style="margin-top: 30px;">
            <a href="/" class="btn">← Back to Website</a>
            <a href="?delete=confirm" class="btn btn-danger">🗑️ Delete This File</a>
        </div>

        <?php
        // Self-delete functionality
        if (isset($_GET['delete']) && $_GET['delete'] === 'confirm') {
            if (unlink(__FILE__)) {
                echo '<div class="success" style="margin-top: 20px;">';
                echo '✅ File deleted successfully! Redirecting to homepage...';
                echo '<script>setTimeout(function(){ window.location.href = "/"; }, 2000);</script>';
                echo '</div>';
            } else {
                echo '<div class="error" style="margin-top: 20px;">';
                echo '❌ Could not delete file. Please delete manually via FTP or File Manager.';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
