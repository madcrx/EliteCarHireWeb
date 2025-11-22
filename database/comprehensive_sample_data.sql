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
-- This simplified version includes:
-- - 1 Admin user
-- - 3 Vehicle owners (1 pending, 2 active)
-- - 5 Customers (3 active, 1 pending, 1 suspended)
-- - 8 Vehicles (7 approved, 1 pending)
-- - 4 Contact submissions (3 new, 1 read)
--
-- You can manually add bookings, payments, disputes, etc. through the admin interface
-- after these users and vehicles are created.
