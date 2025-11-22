<?php
// Clear PHP OPcache
// Access this file once via browser to clear cache: http://yourdomain.com/clear-cache.php

$cleared = [];

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    $cleared[] = 'OPcache cleared';
}

// Clear file stat cache
clearstatcache(true);
$cleared[] = 'File stat cache cleared';

// Clear realpath cache
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    $cleared[] = 'Realpath cache cleared';
}

// Display results
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cache Cleared - Elite Car Hire</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        h1 { color: #2c3e50; margin-top: 0; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        ul { margin: 10px 0; padding-left: 20px; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #C5A253;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover { background: #a68944; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✓ Cache Cleared Successfully</h1>

        <div class="success">
            <strong>The following caches have been cleared:</strong>
            <ul>
                <?php foreach ($cleared as $item): ?>
                    <li><?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="info">
            <strong>What was cleared:</strong>
            <ul>
                <li><strong>OPcache:</strong> PHP's opcode cache that stores precompiled script bytecode</li>
                <li><strong>File Stat Cache:</strong> Cache of file metadata and existence checks</li>
                <li><strong>Realpath Cache:</strong> Cache of resolved file paths</li>
            </ul>
        </div>

        <p>Your application should now use the latest code files. Please try accessing the vehicle edit page again.</p>

        <a href="/admin/vehicles" class="btn">Go to Admin Vehicles</a>
        <a href="/" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
<?php
// Delete this file after use for security
// Uncomment the line below if you want the script to self-delete after running once
// @unlink(__FILE__);
?>
