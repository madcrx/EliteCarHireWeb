<?php
/**
 * Application Bootstrap
 * Used by cron jobs and CLI scripts
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Load configuration
$config = require __DIR__ . '/../config/app.php';

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load Database class (required by helpers)
require __DIR__ . '/Database.php';

// Load helpers
require __DIR__ . '/helpers.php';

// Set timezone
date_default_timezone_set(config('app.timezone', 'UTC'));
