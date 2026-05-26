<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Admin Migration</h2>";

// 1. Create admins table
$sql1 = "CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql1)) {
    echo "✅ Table 'admins' created/verified successfully.<br>";
} else {
    echo "❌ Error creating table: " . mysqli_error($conn) . "<br>";
}

// 2. Add email column if it doesn't exist (for existing tables)
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM admins LIKE 'email'");
if (mysqli_num_rows($colCheck) === 0) {
    if (mysqli_query($conn, "ALTER TABLE admins ADD COLUMN email VARCHAR(100) NOT NULL UNIQUE AFTER username")) {
        echo "✅ Added 'email' column to admins table.<br>";
    } else {
        echo "❌ Error adding email column: " . mysqli_error($conn) . "<br>";
    }
}

// 3. Insert or update default admin with hashed password
$adminEmail = 'admin@admin.com';
$adminUser  = 'admin';
$adminPass  = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "SELECT admin_id FROM admins WHERE email = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $adminEmail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Update existing admin password
        mysqli_stmt_close($stmt);
        $update = mysqli_prepare($conn, "UPDATE admins SET password = ?, username = ? WHERE email = ?");
        if ($update) {
            mysqli_stmt_bind_param($update, "sss", $adminPass, $adminUser, $adminEmail);
            if (mysqli_stmt_execute($update)) {
                echo "✅ Default admin password updated.<br>";
            } else {
                echo "❌ Error updating admin: " . mysqli_error($conn) . "<br>";
            }
            mysqli_stmt_close($update);
        }
    } else {
        // Insert new admin
        mysqli_stmt_close($stmt);
        $insert = mysqli_prepare($conn, "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        if ($insert) {
            mysqli_stmt_bind_param($insert, "sss", $adminUser, $adminEmail, $adminPass);
            if (mysqli_stmt_execute($insert)) {
                echo "✅ Default admin user created.<br>";
            } else {
                echo "❌ Error inserting admin: " . mysqli_error($conn) . "<br>";
            }
            mysqli_stmt_close($insert);
        }
    }
} else {
    echo "❌ Database error: " . mysqli_error($conn) . "<br>";
}

// 4. Update crops status ENUM
$sql3 = "ALTER TABLE crops MODIFY COLUMN status ENUM('available', 'sold', 'reserved', 'expired', 'rejected') DEFAULT 'available'";
if (mysqli_query($conn, $sql3)) {
    echo "✅ Table 'crops' updated with new status options.<br>";
} else {
    echo "⚠️ Crops table update: " . mysqli_error($conn) . "<br>";
}

echo "<br><strong>Migration completed.</strong>";
echo "<br><br>Default admin credentials:<br>";
echo "Email: <code>admin@admin.com</code><br>";
echo "Password: <code>admin123</code><br>";
echo "<br><a href='" . BASE_URL . "admin/login.php'>→ Go to Admin Login</a>";
?>
