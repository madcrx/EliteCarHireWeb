<?php
// Elite Car Hire - Main Entry Point
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Load configuration
$config = require __DIR__ . '/../config/app.php';

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load helpers
require __DIR__ . '/../app/helpers.php';

// Initialize router
require __DIR__ . '/../app/Router.php';
$router = new Router();

// Define routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Admin routes
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/{id}', 'AdminController@viewUser');
$router->post('/admin/users/{id}/approve', 'AdminController@approveUser');
$router->post('/admin/users/{id}/reject', 'AdminController@rejectUser');
$router->get('/admin/vehicles', 'AdminController@vehicles');
$router->get('/admin/vehicles/{id}/approve', 'AdminController@approveVehicle');
$router->get('/admin/bookings', 'AdminController@bookings');
$router->get('/admin/payments', 'AdminController@payments');
$router->get('/admin/payouts', 'AdminController@payouts');
$router->get('/admin/disputes', 'AdminController@disputes');
$router->get('/admin/analytics', 'AdminController@analytics');
$router->get('/admin/security', 'AdminController@security');
$router->get('/admin/audit-logs', 'AdminController@auditLogs');
$router->get('/admin/cms', 'AdminController@cms');
$router->post('/admin/cms/save', 'AdminController@saveCms');
$router->get('/admin/settings', 'AdminController@settings');
$router->get('/admin/pending-changes', 'AdminController@pendingChanges');
$router->post('/admin/pending-changes/{id}/approve', 'AdminController@approvePendingChange');
$router->get('/admin/contact-submissions', 'AdminController@contactSubmissions');
$router->get('/admin/images', 'ImageController@index');
$router->post('/admin/images/upload', 'ImageController@upload');
$router->post('/admin/images/revert', 'ImageController@revertToDefault');

// Owner routes
$router->get('/owner/dashboard', 'OwnerController@dashboard');
$router->get('/owner/listings', 'OwnerController@listings');
$router->get('/owner/listings/add', 'OwnerController@addListing');
$router->post('/owner/listings/add', 'OwnerController@saveListing');
$router->get('/owner/listings/{id}/edit', 'OwnerController@editListing');
$router->post('/owner/listings/{id}/edit', 'OwnerController@updateListing');
$router->get('/owner/bookings', 'OwnerController@bookings');
$router->get('/owner/calendar', 'OwnerController@calendar');
$router->post('/owner/calendar/block', 'OwnerController@blockDates');
$router->post('/owner/calendar/unblock', 'OwnerController@unblockDate');
$router->get('/owner/analytics', 'OwnerController@analytics');
$router->get('/owner/payouts', 'OwnerController@payouts');
$router->get('/owner/reviews', 'OwnerController@reviews');
$router->get('/owner/messages', 'OwnerController@messages');
$router->get('/owner/pending-changes', 'OwnerController@pendingChanges');

// Customer routes
$router->get('/customer/dashboard', 'CustomerController@dashboard');
$router->get('/customer/hires', 'CustomerController@hires');
$router->get('/customer/bookings', 'CustomerController@bookings');
$router->get('/customer/profile', 'CustomerController@profile');
$router->post('/customer/profile/update', 'CustomerController@updateProfile');

// Public routes
$router->get('/vehicles', 'PublicController@vehicles');
$router->get('/vehicles/{id}', 'PublicController@viewVehicle');
$router->post('/booking/create', 'BookingController@create');
$router->get('/terms', 'PublicController@terms');
$router->get('/privacy', 'PublicController@privacy');
$router->get('/faq', 'PublicController@faq');
$router->get('/contact', 'PublicController@contact');
$router->post('/contact/submit', 'PublicController@submitContact');
$router->get('/about', 'PublicController@about');
$router->get('/services', 'PublicController@services');
$router->get('/support', 'PublicController@support');

// API routes for AJAX
$router->post('/api/payment/process', 'PaymentController@process');
$router->get('/api/calendar/events', 'CalendarController@getEvents');
$router->get('/api/analytics/data', 'AnalyticsController@getData');
$router->post('/api/notifications/mark-read', 'NotificationController@markAsRead');

// Dispatch the request
$router->dispatch();
