#!/usr/bin/env php
<?php
/**
 * Email System Diagnostic Test
 *
 * This script tests all components of the email system to identify issues.
 * Run this manually to diagnose problems with the email queue processor.
 *
 * Usage: php scripts/test-email-system.php
 */

echo "========================================\n";
echo "Email System Diagnostic Test\n";
echo "========================================\n\n";

// Test 1: Bootstrap Loading
echo "[1/7] Testing bootstrap loading...\n";
try {
    $config = require __DIR__ . '/../app/bootstrap.php';
    echo "  ✓ Bootstrap loaded successfully\n";
    echo "  ✓ Config array loaded\n";
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Database Connection
echo "\n[2/7] Testing database connection...\n";
try {
    $db = db();
    echo "  ✓ Database connection successful\n";

    // Test query
    $result = $db->fetch("SELECT COUNT(*) as count FROM email_queue");
    echo "  ✓ Database query successful\n";
    echo "  → Found {$result['count']} email(s) in queue\n";
} catch (Exception $e) {
    echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check for Pending Emails
echo "\n[3/7] Checking for pending emails...\n";
$pendingEmails = $db->fetchAll("
    SELECT id, to_email, subject, status, attempts, error_message, created_at
    FROM email_queue
    WHERE status = 'pending'
    ORDER BY created_at ASC
    LIMIT 5
");

if (empty($pendingEmails)) {
    echo "  → No pending emails in queue\n";
} else {
    echo "  → Found " . count($pendingEmails) . " pending email(s):\n";
    foreach ($pendingEmails as $email) {
        echo "     - ID: {$email['id']} | To: {$email['to_email']} | Subject: {$email['subject']}\n";
        echo "       Attempts: {$email['attempts']} | Created: {$email['created_at']}\n";
        if (!empty($email['error_message'])) {
            echo "       Last Error: {$email['error_message']}\n";
        }
    }
}

// Test 4: Check PHPMailer
echo "\n[4/7] Checking PHPMailer installation...\n";
$phpMailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
if (file_exists($phpMailerPath)) {
    echo "  ✓ PHPMailer is installed\n";
    echo "  → Location: vendor/phpmailer/phpmailer/src/PHPMailer.php\n";
} else {
    echo "  ✗ PHPMailer NOT installed\n";
    echo "  → Run: composer install\n";
    echo "  → Or upload PHPMailer manually to vendor/phpmailer/phpmailer/\n";
}

// Test 5: Check Email Configuration
echo "\n[5/7] Checking email configuration...\n";
echo "  SMTP Host: " . ($config['email']['smtp_host'] ?? 'NOT SET') . "\n";
echo "  SMTP Port: " . ($config['email']['smtp_port'] ?? 'NOT SET') . "\n";
echo "  SMTP User: " . ($config['email']['smtp_username'] ?? 'NOT SET') . "\n";
echo "  SMTP Pass: " . (empty($config['email']['smtp_password']) ? 'NOT SET' : '***hidden***') . "\n";
echo "  From Address: " . ($config['email']['from_address'] ?? 'NOT SET') . "\n";

if (empty($config['email']['smtp_host']) || $config['email']['smtp_host'] === 'localhost') {
    echo "  ⚠ WARNING: SMTP not configured (using localhost)\n";
    echo "  → Configure SMTP in root .htaccess file\n";
}

// Test 6: Check Directories and Permissions
echo "\n[6/7] Checking directories and permissions...\n";
$dirs = [
    'storage/logs' => __DIR__ . '/../storage/logs',
    'storage/uploads' => __DIR__ . '/../storage/uploads',
];

foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "  ✓ {$name} exists and is writable\n";
        } else {
            echo "  ✗ {$name} exists but is NOT writable\n";
            echo "  → Run: chmod 755 {$path}\n";
        }
    } else {
        echo "  ✗ {$name} does NOT exist\n";
        echo "  → Run: mkdir -p {$path} && chmod 755 {$path}\n";
    }
}

// Test 7: Test Email Sending Capability
echo "\n[7/7] Testing email sending capability...\n";
if (file_exists($phpMailerPath)) {
    echo "  ✓ PHPMailer is installed and ready\n";

    if (!empty($pendingEmails)) {
        echo "  ✓ Found " . count($pendingEmails) . " pending email(s) ready to be sent\n";
        echo "\n  Next steps to send pending emails:\n";
        echo "  1. Manually run: php scripts/process-email-queue.php\n";
        echo "  2. Or wait for cron job to process them automatically\n";
    } else {
        echo "  → No pending emails in queue\n";
        echo "  → Submit a test contact form to create a test email\n";
    }
} else {
    echo "  ✗ PHPMailer NOT installed\n";
    echo "  → Email sending will NOT work until PHPMailer is installed\n";
    echo "  → Run: composer install\n";
}

// Summary
echo "\n========================================\n";
echo "Diagnostic Summary\n";
echo "========================================\n\n";

$issues = [];
if (!file_exists($phpMailerPath)) {
    $issues[] = "PHPMailer not installed - run 'composer install'";
}
if (empty($config['email']['smtp_host']) || $config['email']['smtp_host'] === 'localhost') {
    $issues[] = "SMTP not configured - edit root .htaccess";
}
if (!empty($pendingEmails)) {
    $issues[] = count($pendingEmails) . " email(s) stuck in pending status";
}

if (empty($issues)) {
    echo "✓ All checks passed! Email system should be working.\n\n";
} else {
    echo "Issues found:\n";
    foreach ($issues as $issue) {
        echo "  • {$issue}\n";
    }
    echo "\nSee EMAIL_DIAGNOSIS.md and CRON_TROUBLESHOOTING.md for fixes.\n\n";
}

echo "For detailed troubleshooting, see:\n";
echo "  - CRON_TROUBLESHOOTING.md\n";
echo "  - EMAIL_DIAGNOSIS.md\n";
echo "  - EMAIL_QUICKSTART.md\n\n";
