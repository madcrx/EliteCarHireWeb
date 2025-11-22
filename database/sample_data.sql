-- Elite Car Hire - Sample Data for Testing
-- Run this AFTER importing cpanel_schema.sql

-- Insert or update sample CMS pages (skips if page_key already exists)
INSERT INTO cms_pages (page_key, title, content, status, created_at, updated_at) VALUES
('terms', 'Terms of Service', '<h2>Terms and Conditions</h2><p>Welcome to Elite Car Hire. By using our services, you agree to these terms...</p><h3>1. Rental Agreement</h3><p>All vehicle rentals are subject to availability and our standard rental agreement.</p><h3>2. Driver Requirements</h3><ul><li>Valid driver''s license (held for minimum 2 years)</li><li>Minimum age 25 years</li><li>Clean driving record</li></ul><h3>3. Insurance</h3><p>All vehicles are comprehensively insured. Excess applies as per rental agreement.</p>', 'published', NOW(), NOW()),
('privacy', 'Privacy Policy', '<h2>Privacy Policy</h2><p>Elite Car Hire is committed to protecting your privacy...</p><h3>Information We Collect</h3><p>We collect information necessary to provide our services including name, contact details, and payment information.</p><h3>How We Use Your Information</h3><ul><li>Process bookings and payments</li><li>Communicate about your rental</li><li>Improve our services</li></ul><h3>Data Security</h3><p>We implement appropriate security measures to protect your personal information.</p>', 'published', NOW(), NOW()),
('faq', 'Frequently Asked Questions', '<h2>Frequently Asked Questions</h2><h3>What are your rental rates?</h3><p>Our rates vary by vehicle and duration. Please view our fleet for specific pricing.</p><h3>What is included in the rental?</h3><p>Insurance, basic fuel, and standard mileage allowance are included.</p><h3>Can I hire a chauffeur?</h3><p>Yes, professional chauffeur services are available for all vehicles at an additional cost.</p><h3>What is your cancellation policy?</h3><p>Cancellations made 48 hours prior to rental date receive a full refund.</p>', 'published', NOW(), NOW()),
('about', 'About Us', 'This page is managed through the database. Content can be updated via Admin Dashboard > CMS.', 'published', NOW(), NOW()),
('services', 'Our Services', 'This page is managed through the database. Content can be updated via Admin Dashboard > CMS.', 'published', NOW(), NOW()),
('support', 'Support', 'This page redirects to Contact. Managed via routing.', 'published', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    content = VALUES(content),
    status = VALUES(status),
    updated_at = NOW();

-- ============================================================================
-- SAMPLE VEHICLES
-- ============================================================================
-- IMPORTANT: Before running this section:
-- 1. First create Owner user accounts via Admin Dashboard > User Management
-- 2. Note the user IDs of those owner accounts
-- 3. Replace the owner_id values below (currently 1, 2, 3) with actual IDs
-- 4. Add state values (e.g., 'Victoria', 'New South Wales', etc.) if using location filters
-- ============================================================================

-- STEP 1: Find your owner user IDs by running this query:
-- SELECT id, first_name, last_name, email FROM users WHERE role = 'owner';

-- STEP 2: Update the owner_id values below with actual user IDs, then run:

-- Uncomment and update these lines after creating owner accounts:
/*
INSERT INTO vehicles (owner_id, make, model, year, color, category, description, hourly_rate, minimum_hours, max_passengers, features, status, registration_number, state, created_at, updated_at) VALUES
(1, 'Ford', 'Mustang GT', 1969, 'Royal Blue', 'classic_muscle', 'Iconic 1969 Ford Mustang GT in stunning royal blue. This classic American muscle car features the original V8 engine, manual transmission, and has been meticulously restored to showroom condition. Perfect for weddings, photo shoots, and special events.', 150.00, 4, 4, '{"engine": "V8 5.0L", "transmission": "Manual", "features": ["Air Conditioning", "Power Steering", "Chrome Details", "Leather Interior"]}', 'approved', 'MUST69', 'Victoria', NOW(), NOW()),
(1, 'Holden', 'Monaro GTS', 1971, 'Volcanic Orange', 'classic_muscle', 'Legendary 1971 Holden Monaro GTS in volcanic orange. One of Australia''s most iconic muscle cars, featuring the powerful 308 V8 engine. Fully restored with original HQ specifications. A true piece of Australian automotive history.', 180.00, 4, 5, '{"engine": "308 V8", "transmission": "Manual 4-speed", "features": ["Original GTS Gauges", "Bench Seats", "Period Correct Wheels"]}', 'approved', 'MONO71', 'Victoria', NOW(), NOW()),
(2, 'Chevrolet', 'Camaro SS', 1970, 'Candy Apple Red', 'classic_muscle', 'Stunning 1970 Chevrolet Camaro SS in candy apple red. This American muscle car icon features the big block V8, and has undergone a complete professional restoration. Chrome bumpers and original SS badges make this a real head-turner.', 165.00, 4, 4, '{"engine": "396 V8", "transmission": "Automatic", "features": ["Power Brakes", "Rally Wheels", "Console Gauges", "Bucket Seats"]}', 'approved', 'CAMA70', 'New South Wales', NOW(), NOW()),
(2, 'Lamborghini', 'Huracan', 2020, 'Arancio Borealis (Orange)', 'luxury_exotic', 'Exhilarating 2020 Lamborghini Huracan in stunning Arancio Borealis orange. This supercar delivers 630hp of pure adrenaline with its naturally aspirated V10 engine. Perfect for special occasions and unforgettable experiences.', 500.00, 4, 2, '{"engine": "V10 5.2L", "transmission": "7-speed Dual Clutch", "features": ["Carbon Ceramic Brakes", "Dynamic Steering", "Launch Control", "Magnetic Ride"]}', 'approved', 'HURA20', 'New South Wales', NOW(), NOW()),
(3, 'Ferrari', '488 GTB', 2019, 'Rosso Corsa (Red)', 'luxury_exotic', 'Magnificent 2019 Ferrari 488 GTB in classic Rosso Corsa red. Experience Italian supercar excellence with 660hp twin-turbo V8. Features include premium leather interior, carbon fiber accents, and Ferrari''s latest performance technology.', 550.00, 4, 2, '{"engine": "V8 3.9L Twin-Turbo", "transmission": "7-speed F1 Dual Clutch", "features": ["Side Slip Control", "E-Diff", "F1-Trac", "SCM-E Suspension"]}', 'approved', 'F488GTB', 'Queensland', NOW(), NOW()),
(3, 'Rolls-Royce', 'Phantom', 2021, 'Diamond Black', 'luxury_exotic', 'The ultimate in luxury - 2021 Rolls-Royce Phantom in diamond black. Perfect for weddings and VIP events. Features include starlight headliner, premium leather, and the smoothest ride in automotive history. Chauffeur service recommended.', 600.00, 4, 5, '{"engine": "V12 6.75L", "transmission": "8-speed Automatic", "features": ["Starlight Headliner", "Rear Entertainment", "Refrigerator", "Privacy Glass", "Massage Seats"]}', 'approved', 'PHANT21', 'Queensland', NOW(), NOW());
*/

-- ============================================================================
-- SAMPLE CONTACT SUBMISSIONS
-- ============================================================================
-- These are safe to run multiple times (creates new submissions each time)

INSERT INTO contact_submissions (name, email, phone, subject, message, created_at) VALUES
('Sarah Johnson', 'sarah.j@email.com', '0412 345 678', 'Wedding Car Hire', 'Hi, I''m interested in hiring a classic car for my wedding on March 15th. Can you please provide pricing and availability for the Mustang?', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Michael Chen', 'mchen@company.com', '0423 456 789', 'Corporate Event', 'We need luxury vehicles for a corporate event with 10 executives. Do you offer packages for multiple vehicles?', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Emma Wilson', 'emma.wilson@photo.com', '0434 567 890', 'Photo Shoot Inquiry', 'Looking to hire classic muscle cars for a commercial shoot. Need vehicles for 2 days. What are your rates?', NOW());

COMMIT;
