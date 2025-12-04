<?php
/**
 * Image Diagnostics - Check what's actually happening
 * Run this from browser: https://elitecarhire.au/check-images.php?secret=check123
 */

if (!isset($_GET['secret']) || $_GET['secret'] !== 'check123') {
    http_response_code(403);
    die('Access denied.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Diagnostics</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #1a1a2e;
            color: #eee;
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #00d9ff; margin-bottom: 20px; }
        h2 { color: #00d9ff; margin: 30px 0 15px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .test {
            background: #16213e;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #0f3460;
        }
        .success { border-left-color: #28a745; background: #1a3a1a; }
        .error { border-left-color: #dc3545; background: #3a1a1a; }
        .warning { border-left-color: #ffc107; background: #3a3a1a; }
        code {
            background: #000;
            padding: 2px 8px;
            border-radius: 4px;
            color: #0f0;
            font-family: 'Courier New', monospace;
        }
        .code-block {
            background: #000;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            color: #0f0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: #16213e;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        th { background: #0f3460; color: #00d9ff; }
        .icon { margin-right: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Image Loading Diagnostics</h1>

<?php
$publicDir = __DIR__;
$projectRoot = dirname($publicDir);
$symlinkPath = $publicDir . '/storage';

echo '<h2>1. Symlink Check</h2>';

if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        $target = readlink($symlinkPath);
        echo '<div class="test success">';
        echo '<span class="icon">✅</span><strong>Symlink exists</strong><br>';
        echo 'Location: <code>' . $symlinkPath . '</code><br>';
        echo 'Points to: <code>' . $target . '</code><br>';

        // Check if target is accessible
        $realTarget = realpath($symlinkPath);
        if ($realTarget) {
            echo 'Real path: <code>' . $realTarget . '</code><br>';
            echo '<span class="icon">✅</span>Symlink is valid and accessible';
        } else {
            echo '<span class="icon">❌</span>Symlink exists but target is not accessible';
        }
        echo '</div>';
    } else {
        echo '<div class="test error">';
        echo '<span class="icon">❌</span><strong>Problem:</strong> A file/directory named "storage" exists but it\'s NOT a symlink<br>';
        echo 'Type: ' . (is_dir($symlinkPath) ? 'Directory' : 'File');
        echo '</div>';
    }
} else {
    echo '<div class="test error">';
    echo '<span class="icon">❌</span><strong>Symlink does not exist</strong><br>';
    echo 'Expected location: <code>' . $symlinkPath . '</code>';
    echo '</div>';
}

echo '<h2>2. Directory Structure Check</h2>';

$directories = [
    'storage/uploads/logo' => $projectRoot . '/storage/uploads/logo',
    'storage/uploads/site-images' => $projectRoot . '/storage/uploads/site-images',
    'storage/vehicles' => $projectRoot . '/storage/vehicles'
];

echo '<table>';
echo '<tr><th>Directory</th><th>Exists?</th><th>Permissions</th><th>Accessible via Symlink?</th></tr>';

foreach ($directories as $name => $path) {
    echo '<tr>';
    echo '<td><code>' . $name . '</code></td>';

    // Check if directory exists
    if (is_dir($path)) {
        echo '<td style="color: #28a745;">✅ Yes</td>';

        // Get permissions
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo '<td><code>' . $perms . '</code></td>';

        // Check if accessible via symlink
        $symlinkAccess = $publicDir . '/storage/' . str_replace('storage/', '', $name);
        if (is_dir($symlinkAccess)) {
            echo '<td style="color: #28a745;">✅ Yes</td>';
        } else {
            echo '<td style="color: #dc3545;">❌ No</td>';
        }
    } else {
        echo '<td style="color: #dc3545;">❌ No</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
    }

    echo '</tr>';
}

echo '</table>';

echo '<h2>3. Image Files Check</h2>';

// Check the specific files the user mentioned
$testFiles = [
    '/storage/uploads/logo/logo-1764375936-692a3d807e627.png',
    '/storage/vehicles/6924f0ce86144_1764028622.jpg'
];

echo '<table>';
echo '<tr><th>Image Path (from your HTML)</th><th>Exists?</th><th>File Size</th><th>Readable?</th></tr>';

foreach ($testFiles as $file) {
    echo '<tr>';
    echo '<td><code>' . htmlspecialchars($file) . '</code></td>';

    // Remove leading slash and check via project root
    $fsPath = $projectRoot . $file;

    if (file_exists($fsPath)) {
        echo '<td style="color: #28a745;">✅ Yes</td>';
        echo '<td>' . number_format(filesize($fsPath)) . ' bytes</td>';
        echo '<td style="color: ' . (is_readable($fsPath) ? '#28a745' : '#dc3545') . ';">';
        echo (is_readable($fsPath) ? '✅ Yes' : '❌ No');
        echo '</td>';
    } else {
        echo '<td style="color: #dc3545;">❌ No</td>';
        echo '<td>-</td>';
        echo '<td>-</td>';
    }

    echo '</tr>';
}

echo '</table>';

// Scan for actual files in those directories
echo '<h2>4. Actual Files in Directories</h2>';

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        echo '<div class="test">';
        echo '<strong>' . $name . '</strong><br>';

        if (count($files) > 0) {
            echo 'Files found: ' . count($files) . '<br>';
            echo '<div class="code-block">';
            foreach (array_slice($files, 0, 10) as $file) {
                $filePath = $path . '/' . $file;
                $size = is_file($filePath) ? filesize($filePath) : 0;
                $perms = substr(sprintf('%o', fileperms($filePath)), -4);
                echo $file . ' (' . number_format($size) . ' bytes, perms: ' . $perms . ')<br>';
            }
            if (count($files) > 10) {
                echo '... and ' . (count($files) - 10) . ' more files';
            }
            echo '</div>';
        } else {
            echo '<span style="color: #ffc107;">⚠️ Directory is empty</span>';
        }

        echo '</div>';
    }
}

echo '<h2>5. Web Access Test</h2>';

echo '<div class="test">';
echo '<strong>Test these URLs in a new browser tab:</strong><br><br>';

foreach ($testFiles as $file) {
    $url = 'https://elitecarhire.au' . $file;
    echo '<a href="' . $url . '" target="_blank" style="color: #00d9ff;">' . $url . '</a><br>';
}

echo '<br><strong>Expected result:</strong> Image should load OR you should see a different error<br>';
echo '<strong>If 404 Not Found:</strong> Symlink not working or .htaccess not being read<br>';
echo '<strong>If 403 Forbidden:</strong> Permission issue<br>';
echo '<strong>If image loads:</strong> Problem is elsewhere (caching, HTML, etc.)';
echo '</div>';

echo '<h2>6. .htaccess Check</h2>';

$htaccessPath = $publicDir . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo '<div class="test success">';
    echo '<span class="icon">✅</span>.htaccess file exists<br>';

    $htaccess = file_get_contents($htaccessPath);

    // Check for our storage rule
    if (strpos($htaccess, 'RewriteCond %{REQUEST_URI} ^/storage/') !== false) {
        echo '<span class="icon">✅</span>Storage rewrite rule found<br>';
    } else {
        echo '<span class="icon">❌</span>Storage rewrite rule NOT found<br>';
    }

    // Check for FollowSymLinks
    if (strpos($htaccess, '+FollowSymLinks') !== false || strpos($htaccess, 'FollowSymLinks') !== false) {
        echo '<span class="icon">✅</span>FollowSymLinks enabled<br>';
    } else {
        echo '<span class="icon">❌</span>FollowSymLinks NOT enabled<br>';
    }

    echo '</div>';

    // Show the relevant parts
    echo '<div class="test">';
    echo '<strong>Relevant .htaccess content:</strong>';
    echo '<div class="code-block">';
    $lines = explode("\n", $htaccess);
    $inRelevantSection = false;
    foreach ($lines as $line) {
        if (stripos($line, 'storage') !== false || stripos($line, 'FollowSymLinks') !== false || stripos($line, 'RewriteEngine') !== false) {
            echo htmlspecialchars($line) . '<br>';
            $inRelevantSection = true;
        } elseif ($inRelevantSection && trim($line) !== '') {
            echo htmlspecialchars($line) . '<br>';
        } elseif (trim($line) === '') {
            $inRelevantSection = false;
        }
    }
    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="test error">';
    echo '<span class="icon">❌</span>.htaccess file NOT found at: <code>' . $htaccessPath . '</code>';
    echo '</div>';
}

echo '<h2>7. PHP Configuration</h2>';

echo '<div class="test">';
echo '<div class="code-block">';
echo 'PHP Version: ' . PHP_VERSION . '<br>';
echo 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . '<br>';
echo 'Script Filename: ' . $_SERVER['SCRIPT_FILENAME'] . '<br>';
echo 'Symlink function available: ' . (function_exists('symlink') ? 'Yes' : 'No') . '<br>';
echo 'open_basedir: ' . (ini_get('open_basedir') ?: 'Not set') . '<br>';
echo '</div>';
echo '</div>';

echo '<h2>8. Recommended Next Steps</h2>';

$issues = [];

// Determine issues
if (!file_exists($symlinkPath) || !is_link($symlinkPath)) {
    $issues[] = 'Symlink missing or invalid';
}

$emptyDirs = [];
foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        if (count($files) == 0) {
            $emptyDirs[] = $name;
        }
    }
}

if (count($emptyDirs) > 0) {
    $issues[] = 'Empty directories: ' . implode(', ', $emptyDirs);
}

if (count($issues) > 0) {
    echo '<div class="test warning">';
    echo '<strong>Issues found:</strong><br>';
    foreach ($issues as $issue) {
        echo '<span class="icon">⚠️</span>' . $issue . '<br>';
    }
    echo '</div>';

    echo '<div class="test">';
    echo '<strong>Solutions:</strong><br><br>';

    if (in_array('Symlink missing or invalid', $issues)) {
        echo '1. Run the fix script again: <a href="/fix-images.php?secret=fixnow2024" style="color: #00d9ff;">/fix-images.php?secret=fixnow2024</a><br><br>';
    }

    if (count($emptyDirs) > 0) {
        echo '2. Your image files might be in a different location. Check via cPanel File Manager:<br>';
        echo '&nbsp;&nbsp;&nbsp;- Look for files matching: logo-1764375936-*.png<br>';
        echo '&nbsp;&nbsp;&nbsp;- Look for files matching: 6924f0ce86144_*.jpg<br>';
        echo '&nbsp;&nbsp;&nbsp;- Move them to the correct directories listed above<br><br>';
    }

    echo '3. Or manually create symlink via cPanel File Manager<br>';
    echo '4. Contact hosting support if symlinks are disabled';
    echo '</div>';
} else {
    echo '<div class="test success">';
    echo '<span class="icon">✅</span><strong>Setup looks correct!</strong><br><br>';
    echo 'If images still don\'t show, the issue might be:<br>';
    echo '1. Browser cache - try Ctrl+Shift+R<br>';
    echo '2. CDN/Cloudflare cache - purge cache<br>';
    echo '3. Apache not reading .htaccess - contact hosting support';
    echo '</div>';
}

?>

        <div style="margin-top: 30px; padding: 20px; background: #3a1a1a; border-radius: 8px; border: 2px solid #dc3545;">
            <strong style="color: #dc3545;">⚠️ SECURITY WARNING</strong><br>
            Delete this file immediately after reviewing: <code>public/check-images.php</code>
        </div>
    </div>
</body>
</html>
