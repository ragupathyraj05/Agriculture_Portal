<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Starting Migration (Alter Only)</h2>";

try {
    // 3. Update crops status ENUM
    // We suppress error if column is already compatible or just try it.
    $sql3 = "ALTER TABLE crops MODIFY COLUMN status ENUM('available', 'sold', 'reserved', 'expired', 'rejected') DEFAULT 'available'";
    if (mysqli_query($conn, $sql3)) {
        echo "✅ Table 'crops' updated with new status options.<br>";
    } else {
        echo "ℹ️ Alter failed (maybe already done?): " . mysqli_error($conn) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error updating crops table: " . $e->getMessage() . "<br>";
}
echo "Done.";
?>
