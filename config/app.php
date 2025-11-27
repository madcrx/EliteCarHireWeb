<?php
// Main Application Configuration

return [
    'app' => [
        'name' => 'Elite Car Hire',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => getenv('APP_DEBUG') === 'true',
        'url' => getenv('APP_URL') ?: 'http://ech.cyberlogicit.com.au',
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
        'from_address' => 'noreply@elitecarhire.au',
        'from_name' => 'Elite Car Hire',

        // Role-specific email addresses
        'admin_address' => getenv('ADMIN_EMAIL') ?: 'admin@elitecarhire.au',
        'booking_confirmations' => getenv('BOOKING_EMAIL') ?: 'bookings_confirmations@elitecarhire.au',
        'payment_confirmations' => getenv('PAYMENT_EMAIL') ?: 'payment_confirmations@elitecarhire.au',
        'cancellations' => getenv('CANCELLATION_EMAIL') ?: 'cancellations@elitecarhire.au',
        'disputes' => getenv('DISPUTES_EMAIL') ?: 'disputes@elitecarhire.au',
        'contact_inquiries' => getenv('CONTACT_EMAIL') ?: 'inquiries@elitecarhire.au',
        'vehicle_approvals' => getenv('VEHICLE_EMAIL') ?: 'vehicles@elitecarhire.au',
        'support' => getenv('SUPPORT_EMAIL') ?: 'support@elitecarhire.au',

        // SMTP Settings
        'smtp_host' => getenv('SMTP_HOST') ?: 'localhost',
        'smtp_port' => getenv('SMTP_PORT') ?: 587,
        'smtp_username' => getenv('SMTP_USER') ?: '',
        'smtp_password' => getenv('SMTP_PASS') ?: '',
        'smtp_encryption' => 'tls',
    ],
    
    'upload' => [
        'max_file_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'webp'],
        'upload_path' => __DIR__ . '/../storage/uploads/',
    ],
    
    'pagination' => [
        'per_page' => 20,
    ],
    
    'payment' => [
        'currency' => 'AUD',
        'commission_rate' => 15.00, // percentage
        'stripe' => [
            'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
            'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
            'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
            'connect_client_id' => getenv('STRIPE_CONNECT_CLIENT_ID') ?: '',
        ],
    ],
    
    'settings' => [
        'auto_approve_customers' => true,
    ],
];
