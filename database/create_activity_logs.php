<?php
/**
 * Migration: Create admin_activity_logs table
 * Run this once: http://localhost/Agriculture-Portal/database/create_activity_logs.php
 */
require_once '../includes/config.php';
require_once '../includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS admin_activity_logs (
    log_id     INT AUTO_INCREMENT PRIMARY KEY,
    admin_id   INT,
    action_type VARCHAR(100),
    description TEXT,
    target_id  INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_admin_id   (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color:green;font-family:sans-serif;'>✅ Table <strong>admin_activity_logs</strong> created successfully (or already exists).</p>";
} else {
    echo "<p style='color:red;font-family:sans-serif;'>❌ Error: " . mysqli_error($conn) . "</p>";
}
?>
