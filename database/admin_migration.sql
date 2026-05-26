-- ============================================
-- Admin Migration Script
-- ============================================
-- This creates the admins table and inserts the default admin user.
-- Default credentials: email = admin@admin.com, password = admin123
-- ============================================

-- 1. Create admins table
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Insert default admin (password: admin123, bcrypt hashed)
INSERT IGNORE INTO admins (username, email, password) VALUES 
('admin', 'admin@admin.com', '$2y$10$qvQdPkfFSZ/buTRUKS1HbOl06VHEjO/z8bj4/qu4/YUww1dweZqB6');

-- 3. Update crops status ENUM to include 'rejected'
ALTER TABLE crops MODIFY COLUMN status ENUM('available', 'sold', 'reserved', 'expired', 'rejected') DEFAULT 'available';
