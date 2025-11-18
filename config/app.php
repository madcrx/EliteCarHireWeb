<?php
// Main Application Configuration

return [
    'app' => [
        'name' => 'Elite Car Hire',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => getenv('APP_DEBUG') === 'true',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => 'Australia/Melbourne',
    ],
    
    'database' => require __DIR__ . '/database.php',
    
    'security' => [
        'session_name' => 'elite_car_hire_session',
        'session_lifetime' => 7200, // 2 hours
        'csrf_token_name' => '_csrf_token',
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
    ],
    
    'email' => [
        'from_address' => 'info@elitecarhire.au',
        'from_name' => 'Elite Car Hire',
        'smtp_host' => getenv('SMTP_HOST') ?: 'localhost',
        'smtp_port' => getenv('SMTP_PORT') ?: 587,
        'smtp_username' => getenv('SMTP_USER') ?: '',
        'smtp_password' => getenv('SMTP_PASS') ?: '',
        'smtp_encryption' => 'tls',
    ],
    
    'upload' => [
        'max_file_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
        'upload_path' => __DIR__ . '/../storage/uploads/',
    ],
    
    'pagination' => [
        'per_page' => 20,
    ],
    
    'payment' => [
        'currency' => 'AUD',
        'commission_rate' => 15.00, // percentage
    ],
    
    'settings' => [
        'auto_approve_customers' => true,
    ],
];
