<?php
/**
 * Run Demo Seed — Imports demo_seed.sql into the database
 * Run via browser: http://localhost/Agriculture-Portal/database/run_seed.php
 * Or via CLI: C:\xampp\php\php.exe database/run_seed.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "<pre>\n";
echo "========================================\n";
echo "  Agriculture Portal - Demo Seed Runner\n";
echo "========================================\n\n";

// Step 1: Generate crop images
echo "[1/2] Generating crop images...\n";
include __DIR__ . '/generate_crop_images.php';
echo "\n";

// Step 2: Run SQL seed
echo "[2/2] Importing demo_seed.sql...\n";
$sqlFile = __DIR__ . '/demo_seed.sql';
if (!file_exists($sqlFile)) {
    die("ERROR: demo_seed.sql not found!\n");
}

$sql = file_get_contents($sqlFile);

// Enable multi-query execution
if (mysqli_multi_query($conn, $sql)) {
    $success = 0;
    do {
        $success++;
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));

    // Check for errors
    if (mysqli_errno($conn)) {
        echo "  ERROR at statement $success: " . mysqli_error($conn) . "\n";
    } else {
        echo "  All SQL statements executed successfully.\n";
    }
} else {
    echo "  ERROR: " . mysqli_error($conn) . "\n";
}

echo "\n========================================\n";
echo "  Verifying Data Counts:\n";
echo "========================================\n\n";

// Verify counts
$tables = ['farmers', 'customers', 'crops', 'orders'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM $table");
    $row = mysqli_fetch_assoc($result);
    echo "  $table: " . $row['cnt'] . " rows\n";
}

// Verify order statuses
$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE order_status = 'Delivered'");
$row = mysqli_fetch_assoc($result);
echo "\n  Delivered orders: " . $row['cnt'] . "\n";

$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE payment_method = 'COD'");
$row = mysqli_fetch_assoc($result);
echo "  COD orders: " . $row['cnt'] . "\n";

$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE payment_status = 'Confirmed (COD Collected)'");
$row = mysqli_fetch_assoc($result);
echo "  COD Confirmed: " . $row['cnt'] . "\n";

echo "\n========================================\n";
echo "  Demo data loaded successfully!\n";
echo "  Login password for all users: Demo@123\n";
echo "========================================\n";
echo "</pre>\n";
