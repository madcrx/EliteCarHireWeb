-- Elite Car Hire - Comprehensive Sample Data
-- Run this AFTER importing cpanel_schema.sql and phase2_updates.sql
-- This file provides complete test data for all features

-- ==============================================================================
-- SAMPLE USERS
-- ==============================================================================
-- Password for all test users: password123

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

INSERT INTO vehicles (owner_id, make, model, year, category, hourly_rate, color, registration_number, features, status, created_at) VALUES
-- Owner 1's vehicles (James Smith)
(2, 'Mercedes-Benz', 'S-Class', 2023, 'luxury_exotic', 95.00, 'Black', 'ABC123-VIC', '["Leather seats", "Premium sound system", "Navigation", "Bluetooth", "Climate control"]', 'approved', '2025-01-06 10:00:00'),
(2, 'BMW', '7 Series', 2024, 'luxury_exotic', 105.00, 'Silver', 'DEF456-VIC', '["Heated seats", "Sunroof", "Advanced safety features", "Premium audio"]', 'approved', '2025-01-06 11:00:00'),
(2, 'Audi', 'A8', 2023, 'premium', 90.00, 'White', 'GHI789-VIC', '["Massage seats", "Premium audio", "Adaptive cruise control", "LED headlights"]', 'approved', '2025-01-07 09:00:00'),

-- Owner 2's vehicles (Emma Wilson)
(3, 'Porsche', '911 Carrera', 2024, 'luxury_exotic', 180.00, 'Red', 'JKL012-NSW', '["Sport exhaust", "Carbon fiber trim", "Track mode", "Sport seats"]', 'approved', '2025-01-08 14:00:00'),
(3, 'Ferrari', 'F8 Tributo', 2023, 'luxury_exotic', 320.00, 'Yellow', 'MNO345-NSW', '["V8 engine", "Carbon ceramic brakes", "Launch control", "Premium interior"]', 'approved', '2025-01-08 15:00:00'),
(3, 'Lamborghini', 'Huracán', 2024, 'luxury_exotic', 380.00, 'Orange', 'PQR678-NSW', '["AWD", "Carbon fiber bodykit", "Race mode", "Performance exhaust"]', 'approved', '2025-01-09 10:00:00'),
(3, 'Tesla', 'Model S Plaid', 2024, 'premium', 115.00, 'Blue', 'STU901-NSW', '["Autopilot", "Supercharging included", "0-100 in 2.1s", "Premium interior"]', 'pending', '2025-01-10 12:00:00'),

-- Owner 3's vehicles (Michael Brown - pending owner)
(4, 'Rolls-Royce', 'Ghost', 2023, 'luxury_exotic', 425.00, 'Black', 'VWX234-QLD', '["Starlight headliner", "Champagne cooler", "Bespoke audio", "Luxury seats"]', 'pending', '2025-01-11 11:00:00')
ON DUPLICATE KEY UPDATE make=make;

-- ==============================================================================
-- SAMPLE BOOKINGS
-- ==============================================================================

INSERT INTO bookings (booking_reference, customer_id, owner_id, vehicle_id, booking_date, start_date, end_date, pickup_location, dropoff_location, total_amount, status, payment_status, created_at) VALUES
-- Active bookings
('ECH20250120ABC123', 5, 2, 1, '2025-01-15 10:30:00', '2025-01-25', '2025-01-28', 'Melbourne Airport', 'Melbourne Airport', 1350.00, 'confirmed', 'paid', '2025-01-15 10:30:00'),
('ECH20250121DEF456', 6, 2, 2, '2025-01-16 14:20:00', '2025-01-26', '2025-01-27', 'Melbourne CBD', 'Melbourne CBD', 480.00, 'confirmed', 'paid', '2025-01-16 14:20:00'),
('ECH20250122GHI789', 7, 3, 4, '2025-01-17 09:15:00', '2025-02-01', '2025-02-03', 'Sydney Airport', 'Sydney Airport', 2550.00, 'confirmed', 'deposit', '2025-01-17 09:15:00'),

-- Completed bookings
('ECH20250105ABC001', 5, 2, 1, '2025-01-02 11:00:00', '2025-01-10', '2025-01-12', 'Melbourne CBD', 'Melbourne CBD', 900.00, 'completed', 'paid', '2025-01-02 11:00:00'),
('ECH20250106DEF002', 6, 3, 5, '2025-01-03 15:30:00', '2025-01-08', '2025-01-09', 'Sydney Airport', 'Sydney Airport', 1500.00, 'completed', 'paid', '2025-01-03 15:30:00'),
('ECH20250108GHI003', 7, 2, 3, '2025-01-05 10:00:00', '2025-01-12', '2025-01-14', 'Melbourne Airport', 'Melbourne Airport', 840.00, 'completed', 'paid', '2025-01-05 10:00:00'),
('ECH20250110JKL004', 5, 3, 6, '2025-01-07 13:45:00', '2025-01-13', '2025-01-14', 'Sydney CBD', 'Sydney CBD', 1800.00, 'completed', 'paid', '2025-01-07 13:45:00'),

-- Pending bookings
('ECH20250123ABC789', 6, 2, 2, '2025-01-20 16:00:00', '2025-02-05', '2025-02-08', 'Melbourne Airport', 'Melbourne CBD', 1440.00, 'pending', 'pending', '2025-01-20 16:00:00'),
('ECH20250124DEF012', 7, 3, 4, '2025-01-21 11:30:00', '2025-02-10', '2025-02-12', 'Sydney CBD', 'Sydney Airport', 2550.00, 'pending', 'pending', '2025-01-21 11:30:00'),

-- Cancelled bookings
('ECH20250115XYZ999', 5, 2, 1, '2025-01-10 09:00:00', '2025-01-18', '2025-01-20', 'Melbourne CBD', 'Melbourne CBD', 900.00, 'cancelled', 'refunded', '2025-01-10 09:00:00')
ON DUPLICATE KEY UPDATE booking_reference=booking_reference;

-- ==============================================================================
-- SAMPLE PAYMENTS
-- ==============================================================================

INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status, processed_at) VALUES
(1, 1350.00, 'credit_card', 'TXN-20250115-001', 'completed', '2025-01-15 10:35:00'),
(2, 480.00, 'credit_card', 'TXN-20250116-002', 'completed', '2025-01-16 14:25:00'),
(3, 850.00, 'credit_card', 'TXN-20250117-003', 'completed', '2025-01-17 09:20:00'),
(4, 900.00, 'credit_card', 'TXN-20250102-004', 'completed', '2025-01-02 11:05:00'),
(5, 1500.00, 'bank_transfer', 'TXN-20250103-005', 'completed', '2025-01-03 16:00:00'),
(6, 840.00, 'credit_card', 'TXN-20250105-006', 'completed', '2025-01-05 10:10:00'),
(7, 1800.00, 'credit_card', 'TXN-20250107-007', 'completed', '2025-01-07 13:50:00'),
(8, 480.00, 'credit_card', 'TXN-20250120-008', 'pending', NULL),
(9, 850.00, 'bank_transfer', 'TXN-20250121-009', 'pending', NULL)
ON DUPLICATE KEY UPDATE transaction_id=transaction_id;

-- ==============================================================================
-- SAMPLE PAYOUTS
-- ==============================================================================

INSERT INTO payouts (owner_id, amount, payout_date, method, reference, status, created_at) VALUES
(2, 2700.00, '2025-01-15', 'bank_transfer', 'PAYOUT-20250115-001', 'completed', '2025-01-14 10:00:00'),
(3, 3300.00, '2025-01-15', 'bank_transfer', 'PAYOUT-20250115-002', 'completed', '2025-01-14 10:30:00'),
(2, 1350.00, '2025-01-22', 'bank_transfer', 'PAYOUT-20250122-001', 'pending', '2025-01-21 09:00:00'),
(3, 2550.00, '2025-01-22', 'bank_transfer', 'PAYOUT-20250122-002', 'processing', '2025-01-21 09:30:00')
ON DUPLICATE KEY UPDATE reference=reference;

-- ==============================================================================
-- SAMPLE DISPUTES
-- ==============================================================================

INSERT INTO disputes (booking_id, raised_by, subject, description, status, priority, created_at, updated_at) VALUES
(1, 5, 'Vehicle damage claim', 'Customer claims there was existing damage to the front bumper that was not documented in the initial inspection.', 'open', 'high', '2025-01-16 10:00:00', '2025-01-16 10:00:00'),
(4, 2, 'Late return fees', 'Owner is disputing the refund of late return fees. Customer was only 15 minutes late due to traffic.', 'investigating', 'medium', '2025-01-14 14:30:00', '2025-01-18 09:00:00'),
(5, 6, 'Overcharged for fuel', 'Customer claims they filled the tank but were still charged $50 for fuel.', 'resolved', 'low', '2025-01-11 16:00:00', '2025-01-13 11:00:00')
ON DUPLICATE KEY UPDATE subject=subject;

-- ==============================================================================
-- SAMPLE PENDING CHANGES
-- ==============================================================================

INSERT INTO pending_changes (owner_id, entity_type, entity_id, change_type, old_data, new_data, reason, status, created_at) VALUES
(2, 'vehicle', 1, 'update', '{"daily_rate": 450.00, "features": "Leather seats, Premium sound system, Navigation, Bluetooth"}', '{"daily_rate": 475.00, "features": "Leather seats, Premium sound system, Navigation, Bluetooth, Heated steering wheel"}', 'Updated pricing and added feature', 'pending', '2025-01-19 10:00:00'),
(3, 'vehicle', 4, 'update', '{"daily_rate": 850.00, "color": "Red"}', '{"daily_rate": 900.00, "color": "Red"}', 'Price increase due to market demand', 'pending', '2025-01-20 11:00:00'),
(2, 'vehicle', 2, 'update', '{"weekly_rate": 3000.00}', '{"weekly_rate": 3200.00}', 'Seasonal price adjustment', 'approved', '2025-01-15 14:00:00')
ON DUPLICATE KEY UPDATE reason=reason;

-- ==============================================================================
-- SAMPLE CONTACT SUBMISSIONS
-- ==============================================================================

INSERT INTO contact_submissions (name, email, phone, subject, message, status, created_at) VALUES
('John Doe', 'john.doe@example.com', '0491 234 567', 'Inquiry about wedding car hire', 'Hi, I am interested in hiring a luxury vehicle for my wedding on March 15th. Do you offer chauffeur services? Please let me know the available options and pricing.', 'new', '2025-01-21 09:30:00'),
('Jane Smith', 'jane.smith@example.com', '0492 345 678', 'Corporate rental inquiry', 'We are looking to hire multiple vehicles for our corporate event in February. We need 5 luxury sedans for 3 days. Can you provide a quote?', 'new', '2025-01-22 14:15:00'),
('Mark Wilson', 'mark.wilson@example.com', '0493 456 789', 'Question about insurance', 'What insurance coverage is included with your rentals? Do you offer additional coverage options?', 'read', '2025-01-20 11:00:00'),
('Sarah Brown', 'sarah.brown@example.com', '0494 567 890', 'Feedback on recent rental', 'I recently rented a Mercedes S-Class and had an excellent experience! The vehicle was in pristine condition and the service was outstanding. Will definitely rent again.', 'responded', '2025-01-18 16:45:00')
ON DUPLICATE KEY UPDATE subject=subject;

-- Update responded submission
UPDATE contact_submissions
SET response_text = 'Thank you for your wonderful feedback! We are delighted to hear that you enjoyed your experience with Elite Car Hire. We look forward to serving you again in the future. Please don\'t hesitate to contact us for your next rental needs.',
    responded_at = '2025-01-19 09:00:00',
    responded_by = 1,
    status = 'responded'
WHERE email = 'sarah.brown@example.com';

-- ==============================================================================
-- SAMPLE NOTIFICATIONS
-- ==============================================================================

INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) VALUES
(2, 'booking', 'New Booking', 'You have a new booking for Mercedes-Benz S-Class', '/owner/bookings', FALSE, '2025-01-15 10:30:00'),
(2, 'payment', 'Payment Received', 'Payment of $1350.00 received for booking ECH20250120ABC123', '/owner/bookings', TRUE, '2025-01-15 10:35:00'),
(3, 'booking', 'New Booking', 'You have a new booking for Porsche 911 Carrera', '/owner/bookings', FALSE, '2025-01-17 09:15:00'),
(5, 'approval', 'Booking Confirmed', 'Your booking ECH20250120ABC123 has been confirmed', '/customer/bookings', TRUE, '2025-01-15 11:00:00'),
(6, 'approval', 'Booking Confirmed', 'Your booking ECH20250121DEF456 has been confirmed', '/customer/bookings', TRUE, '2025-01-16 15:00:00'),
(4, 'approval', 'Account Pending', 'Your owner account is pending admin approval', NULL, FALSE, '2025-01-11 11:30:00')
ON DUPLICATE KEY UPDATE message=message;

-- ==============================================================================
-- SAMPLE AUDIT LOGS
-- ==============================================================================

INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, created_at) VALUES
(1, 'login', 'users', 1, '203.0.113.1', 'Mozilla/5.0', '2025-01-22 09:00:00'),
(1, 'approve_user', 'users', 2, '203.0.113.1', 'Mozilla/5.0', '2025-01-06 09:30:00'),
(1, 'approve_vehicle', 'vehicles', 1, '203.0.113.1', 'Mozilla/5.0', '2025-01-06 11:00:00'),
(2, 'create_vehicle', 'vehicles', 1, '203.0.113.2', 'Mozilla/5.0', '2025-01-06 10:00:00'),
(5, 'create_booking', 'bookings', 1, '203.0.113.5', 'Mozilla/5.0', '2025-01-15 10:30:00'),
(1, 'update_setting', 'settings', NULL, '203.0.113.1', 'Mozilla/5.0', '2025-01-20 15:00:00')
ON DUPLICATE KEY UPDATE action=action;

-- ==============================================================================
-- SUMMARY
-- ==============================================================================
-- This script has created:
-- - 9 users (1 admin, 3 owners, 5 customers) with various statuses
-- - 8 vehicles across different categories and states
-- - 10 bookings (confirmed, pending, completed, cancelled)
-- - 9 payments (completed and pending)
-- - 4 payouts (completed, pending, processing)
-- - 3 disputes (open, investigating, resolved)
-- - 3 pending changes
-- - 4 contact submissions
-- - 6 notifications
-- - 6 audit log entries
--
-- All passwords are: password123
-- All data uses realistic Australian phone numbers and locations
-- ==============================================================================
