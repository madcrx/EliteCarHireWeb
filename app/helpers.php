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
 * Calculate next payout date (Monday) with minimum 4-day waiting period
 *
 * Payouts are processed weekly on Mondays. Bookings must be completed
 * for at least 4 days before being eligible for payout.
 *
 * @param string $bookingEndDate - The booking end_date (Y-m-d format)
 * @return string - Next Monday payout date (Y-m-d format)
 */
function calculateNextPayoutDate($bookingEndDate) {
    // Minimum 4 days after booking completion
    $earliestPayoutDate = date('Y-m-d', strtotime($bookingEndDate . ' +4 days'));

    // Find the next Monday from earliest payout date
    $earliestTimestamp = strtotime($earliestPayoutDate);
    $dayOfWeek = date('N', $earliestTimestamp); // 1 (Monday) through 7 (Sunday)

    if ($dayOfWeek == 1) {
        // Already Monday
        $nextMonday = $earliestPayoutDate;
    } else {
        // Calculate days until next Monday
        $daysUntilMonday = (8 - $dayOfWeek) % 7;
        if ($daysUntilMonday == 0) {
            $daysUntilMonday = 7;
        }
        $nextMonday = date('Y-m-d', strtotime($earliestPayoutDate . ' +' . $daysUntilMonday . ' days'));
    }

    return $nextMonday;
}
