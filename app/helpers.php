<?php
// Helper Functions

function config($key, $default = null) {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

function db() {
    return Database::getInstance();
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function view($name, $data = []) {
    extract($data);
    require __DIR__ . "/views/$name.php";
}

function auth() {
    return isset($_SESSION['user_id']) ? $_SESSION : null;
}

function authUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE id = ?";
    return db()->fetch($sql, [$_SESSION['user_id']]);
}

function requireAuth($role = null) {
    if (!auth()) {
        redirect('/login');
    }
    
    if ($role && $_SESSION['role'] !== $role) {
        redirect('/login');
    }
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash($key, $value = null) {
    if ($value === null) {
        $msg = $_SESSION["flash_$key"] ?? null;
        unset($_SESSION["flash_$key"]);
        return $msg;
    }
    $_SESSION["flash_$key"] = $value;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function setOld($data) {
    $_SESSION['old'] = $data;
}

function clearOld() {
    unset($_SESSION['old']);
}

function generateBookingReference() {
    return 'ECH' . date('Ymd') . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
}

function formatMoney($amount) {
    return '$' . number_format($amount, 2);
}

function sendEmail($to, $subject, $body) {
    $headers = "From: " . config('email.from_name') . " <" . config('email.from_address') . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Queue email for sending
    $sql = "INSERT INTO email_queue (to_email, to_name, subject, body_html, status) VALUES (?, ?, ?, ?, 'pending')";
    try {
        db()->execute($sql, [$to, '', $subject, $body]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to queue email: " . $e->getMessage());
        return false;
    }
}

function logAudit($action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null) {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, old_values, new_values)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        db()->execute($sql, [
            $userId,
            $action,
            $entityType,
            $entityId,
            $ipAddress,
            $userAgent,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null
        ]);
    } catch (\PDOException $e) {
        // Log audit errors silently - don't break the application
        error_log("Audit log error: " . $e->getMessage());
    }
}

function createNotification($userId, $type, $title, $message, $link = null) {
    try {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)";
        db()->execute($sql, [$userId, $type, $title, $message, $link]);
    } catch (\PDOException $e) {
        // Log notification errors silently - don't break the application
        error_log("Create notification error: " . $e->getMessage());
    }
}

function asset($path) {
    return '/assets/' . ltrim($path, '/');
}

function url($path = '') {
    return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
}

function json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function uploadFile($file, $directory = 'uploads') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $maxSize = config('upload.max_file_size');
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedTypes = config('upload.allowed_types');
    
    if (!in_array(strtolower($extension), $allowedTypes)) {
        return false;
    }
    
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = __DIR__ . "/../storage/$directory/" . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return "storage/$directory/$filename";
    }

    return false;
}

/**
 * Convert timestamp to human-readable "time ago" format
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'just now';
    }

    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute'
    ];

    foreach ($intervals as $seconds => $label) {
        $count = floor($diff / $seconds);
        if ($count > 0) {
            return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
        }
    }

    return 'just now';
}

/**
 * Generate a secure action token for email links
 *
 * @param int $userId User who can use this token
 * @param string $actionType Type of action (e.g., 'confirm_booking', 'cancel_booking')
 * @param string $entityType Entity type (e.g., 'booking', 'vehicle')
 * @param int $entityId ID of the entity
 * @param int $expiryHours Hours until token expires (default 72)
 * @param array $metadata Optional additional data
 * @return string The generated token
 */
function generateActionToken($userId, $actionType, $entityType, $entityId, $expiryHours = 72, $metadata = null) {
    // Generate cryptographically secure random token
    $token = bin2hex(random_bytes(32));

    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));

    $metadataJson = $metadata ? json_encode($metadata) : null;

    db()->execute(
        "INSERT INTO action_tokens (token, user_id, action_type, entity_type, entity_id, metadata, expires_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$token, $userId, $actionType, $entityType, $entityId, $metadataJson, $expiresAt]
    );

    return $token;
}

/**
 * Verify and consume an action token
 *
 * @param string $token The token to verify
 * @return array|false Token data if valid, false otherwise
 */
function verifyActionToken($token) {
    $tokenData = db()->fetch(
        "SELECT * FROM action_tokens WHERE token = ? AND expires_at > NOW() AND used_at IS NULL",
        [$token]
    );

    if (!$tokenData) {
        return false;
    }

    // Mark token as used
    db()->execute("UPDATE action_tokens SET used_at = NOW() WHERE id = ?", [$tokenData['id']]);

    // Decode metadata if present
    if ($tokenData['metadata']) {
        $tokenData['metadata'] = json_decode($tokenData['metadata'], true);
    }

    return $tokenData;
}

/**
 * Generate action URL with token
 *
 * @param string $actionType Action type
 * @param string $path Base path
 * @param int $userId User ID
 * @param string $entityType Entity type
 * @param int $entityId Entity ID
 * @param int $expiryHours Expiry hours
 * @return string Full URL with token
 */
function generateActionUrl($actionType, $path, $userId, $entityType, $entityId, $expiryHours = 72) {
    $token = generateActionToken($userId, $actionType, $entityType, $entityId, $expiryHours);
    $baseUrl = rtrim(config('app.url', 'http://localhost'), '/');
    return "{$baseUrl}{$path}?token={$token}";
}

/**
 * Generate login redirect URL
 *
 * @param string $redirectPath Where to redirect after login
 * @return string Full URL
 */
function generateLoginUrl($redirectPath) {
    $baseUrl = rtrim(config('app.url', 'http://localhost'), '/');
    $encodedPath = urlencode($redirectPath);
    return "{$baseUrl}/login?redirect={$encodedPath}";
}

/**
 * Get email button HTML
 *
 * @param string $url Button URL
 * @param string $text Button text
 * @param string $color Button color (primary/success/danger/warning)
 * @return string HTML for email button
 */
function getEmailButton($url, $text, $color = 'primary') {
    $colors = [
        'primary' => '#C5A253',
        'success' => '#4caf50',
        'danger' => '#e74c3c',
        'warning' => '#f39c12',
        'info' => '#3498db'
    ];

    $bgColor = $colors[$color] ?? $colors['primary'];

    return "
    <table border='0' cellpadding='0' cellspacing='0' style='margin: 20px 0;'>
        <tr>
            <td align='center' style='border-radius: 4px;' bgcolor='{$bgColor}'>
                <a href='{$url}' target='_blank' style='font-size: 16px; font-family: Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 4px; display: inline-block; font-weight: 600;'>
                    {$text}
                </a>
            </td>
        </tr>
    </table>
    ";
}

/**
 * Track an email for reminder sending
 *
 * @param string $emailType Type of email (booking_request, vehicle_approval, etc.)
 * @param string $entityType Entity type (booking, vehicle, pending_change, contact_submission)
 * @param int $entityId ID of the related entity
 * @param string $recipientEmail Email address of recipient
 * @param string $subject Email subject line
 * @return int|false The reminder ID or false on failure
 */
function trackEmailForReminder($emailType, $entityType, $entityId, $recipientEmail, $subject) {
    try {
        db()->execute(
            "INSERT INTO email_reminders (email_type, entity_type, entity_id, recipient_email, subject, sent_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$emailType, $entityType, $entityId, $recipientEmail, $subject]
        );
        return db()->lastInsertId();
    } catch (Exception $e) {
        error_log("Failed to track email for reminder: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark an email reminder as responded to
 *
 * @param string $entityType Entity type
 * @param int $entityId Entity ID
 * @return bool Success status
 */
function markEmailReminderResponded($entityType, $entityId) {
    try {
        db()->execute(
            "UPDATE email_reminders
             SET response_received = 1, response_received_at = NOW()
             WHERE entity_type = ? AND entity_id = ? AND response_received = 0",
            [$entityType, $entityId]
        );
        return true;
    } catch (Exception $e) {
        error_log("Failed to mark email reminder as responded: " . $e->getMessage());
        return false;
    }
}

/**
 * Get emails that need reminders (sent >12 hours ago, no response, no reminder sent yet)
 *
 * @return array Array of email reminders needing to be sent
 */
function getEmailsNeedingReminders() {
    try {
        return db()->fetchAll(
            "SELECT * FROM email_reminders
             WHERE response_received = 0
             AND reminder_sent_at IS NULL
             AND sent_at < DATE_SUB(NOW(), INTERVAL 12 HOUR)
             ORDER BY sent_at ASC"
        );
    } catch (Exception $e) {
        error_log("Failed to get emails needing reminders: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark a reminder as sent
 *
 * @param int $reminderId Reminder ID
 * @return bool Success status
 */
function markReminderSent($reminderId) {
    try {
        db()->execute(
            "UPDATE email_reminders SET reminder_sent_at = NOW() WHERE id = ?",
            [$reminderId]
        );
        return true;
    } catch (Exception $e) {
        error_log("Failed to mark reminder as sent: " . $e->getMessage());
        return false;
    }
}

