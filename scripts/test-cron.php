#!/usr/bin/env php
<?php
/**
 * Cron Job Test Script
 *
 * This simple script verifies that cron jobs can execute PHP scripts.
 * Add this as a temporary cron job to test if cron is working.
 *
 * Test cron job (runs every minute):
 * * * * * * /usr/bin/php /home/cp825575/public_html/scripts/test-cron.php >> /home/cp825575/public_html/storage/logs/cron-test.log 2>&1
 *
 * After 2-3 minutes, check storage/logs/cron-test.log to see if it ran.
 */

$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Cron test script executed successfully!\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Working Directory: " . getcwd() . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "---\n";
