<?php
/**
 * STRIPE INTEGRATION ROUTES
 *
 * Add these routes to your public/index.php file
 * Place them in the appropriate sections with other similar routes
 */

// =====================================================
// ADMIN ROUTES - Add to Admin section
// =====================================================
$router->get('/admin/settings/stripe', 'AdminStripeController@index');
$router->post('/admin/settings/stripe/update', 'AdminStripeController@update');

// =====================================================
// API ROUTES - Add to API section
// =====================================================
$router->post('/api/payment/create-intent', 'PaymentController@createPaymentIntent');
// Note: /api/payment/process route should already exist

// =====================================================
// WEBHOOK ROUTES - Add to webhooks section (if any)
// =====================================================
// The Stripe webhook is a standalone file at: public/webhook/stripe.php
// It doesn't need a route - access it directly at: /webhook/stripe.php
