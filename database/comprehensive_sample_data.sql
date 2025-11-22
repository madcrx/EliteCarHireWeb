-- Elite Car Hire - Comprehensive Sample Data
-- Run this AFTER importing cpanel_schema.sql and phase2_updates.sql
-- This file provides complete test data for all features
-- Password for all test users: password123

-- ==============================================================================
-- SAMPLE USERS
-- ==============================================================================

-- Admin User
INSERT INTO users (email, password, first_name, last_name, phone, role, status, created_at, last_login) VALUES
('admin@elitecarhire.au', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '0406 907 849', 'admin', 'active', '2025-01-01 10:00:00', NOW())
ON DUPLICATE KEY UPDATE email=email;

-- Sample Vehicle Owners
INSERT INTO users (email, password, first_name, last_name, phone, role, status, created_at, last_login) VALUES
('owner1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'James', 'Smith', '0412 345 678', 'owner', 'active', '2025-01-05 09:00:00', NOW()),
('owner2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Wilson', '0423 456 789', 'owner', 'active', '2025-01-06 10:30:00', NOW()),
('owner3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', '0434 567 890', 'owner', 'pending', '2025-01-10 14:20:00', NULL)
ON DUPLICATE KEY UPDATE email=email;

-- Sample Customers
INSERT INTO users (email, password, first_name, last_name, phone, role, status, created_at, last_login) VALUES
('customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', '0445 678 901', 'customer', 'active', '2025-01-08 11:00:00', NOW()),
('customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Lee', '0456 789 012', 'customer', 'active', '2025-01-12 15:45:00', NOW()),
('customer3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jessica', 'Taylor', '0467 890 123', 'customer', 'active', '2025-01-15 09:30:00', NOW()),
('customer4@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Anderson', '0478 901 234', 'customer', 'pending', '2025-01-18 16:20:00', NULL),
('customer5@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Linda', 'Martinez', '0489 012 345', 'customer', 'suspended', '2025-01-20 12:00:00', '2025-01-21 10:00:00')
ON DUPLICATE KEY UPDATE email=email;

-- ==============================================================================
-- SAMPLE VEHICLES
-- ==============================================================================

-- Owner 1's vehicles (James Smith - owner1@example.com)
INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Mercedes-Benz', 'S-Class', 2023, 'luxury_exotic', 95.00, 'Black', 'ABC123-VIC', '["Leather seats", "Premium sound system", "Navigation", "Bluetooth", "Climate control"]', 'approved', '2025-01-06 10:00:00'
FROM users WHERE email = 'owner1@example.com';

INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'BMW', '7 Series', 2024, 'luxury_exotic', 105.00, 'Silver', 'DEF456-VIC', '["Heated seats", "Sunroof", "Advanced safety features", "Premium audio"]', 'approved', '2025-01-06 11:00:00'
FROM users WHERE email = 'owner1@example.com';

INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Audi', 'A8', 2023, 'premium', 90.00, 'White', 'GHI789-VIC', '["Massage seats", "Premium audio", "Adaptive cruise control", "LED headlights"]', 'approved', '2025-01-07 09:00:00'
FROM users WHERE email = 'owner1@example.com';

-- Owner 2's vehicles (Emma Wilson - owner2@example.com)
INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Porsche', '911 Carrera', 2024, 'luxury_exotic', 180.00, 'Red', 'JKL012-NSW', '["Sport exhaust", "Carbon fiber trim", "Track mode", "Sport seats"]', 'approved', '2025-01-08 14:00:00'
FROM users WHERE email = 'owner2@example.com';

INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Ferrari', 'F8 Tributo', 2023, 'luxury_exotic', 320.00, 'Yellow', 'MNO345-NSW', '["V8 engine", "Carbon ceramic brakes", "Launch control", "Premium interior"]', 'approved', '2025-01-08 15:00:00'
FROM users WHERE email = 'owner2@example.com';

INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Lamborghini', 'Huracán', 2024, 'luxury_exotic', 380.00, 'Orange', 'PQR678-NSW', '["AWD", "Carbon fiber bodykit", "Race mode", "Performance exhaust"]', 'approved', '2025-01-09 10:00:00'
FROM users WHERE email = 'owner2@example.com';

INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Tesla', 'Model S Plaid', 2024, 'premium', 115.00, 'Blue', 'STU901-NSW', '["Autopilot", "Supercharging included", "0-100 in 2.1s", "Premium interior"]', 'pending', '2025-01-10 12:00:00'
FROM users WHERE email = 'owner2@example.com';

-- Owner 3's vehicles (Michael Brown - owner3@example.com - pending owner)
INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at)
SELECT id, 'Rolls-Royce', 'Ghost', 2023, 'luxury_exotic', 425.00, 'Black', 'VWX234-QLD', '["Starlight headliner", "Champagne cooler", "Bespoke audio", "Luxury seats"]', 'pending', '2025-01-11 11:00:00'
FROM users WHERE email = 'owner3@example.com';

-- ==============================================================================
-- SAMPLE CONTACT SUBMISSIONS
-- ==============================================================================

INSERT INTO contact_submissions (name, email, phone, subject, message, status, created_at) VALUES
('John Customer', 'john@example.com', '0411 222 333', 'Booking Inquiry', 'I would like to inquire about booking a luxury vehicle for my wedding in March.', 'new', '2025-01-15 10:00:00'),
('Mary Jones', 'mary@example.com', '0422 333 444', 'Vehicle Availability', 'Do you have any sports cars available for the Australia Day weekend?', 'read', '2025-01-16 14:00:00'),
('Peter Brown', 'peter@example.com', '0433 444 555', 'Pricing Question', 'Can you provide pricing for a 3-day rental of your Mercedes S-Class?', 'new', '2025-01-18 09:00:00'),
('Susan Wilson', 'susan@example.com', '0444 555 666', 'Corporate Booking', 'We need 5 luxury vehicles for a corporate event. Please contact me.', 'new', '2025-01-20 11:00:00')
ON DUPLICATE KEY UPDATE email=email;

-- ==============================================================================
-- SAMPLE BOOKINGS
-- ==============================================================================

-- Booking 1: Customer1 books Mercedes S-Class (Owner1) - Completed Wedding
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, created_at)
SELECT
    'ECH-2025-001',
    (SELECT id FROM users WHERE email = 'customer1@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'ABC123-VIC'),
    (SELECT id FROM users WHERE email = 'owner1@example.com'),
    '2025-01-10',
    '14:00:00',
    '22:00:00',
    8.00,
    'Melbourne CBD',
    'Melbourne CBD',
    'wedding',
    760.00,
    760.00,
    114.00,
    'completed',
    'paid',
    '2025-01-05 10:00:00';

-- Booking 2: Customer2 books Porsche 911 (Owner2) - Confirmed Corporate Event
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, created_at)
SELECT
    'ECH-2025-002',
    (SELECT id FROM users WHERE email = 'customer2@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'JKL012-NSW'),
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    '2025-01-25',
    '09:00:00',
    '17:00:00',
    8.00,
    'Sydney Airport',
    'Sydney CBD',
    'corporate',
    1440.00,
    1440.00,
    216.00,
    'confirmed',
    'paid',
    '2025-01-15 14:30:00';

-- Booking 3: Customer3 books BMW 7 Series (Owner1) - In Progress Photoshoot
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, created_at)
SELECT
    'ECH-2025-003',
    (SELECT id FROM users WHERE email = 'customer3@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'DEF456-VIC'),
    (SELECT id FROM users WHERE email = 'owner1@example.com'),
    '2025-01-22',
    '10:00:00',
    '16:00:00',
    6.00,
    'Melbourne Airport',
    'Melbourne Airport',
    'photoshoot',
    630.00,
    630.00,
    94.50,
    'in_progress',
    'paid',
    '2025-01-18 09:15:00';

-- Booking 4: Customer1 books Ferrari F8 (Owner2) - Completed Special Occasion
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, created_at)
SELECT
    'ECH-2025-004',
    (SELECT id FROM users WHERE email = 'customer1@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'MNO345-NSW'),
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    '2025-01-08',
    '18:00:00',
    '23:00:00',
    5.00,
    'Sydney CBD',
    'Sydney CBD',
    'special_occasion',
    1600.00,
    1600.00,
    240.00,
    'completed',
    'paid',
    '2025-01-02 16:20:00';

-- Booking 5: Customer2 books Audi A8 (Owner1) - Pending
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, created_at)
SELECT
    'ECH-2025-005',
    (SELECT id FROM users WHERE email = 'customer2@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'GHI789-VIC'),
    (SELECT id FROM users WHERE email = 'owner1@example.com'),
    '2025-02-05',
    '12:00:00',
    '18:00:00',
    6.00,
    'Melbourne CBD',
    'Melbourne CBD',
    'other',
    540.00,
    540.00,
    81.00,
    'pending',
    'pending',
    '2025-01-20 11:45:00';

-- Booking 6: Customer3 books Lamborghini Huracán (Owner2) - Confirmed
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, created_at)
SELECT
    'ECH-2025-006',
    (SELECT id FROM users WHERE email = 'customer3@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'PQR678-NSW'),
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    '2025-01-28',
    '15:00:00',
    '20:00:00',
    5.00,
    'Sydney Airport',
    'Sydney Airport',
    'wedding',
    1900.00,
    1900.00,
    285.00,
    'confirmed',
    'paid',
    '2025-01-21 10:00:00';

-- Booking 7: Customer1 books Tesla Model S (Owner2) - Cancelled
INSERT INTO bookings (booking_reference, customer_id, vehicle_id, owner_id, booking_date, start_time, end_time, duration_hours, pickup_location, dropoff_location, event_type, base_amount, total_amount, commission_amount, status, payment_status, cancellation_reason, cancelled_at, created_at)
SELECT
    'ECH-2025-007',
    (SELECT id FROM users WHERE email = 'customer1@example.com'),
    (SELECT id FROM vehicles WHERE registration_number = 'STU901-NSW'),
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    '2025-01-15',
    '09:00:00',
    '17:00:00',
    8.00,
    'Sydney CBD',
    'Sydney CBD',
    'corporate',
    920.00,
    920.00,
    138.00,
    'cancelled',
    'refunded',
    'Customer changed plans - requested full refund',
    '2025-01-13 14:30:00',
    '2025-01-12 08:00:00';

-- ==============================================================================
-- SAMPLE PAYMENTS
-- ==============================================================================

-- Payment for Booking 1 (Completed Mercedes S-Class Wedding)
INSERT INTO payments (booking_id, transaction_id, amount, payment_method, card_last_four, card_brand, status, payment_date, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-001'),
    'TXN-20250105-ABC123',
    760.00,
    'credit_card',
    '4242',
    'Visa',
    'completed',
    '2025-01-05 10:05:00',
    '2025-01-05 10:05:00';

-- Payment for Booking 2 (Confirmed Porsche Corporate)
INSERT INTO payments (booking_id, transaction_id, amount, payment_method, card_last_four, card_brand, status, payment_date, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-002'),
    'TXN-20250115-JKL012',
    1440.00,
    'credit_card',
    '5555',
    'Mastercard',
    'completed',
    '2025-01-15 14:35:00',
    '2025-01-15 14:35:00';

-- Payment for Booking 3 (In Progress BMW Photoshoot)
INSERT INTO payments (booking_id, transaction_id, amount, payment_method, card_last_four, card_brand, status, payment_date, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-003'),
    'TXN-20250118-DEF456',
    630.00,
    'credit_card',
    '4111',
    'Visa',
    'completed',
    '2025-01-18 09:20:00',
    '2025-01-18 09:20:00';

-- Payment for Booking 4 (Completed Ferrari Special Occasion)
INSERT INTO payments (booking_id, transaction_id, amount, payment_method, card_last_four, card_brand, status, payment_date, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-004'),
    'TXN-20250102-MNO345',
    1600.00,
    'bank_transfer',
    NULL,
    NULL,
    'completed',
    '2025-01-02 16:30:00',
    '2025-01-02 16:30:00';

-- Payment for Booking 6 (Confirmed Lamborghini Wedding)
INSERT INTO payments (booking_id, transaction_id, amount, payment_method, card_last_four, card_brand, status, payment_date, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-006'),
    'TXN-20250121-PQR678',
    1900.00,
    'credit_card',
    '3782',
    'Amex',
    'completed',
    '2025-01-21 10:05:00',
    '2025-01-21 10:05:00';

-- Payment for Booking 7 (Cancelled Tesla - Refunded)
INSERT INTO payments (booking_id, transaction_id, amount, payment_method, card_last_four, card_brand, status, payment_date, refund_amount, refund_date, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-007'),
    'TXN-20250112-STU901',
    920.00,
    'credit_card',
    '4242',
    'Visa',
    'refunded',
    '2025-01-12 08:10:00',
    920.00,
    '2025-01-13 15:00:00',
    '2025-01-12 08:10:00';

-- ==============================================================================
-- SAMPLE PAYOUTS
-- ==============================================================================

-- Payout for Owner1 - Booking 1 (Mercedes S-Class Wedding)
INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, payout_date, reference, created_at)
SELECT
    (SELECT id FROM users WHERE email = 'owner1@example.com'),
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-001'),
    646.00,
    'completed',
    '2025-01-17',
    '2025-01-17 09:00:00',
    'PAYOUT-001-2025',
    '2025-01-11 10:00:00';

-- Payout for Owner2 - Booking 4 (Ferrari Special Occasion)
INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, payout_date, reference, created_at)
SELECT
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-004'),
    1360.00,
    'completed',
    '2025-01-15',
    '2025-01-15 09:00:00',
    'PAYOUT-002-2025',
    '2025-01-09 14:00:00';

-- Payout for Owner2 - Booking 2 (Porsche Corporate) - Scheduled
INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, reference, created_at)
SELECT
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-002'),
    1224.00,
    'scheduled',
    '2025-02-01',
    'PAYOUT-003-2025',
    '2025-01-16 10:00:00';

-- Payout for Owner1 - Booking 3 (BMW Photoshoot) - Processing
INSERT INTO payouts (owner_id, booking_id, amount, status, scheduled_date, reference, created_at)
SELECT
    (SELECT id FROM users WHERE email = 'owner1@example.com'),
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-003'),
    535.50,
    'processing',
    '2025-01-29',
    'PAYOUT-004-2025',
    '2025-01-23 11:00:00';

-- ==============================================================================
-- SAMPLE DISPUTES
-- ==============================================================================

-- Dispute 1: Service quality issue on completed booking
INSERT INTO disputes (booking_id, raised_by, dispute_type, description, status, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-001'),
    (SELECT id FROM users WHERE email = 'customer1@example.com'),
    'service_quality',
    'Vehicle arrived 30 minutes late for the wedding pickup. Driver was apologetic but this caused stress on our special day.',
    'investigating',
    '2025-01-11 09:00:00';

-- Dispute 2: Payment dispute - resolved
INSERT INTO disputes (booking_id, raised_by, dispute_type, description, status, resolution, resolved_by, resolved_at, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-004'),
    (SELECT id FROM users WHERE email = 'owner2@example.com'),
    'payment',
    'Customer disputed additional toll charges of $45 that were clearly stated in the booking terms.',
    'resolved',
    'After reviewing the booking agreement and toll receipts, charges were validated. Customer accepted the resolution.',
    (SELECT id FROM users WHERE email = 'admin@elitecarhire.au'),
    '2025-01-10 15:30:00',
    '2025-01-09 11:00:00';

-- Dispute 3: Damage claim - open
INSERT INTO disputes (booking_id, raised_by, dispute_type, description, status, created_at)
SELECT
    (SELECT id FROM bookings WHERE booking_reference = 'ECH-2025-003'),
    (SELECT id FROM users WHERE email = 'owner1@example.com'),
    'damage',
    'Customer returned vehicle with minor scratches on rear bumper. Requesting $850 for repair costs. Customer claims damage was pre-existing.',
    'open',
    '2025-01-22 17:00:00';

-- ==============================================================================
-- NOTES
-- ==============================================================================
--
-- Test Credentials:
-- -----------------
-- Admin: admin@elitecarhire.au / password123
--
-- Owners:
--   owner1@example.com / password123 (Active - 3 vehicles)
--   owner2@example.com / password123 (Active - 4 vehicles)
--   owner3@example.com / password123 (Pending - 1 vehicle)
--
-- Customers:
--   customer1@example.com / password123 (Active)
--   customer2@example.com / password123 (Active)
--   customer3@example.com / password123 (Active)
--   customer4@example.com / password123 (Pending)
--   customer5@example.com / password123 (Suspended)
--
-- Sample Data Summary:
-- --------------------
-- ✓ 1 Admin user
-- ✓ 3 Vehicle owners (1 pending, 2 active)
-- ✓ 5 Customers (3 active, 1 pending, 1 suspended)
-- ✓ 8 Vehicles (7 approved, 1 pending)
-- ✓ 4 Contact submissions (3 new, 1 read)
-- ✓ 7 Bookings (2 completed, 2 confirmed, 1 in_progress, 1 pending, 1 cancelled)
-- ✓ 6 Payments (5 completed, 1 refunded)
-- ✓ 4 Payouts (2 completed, 1 scheduled, 1 processing)
-- ✓ 3 Disputes (1 investigating, 1 resolved, 1 open)
--
-- Booking Details:
-- ----------------
-- ECH-2025-001: Mercedes S-Class Wedding (Completed, Paid) - $760
-- ECH-2025-002: Porsche 911 Corporate (Confirmed, Paid) - $1440
-- ECH-2025-003: BMW 7 Series Photoshoot (In Progress, Paid) - $630
-- ECH-2025-004: Ferrari F8 Special Occasion (Completed, Paid) - $1600
-- ECH-2025-005: Audi A8 Other (Pending, Pending Payment) - $540
-- ECH-2025-006: Lamborghini Huracán Wedding (Confirmed, Paid) - $1900
-- ECH-2025-007: Tesla Model S Corporate (Cancelled, Refunded) - $920
--
-- Notes:
-- ------
-- - All foreign key relationships use dynamic SELECT subqueries
-- - Data is safe to re-import (uses ON DUPLICATE KEY UPDATE where applicable)
-- - Booking references are unique and can be used to track relationships
-- - Commission rate is 15% on all bookings
-- - Payout amounts are calculated as: booking_total - commission_amount
