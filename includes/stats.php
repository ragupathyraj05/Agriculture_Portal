<?php
require_once 'db.php';

// Initialize variables with default 0
$farmer_count = 0;
$completed_orders = 0;
$crop_types = 0;
$prediction_count = 0;

try {
    // 1. Total Farmers Registered
    $res1 = mysqli_query($conn, "SELECT COUNT(*) as count FROM farmers");
    if ($res1) {
        $row1 = mysqli_fetch_assoc($res1);
        $farmer_count = $row1['count'];
    }

    // 2. Orders Completed
    $res2 = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE order_status = 'Delivered'");
    if ($res2) {
        $row2 = mysqli_fetch_assoc($res2);
        $completed_orders = $row2['count'];
    }

    // 3. Total Crop Types
    $res3 = mysqli_query($conn, "SELECT COUNT(DISTINCT crop_name) as count FROM crops");
    if ($res3) {
        $row3 = mysqli_fetch_assoc($res3);
        $crop_types = $row3['count'];
    }

    // 4. Total Predictions Made
    $res4 = mysqli_query($conn, "SELECT COUNT(*) as count FROM prediction_history");
    if ($res4) {
        $row4 = mysqli_fetch_assoc($res4);
        $prediction_count = $row4['count'];
    }
} catch (Exception $e) {
    // Silently fail to maintain 0 if table doesn't exist yet
}
?>
