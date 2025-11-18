-- Elite Car Hire Complete Database Schema
-- Created: November 2025

CREATE DATABASE IF NOT EXISTS elite_car_hire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE elite_car_hire;

-- Users Table (Admin, Owner, Customer)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'owner', 'customer') NOT NULL,
    status ENUM('pending', 'active', 'suspended', 'rejected') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Vehicles Table
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(50),
    category ENUM('classic_muscle', 'luxury_exotic', 'premium', 'other') NOT NULL,
    description TEXT,
    hourly_rate DECIMAL(10, 2) NOT NULL,
    minimum_hours INT DEFAULT 4,
    max_passengers INT DEFAULT 4,
    features JSON,
    status ENUM('pending', 'approved', 'active', 'inactive', 'rejected') DEFAULT 'pending',
    registration_number VARCHAR(20),
    insurance_policy VARCHAR(100),
    insurance_expiry DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Vehicle Images
CREATE TABLE vehicle_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle (vehicle_id)
) ENGINE=InnoDB;

-- Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    owner_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration_hours DECIMAL(4, 2) NOT NULL,
    pickup_location VARCHAR(255),
    dropoff_location VARCHAR(255),
    event_type ENUM('wedding', 'corporate', 'photoshoot', 'special_occasion', 'other'),
    special_requirements TEXT,
    base_amount DECIMAL(10, 2) NOT NULL,
    additional_charges DECIMAL(10, 2) DEFAULT 0.00,
    toll_charges DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    commission_rate DECIMAL(5, 2) DEFAULT 15.00,
    commission_amount DECIMAL(10, 2),
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (owner_id) REFERENCES users(id),
    INDEX idx_booking_ref (booking_reference),
    INDEX idx_customer (customer_id),
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_owner (owner_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer'),
    card_last_four VARCHAR(4),
    card_brand VARCHAR(20),
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    refund_amount DECIMAL(10, 2) DEFAULT 0.00,
    refund_date TIMESTAMP NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    INDEX idx_booking (booking_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Payouts Table
CREATE TABLE payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    booking_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'scheduled', 'processing', 'completed', 'failed') DEFAULT 'pending',
    scheduled_date DATE,
    payout_date TIMESTAMP NULL,
    bank_account VARCHAR(100),
    reference VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_scheduled_date (scheduled_date)
) ENGINE=InnoDB;

-- Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    owner_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    response_text TEXT,
    response_date TIMESTAMP NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (owner_id) REFERENCES users(id),
    INDEX idx_booking (booking_id),
    INDEX idx_vehicle (vehicle_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Messages Table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    booking_id INT,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    INDEX idx_from_user (from_user_id),
    INDEX idx_to_user (to_user_id),
    INDEX idx_booking (booking_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB;

-- Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Pending Changes Table
CREATE TABLE pending_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    entity_type ENUM('vehicle', 'profile', 'listing') NOT NULL,
    entity_id INT,
    change_type ENUM('create', 'update', 'delete') NOT NULL,
    current_data JSON,
    new_data JSON NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- Contact Submissions Table
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'responded', 'archived') DEFAULT 'new',
    responded_by INT,
    responded_at TIMESTAMP NULL,
    response_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responded_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    old_values JSON,
    new_values JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- Security Alerts Table
CREATE TABLE security_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_at TIMESTAMP NULL,
    resolved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_severity (severity),
    INDEX idx_is_resolved (is_resolved)
) ENGINE=InnoDB;

-- CMS Pages Table
CREATE TABLE cms_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_page_key (page_key),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB;

-- Calendar Events Table
CREATE TABLE calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    location VARCHAR(255),
    event_type ENUM('booking', 'maintenance', 'personal', 'other') DEFAULT 'other',
    color VARCHAR(7) DEFAULT '#C5A253',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_start_datetime (start_datetime),
    INDEX idx_booking (booking_id)
) ENGINE=InnoDB;

-- Disputes Table
CREATE TABLE disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    raised_by INT NOT NULL,
    dispute_type ENUM('service_quality', 'damage', 'payment', 'cancellation', 'other') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    resolution TEXT,
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (raised_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    INDEX idx_booking (booking_id),
    INDEX idx_raised_by (raised_by),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Email Queue Table
CREATE TABLE email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    to_name VARCHAR(100),
    subject VARCHAR(255) NOT NULL,
    body_html LONGTEXT,
    body_text TEXT,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    last_attempt TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Insert default admin user (password: Admin123!)
INSERT INTO users (email, password, first_name, last_name, role, status, email_verified) 
VALUES ('admin@elitecarhire.au', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active', TRUE);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'Elite Car Hire', 'string'),
('site_email', 'info@elitecarhire.au', 'string'),
('site_phone', '1300 324 473', 'string'),
('company_address', 'Melbourne, VIC, Australia', 'string'),
('commission_rate', '15.00', 'decimal'),
('minimum_booking_hours', '4', 'integer'),
('auto_approve_customers', '1', 'boolean'),
('currency', 'AUD', 'string'),
('timezone', 'Australia/Melbourne', 'string');

-- Insert CMS Pages with full Terms of Service and Privacy Policy
INSERT INTO cms_pages (page_key, title, content, status) VALUES
('home', 'Welcome to Elite Car Hire', '<h1>Luxury Vehicle Hire in Melbourne</h1><p>Experience the finest in luxury and classic muscle car hire for your special occasion.</p>', 'published'),

('about', 'About Us', '<h1>About Elite Car Hire</h1><p>We are Melbourne''s premier luxury vehicle hire service, specializing in classic muscle cars and luxury exotic vehicles for weddings, corporate events, photo shoots, and special occasions.</p>', 'published'),

('faq', 'Frequently Asked Questions', '<h1>FAQ</h1><h3>What is the minimum hire period?</h3><p>Most vehicles have a minimum hire period of 4 hours.</p><h3>What areas do you service?</h3><p>We service Melbourne and regional Victoria. Interstate travel requires prior approval.</p>', 'published'),

('support', 'Support', '<h1>Customer Support</h1><p>Need help? Contact us at 1300 324 473 or email info@elitecarhire.au</p>', 'published'),

('terms', 'Terms of Service', '<h1>Terms of Service</h1>
<p><strong>Last Updated: November 2025</strong></p>

<p>Welcome to Elite Car Hire. These Terms of Service ("Terms") govern your use of our luxury vehicle hire services and website. By accessing or using our services, you agree to be bound by these Terms. Please read them carefully.</p>

<h2>Service Overview</h2>
<p>Elite Car Hire operates as a premium booking platform connecting customers with professional chauffeurs and luxury vehicle owners. We specialise in classic muscle cars and luxury exotic vehicles for weddings, corporate events, photo shoots, and special occasions.</p>

<p>We act as an intermediary between customers and vehicle owners, facilitating bookings and ensuring quality service standards. The actual provision of chauffeured services is performed by independent vehicle owners who meet our stringent quality requirements.</p>

<h2>Booking and Reservations</h2>
<h3>Booking Process</h3>
<ul>
<li>All bookings must be made through our website or authorised channels</li>
<li>You must provide accurate and complete information</li>
<li>Bookings are subject to vehicle availability</li>
<li>Confirmation emails contain your booking reference and details</li>
</ul>

<h3>Minimum Requirements</h3>
<ul>
<li>Customers must be at least 18 years of age</li>
<li>Credit card for payment</li>
<li>Most vehicles have a minimum hire period of 4 hours</li>
</ul>

<h2>Payment Terms</h2>
<h3>Payment Methods</h3>
<p>We accept major credit cards (Visa, Mastercard, American Express) and debit cards. Full payment is required at the time of booking unless otherwise agreed.</p>

<h3>Additional Charges</h3>
<ul>
<li>Extended hours beyond the booked period</li>
<li>Additional stops or route changes not included in original booking</li>
<li>Toll charges incurred during hire</li>
</ul>

<h2>Cancellation and Refund Policy</h2>
<h3>Customer Cancellations</h3>
<ul>
<li>More than 14 days before hire: Full refund minus 10% administration fee</li>
<li>7-14 days before hire: 50% refund</li>
<li>Less than 7 days before hire: No refund</li>
<li>No-show: No refund and full charges apply</li>
</ul>

<h3>Elite Car Hire Cancellations</h3>
<p>In the unlikely event we must cancel your booking due to circumstances beyond our control (vehicle breakdown, chauffeur illness), we will offer a suitable replacement vehicle or provide a full refund. We are not liable for consequential losses or expenses.</p>

<h2>Vehicle Use and Responsibilities</h2>
<h3>Service Use</h3>
<ul>
<li>Services must be used only for the stated purpose</li>
<li>Metropolitan and regional travel is generally permitted</li>
<li>Interstate travel requires prior written approval</li>
<li>Illegal activities are strictly forbidden</li>
</ul>

<h3>Customer Responsibilities</h3>
<ul>
<li>Treat the vehicle with care and respect</li>
<li>Report any issues immediately to the chauffeur or our support team</li>
<li>No smoking in vehicles unless explicitly permitted</li>
<li>Supervise passengers, especially children</li>
</ul>

<h2>Insurance and Liability</h2>
<p>All vehicles are covered by comprehensive insurance. Our professional chauffeurs are fully insured and licensed.</p>

<h3>Customer Liability</h3>
<p>Customers are liable for:</p>
<ul>
<li>Damage caused by intentional misconduct or negligence</li>
<li>Interior damage, stains, or excessive mess requiring professional cleaning</li>
<li>Fines or penalties resulting from customer actions during hire</li>
</ul>

<h2>Chauffeur Services</h2>
<p>Our chauffeur-driven services include a professional, experienced driver with comprehensive knowledge of luxury vehicles and customer service excellence.</p>

<h3>Service Standards</h3>
<ul>
<li>Chauffeurs arrive punctually and professionally attired</li>
<li>All chauffeurs hold appropriate licences and insurance</li>
<li>Vehicles are presented in immaculate condition</li>
<li>Chauffeurs follow your itinerary and schedule</li>
<li>Gratuities are appreciated but not mandatory</li>
</ul>

<h2>Vehicle Owner Terms</h2>
<h3>Owner Requirements</h3>
<ul>
<li>Vehicles must be maintained in excellent condition</li>
<li>Comprehensive insurance coverage required</li>
<li>Regular safety inspections and servicing</li>
<li>Professional presentation and cleanliness</li>
<li>Compliance with all local regulations</li>
</ul>

<h3>Commission Structure</h3>
<p>Elite Car Hire operates on a commission basis. Vehicle owners set their own hire rates, and we receive an agreed commission percentage for facilitating bookings and providing the platform.</p>

<h2>Dispute Resolution</h2>
<p>Any disputes arising from these Terms or our services should first be raised with our customer service team. We are committed to fair and prompt resolution. If a dispute cannot be resolved amicably, it will be governed by the laws of Victoria, Australia, and parties submit to the exclusive jurisdiction of Victorian courts.</p>

<h2>Privacy and Data Protection</h2>
<p>Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy. By using our services, you consent to data collection and use as described in our Privacy Policy.</p>

<h2>Limitation of Liability</h2>
<p>To the maximum extent permitted by law, Elite Car Hire is not liable for indirect, incidental, consequential, or punitive damages arising from use of our services. Our total liability is limited to the amount paid for the specific booking in question.</p>

<h2>Changes to Terms</h2>
<p>We reserve the right to modify these Terms at any time. Changes will be posted on our website with an updated "Last Updated" date. Continued use of our services after changes constitutes acceptance of modified Terms.</p>

<h2>Contact Information</h2>
<p>For questions about these Terms of Service, please contact us:</p>
<p><strong>Elite Car Hire</strong><br>
Email: info@elitecarhire.au<br>
Phone: 1300 ECHIRE (1300 324 473)<br>
Address: Melbourne, VIC, Australia</p>', 'published'),

('privacy', 'Privacy Policy', '<h1>Privacy Policy</h1>
<p><strong>Last Updated: November 2025</strong></p>

<p>Elite Car Hire ("we," "us," or "our") is committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy outlines how we collect, use, disclose, and safeguard your information when you use our luxury vehicle hire services.</p>

<h2>Information We Collect</h2>
<h3>Personal Information</h3>
<p>When you book our services or create an account, we may collect:</p>
<ul>
<li>Full name and contact details (email address, phone number)</li>
<li>Payment information and billing address</li>
<li>Event details and special requirements</li>
<li>Communications and correspondence with us</li>
</ul>

<h3>Automatically Collected Information</h3>
<p>We automatically collect certain information when you visit our website:</p>
<ul>
<li>Device information and IP address</li>
<li>Browser type and version</li>
<li>Pages visited and time spent on our site</li>
<li>Referring website addresses</li>
</ul>

<h2>How We Use Your Information</h2>
<p>We use your information to:</p>
<ul>
<li>Process and manage your vehicle hire bookings</li>
<li>Verify your identity and contact information</li>
<li>Process payments and prevent fraud</li>
<li>Communicate with you about bookings, updates, and service improvements</li>
<li>Provide customer support and respond to enquiries</li>
<li>Improve our services and website functionality</li>
<li>Send promotional materials (with your consent)</li>
<li>Comply with legal obligations and protect our rights</li>
</ul>

<h2>Information Sharing and Disclosure</h2>
<p>We do not sell your personal information. We may share your information with:</p>

<h3>Vehicle Owners</h3>
<p>We share necessary booking details with the chauffeurs and vehicle owners providing the service.</p>

<h3>Service Providers</h3>
<p>Third-party companies assisting with payment processing, website hosting, and communication services.</p>

<h3>Legal Requirements</h3>
<p>When required by law, court order, or to protect our rights and safety.</p>

<h3>Business Transfers</h3>
<p>In the event of a merger, acquisition, or sale of assets.</p>

<h2>Data Security</h2>
<p>We implement appropriate technical and organisational security measures to protect your personal information from unauthorised access, disclosure, alteration, or destruction. This includes encrypted data transmission, secure servers, and restricted access to personal information. However, no internet transmission is completely secure, and we cannot guarantee absolute security.</p>

<h2>Data Retention</h2>
<p>We retain your personal information for as long as necessary to fulfil the purposes outlined in this policy, comply with legal obligations, resolve disputes, and enforce our agreements. Booking records are typically retained for 7 years to meet tax and legal requirements.</p>

<h2>Your Rights</h2>
<p>Under Australian privacy law, you have the right to:</p>
<ul>
<li>Access your personal information</li>
<li>Request correction of inaccurate information</li>
<li>Request deletion of your information (subject to legal requirements)</li>
<li>Opt-out of marketing communications</li>
<li>Lodge a complaint with the Office of the Australian Information Commissioner</li>
</ul>

<p>To exercise these rights, please contact us at privacy@elitecarhire.au</p>

<h2>Cookies and Tracking Technologies</h2>
<p>We use cookies and similar tracking technologies to enhance your browsing experience, analyse website traffic, and understand user preferences. You can control cookie settings through your browser preferences, though disabling cookies may affect website functionality.</p>

<h2>Third-Party Links</h2>
<p>Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of these external sites. We encourage you to review their privacy policies before providing any personal information.</p>

<h2>Children''s Privacy</h2>
<p>Our services are not intended for individuals under 18 years of age. We do not knowingly collect personal information from children. All customers must be at least 18 years old.</p>

<h2>Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of significant changes by posting the updated policy on our website with a revised "Last Updated" date. Your continued use of our services after changes constitutes acceptance of the updated policy.</p>

<h2>Contact Us</h2>
<p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us:</p>

<p><strong>Elite Car Hire</strong><br>
Email: privacy@elitecarhire.au<br>
Phone: 1300 ECHIRE (1300 324 473)<br>
Address: Melbourne, VIC, Australia</p>', 'published');
