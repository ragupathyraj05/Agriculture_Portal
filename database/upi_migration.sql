-- ============================================
-- UPI Payment Feature Migration
-- Agriculture Portal
-- Date: 2026-03-13
-- ============================================

USE agriculture_portal;

-- Add farmer_earnings table to track UPI payments
CREATE TABLE IF NOT EXISTS farmer_earnings (
    earning_id    INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id     INT NOT NULL,
    order_id      INT NOT NULL,
    amount        DECIMAL(10,2) NOT NULL,
    payment_type  VARCHAR(30) DEFAULT 'UPI',
    earned_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)  REFERENCES orders(order_id)   ON DELETE CASCADE,
    INDEX idx_farmer_id (farmer_id),
    INDEX idx_order_id  (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure orders table has the upi_txn_ref column (safe ALTER)
ALTER TABLE orders
    MODIFY COLUMN payment_status VARCHAR(80) DEFAULT 'Pending',
    MODIFY COLUMN payment_method VARCHAR(50) DEFAULT 'COD';
