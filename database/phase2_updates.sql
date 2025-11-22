-- Elite Car Hire - Phase 2 Database Updates
-- Run this AFTER cpanel_schema.sql and sample_data.sql

-- Add state field to vehicles table for location filtering
ALTER TABLE vehicles ADD COLUMN state VARCHAR(50) DEFAULT 'VIC' AFTER color;
ALTER TABLE vehicles ADD INDEX idx_state (state);

-- Create site_images table for image management
CREATE TABLE IF NOT EXISTS site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_key VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    default_image_path VARCHAR(255),
    image_type ENUM('logo', 'hero', 'banner', 'feature', 'other') DEFAULT 'other',
    is_active BOOLEAN DEFAULT TRUE,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_image_key (image_key),
    INDEX idx_image_type (image_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Create vehicle_blocked_dates table for owner calendar management
CREATE TABLE IF NOT EXISTS vehicle_blocked_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    owner_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255),
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_pattern VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_vehicle_dates (vehicle_id, start_date, end_date),
    INDEX idx_owner (owner_id)
) ENGINE=InnoDB;

-- Insert default site images (placeholders)
INSERT INTO site_images (image_key, title, description, image_path, default_image_path, image_type, is_active) VALUES
('logo_header', 'Header Logo', 'Company logo displayed in navigation header', '/assets/images/logo.png', '/assets/images/logo-default.png', 'logo', TRUE),
('logo_footer', 'Footer Logo', 'Company logo displayed in footer', '/assets/images/logo-footer.png', '/assets/images/logo-footer-default.png', 'logo', TRUE),
('hero_home', 'Homepage Hero Image', 'Main banner image on homepage', '/assets/images/hero-home.jpg', '/assets/images/hero-default.jpg', 'hero', TRUE),
('banner_services', 'Services Page Banner', 'Banner image for services page', '/assets/images/banner-services.jpg', '/assets/images/banner-default.jpg', 'banner', TRUE),
('banner_about', 'About Page Banner', 'Banner image for about page', '/assets/images/banner-about.jpg', '/assets/images/banner-default.jpg', 'banner', TRUE);

-- Update existing vehicles with states
UPDATE vehicles SET state = 'VIC' WHERE id > 0;

COMMIT;
