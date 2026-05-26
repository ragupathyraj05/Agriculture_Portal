-- ============================================
-- Migration V2: Order System & Activity Logs
-- ============================================
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================

USE agriculture_portal;

-- ============================================
-- 1. Alter orders table: payment_status ENUM → VARCHAR
-- ============================================
ALTER TABLE orders 
    MODIFY COLUMN payment_status VARCHAR(50) DEFAULT 'Pending';

-- ============================================
-- 2. Alter orders table: delivery_status → order_status VARCHAR
-- ============================================
ALTER TABLE orders 
    CHANGE COLUMN delivery_status order_status VARCHAR(50) DEFAULT 'Placed';

-- ============================================
-- 3. Add delivered_at timestamp
-- ============================================
ALTER TABLE orders 
    ADD COLUMN delivered_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at;

-- ============================================
-- 4. Migrate existing data to new status values
-- ============================================
-- Payment status migration
UPDATE orders SET payment_status = 'Pending' WHERE payment_status = 'pending';
UPDATE orders SET payment_status = 'Paid' WHERE payment_status = 'paid';
UPDATE orders SET payment_status = 'Failed' WHERE payment_status = 'failed';
UPDATE orders SET payment_status = 'Refunded' WHERE payment_status = 'refunded';

-- Order status migration  
UPDATE orders SET order_status = 'Placed' WHERE order_status = 'pending';
UPDATE orders SET order_status = 'Approved' WHERE order_status = 'confirmed';
UPDATE orders SET order_status = 'Shipped' WHERE order_status = 'shipped';
UPDATE orders SET order_status = 'Delivered' WHERE order_status = 'delivered';
UPDATE orders SET order_status = 'Cancelled' WHERE order_status = 'cancelled';

-- Update COD orders that are not yet delivered
UPDATE orders 
    SET payment_status = 'COD - Awaiting Delivery' 
    WHERE payment_method = 'COD' 
    AND order_status != 'Delivered' 
    AND order_status != 'Cancelled'
    AND payment_status = 'Pending';

-- Update COD orders that are already delivered
UPDATE orders 
    SET payment_status = 'Confirmed (COD Collected)',
        delivered_at = updated_at
    WHERE payment_method = 'COD' 
    AND order_status = 'Delivered';

-- ============================================
-- 5. Create admin_activity_logs table
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

-- ============================================
-- 6. Update the order_details_view
-- ============================================
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
-- End of Migration V2
-- ============================================
