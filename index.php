<?php
/**
 * Front Controller Redirect
 * 
 * This file serves as the entry point when document root cannot be changed.
 * It includes the actual index.php from the public directory.
 */

// Change to the public directory
chdir(__DIR__ . '/public');

// Include the actual application entry point
require __DIR__ . '/public/index.php';
