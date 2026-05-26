-- ============================================
-- Agriculture Marketplace Database Schema
-- ============================================
-- Created: 2026-02-17
-- Description: Complete database schema for agriculture marketplace platform
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS agriculture_portal;
USE agriculture_portal;

-- ============================================
-- Table: farmers
-- Description: Stores farmer registration and profile information
-- ============================================
CREATE TABLE farmers (
    farmer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    address TEXT,
    district VARCHAR(50),
    state VARCHAR(50),
    farm_size DECIMAL(10, 2) COMMENT 'Farm size in acres',
    main_crops TEXT COMMENT 'Comma-separated list of main crops',
    password VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(255) COMMENT 'Path to profile photo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_district (district),
    INDEX idx_state (state),
    INDEX idx_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: customers
-- Description: Stores customer registration and profile information
-- ============================================
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    INDEX idx_state (state),
    INDEX idx_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: crops
-- Description: Stores crop listings posted by farmers
-- ============================================
CREATE TABLE crops (
    crop_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL COMMENT 'Quantity in kg',
    price_per_kg DECIMAL(10, 2) NOT NULL,
    harvest_date DATE,
    description TEXT,
    image VARCHAR(255) COMMENT 'Path to crop image',
    status ENUM('available', 'sold', 'reserved', 'expired') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_farmer_id (farmer_id),
    INDEX idx_crop_name (crop_name),
    INDEX idx_status (status),
    INDEX idx_harvest_date (harvest_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: orders
-- Description: Stores order transactions between customers and farmers
-- ============================================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    farmer_id INT NOT NULL,
    crop_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL COMMENT 'Quantity ordered in kg',
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status VARCHAR(50) DEFAULT 'Pending',
    order_status VARCHAR(50) DEFAULT 'Placed',
    delivery_address TEXT,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_customer_id (customer_id),
    INDEX idx_farmer_id (farmer_id),
    INDEX idx_crop_id (crop_id),
    INDEX idx_order_date (order_date),
    INDEX idx_payment_status (payment_status),
    INDEX idx_order_status (order_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Additional Useful Views
-- ============================================

-- View: Active crop listings with farmer details
CREATE OR REPLACE VIEW active_crops_view AS
SELECT 
    c.crop_id,
    c.crop_name,
    c.quantity,
    c.price_per_kg,
    c.harvest_date,
    c.description,
    c.image,
    f.farmer_id,
    f.name AS farmer_name,
    f.district,
    f.state,
    f.mobile AS farmer_mobile
FROM crops c
INNER JOIN farmers f ON c.farmer_id = f.farmer_id
WHERE c.status = 'available';

-- ============================================
-- Table: admin_activity_logs
-- Description: Stores admin action history
-- ============================================
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description TEXT,
    target_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- View: Order summary with all details
CREATE OR REPLACE VIEW order_details_view AS
SELECT 
    o.order_id,
    o.quantity,
    o.total_amount,
    o.order_date,
    o.payment_status,
    o.payment_method,
    o.order_status,
    o.delivered_at,
    c.customer_id,
    c.name AS customer_name,
    c.mobile AS customer_mobile,
    c.city AS customer_city,
    f.farmer_id,
    f.name AS farmer_name,
    f.district AS farmer_district,
    cr.crop_id,
    cr.crop_name,
    cr.price_per_kg
FROM orders o
INNER JOIN customers c ON o.customer_id = c.customer_id
INNER JOIN farmers f ON o.farmer_id = f.farmer_id
INNER JOIN crops cr ON o.crop_id = cr.crop_id;

-- ============================================
-- Sample Data (Optional - for testing)
-- ============================================

-- Insert sample farmer
INSERT INTO farmers (name, mobile, email, address, district, state, farm_size, main_crops, password) 
VALUES 
('Rajesh Kumar', '9876543210', 'rajesh@example.com', 'Village Road, Thanjavur', 'Thanjavur', 'Tamil Nadu', 5.5, 'Rice, Sugarcane', '$2y$10$example_hashed_password');

-- Insert sample customer
INSERT INTO customers (name, mobile, email, address, city, state, pincode, password) 
VALUES 
('Priya Sharma', '9123456789', 'priya@example.com', '123 Main Street', 'Chennai', 'Tamil Nadu', '600001', '$2y$10$example_hashed_password');

-- Insert sample crop
INSERT INTO crops (farmer_id, crop_name, quantity, price_per_kg, harvest_date, description, status) 
VALUES 
(1, 'Organic Rice', 500.00, 45.00, '2026-02-15', 'Premium quality organic rice', 'available');

-- ============================================
-- End of Schema
-- ============================================
