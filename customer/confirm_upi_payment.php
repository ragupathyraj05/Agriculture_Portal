<?php
/**
 * confirm_upi_payment.php
 * Called when the user clicks "I Have Paid" on the UPI QR page.
 * Updates all pending UPI orders to 'Confirmed' status, inserts earnings records.
 */
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'customer/upi_payment.php');
    exit();
}

// Auto-create farmer_earnings table if it doesn't exist yet
mysqli_query($conn,
    "CREATE TABLE IF NOT EXISTS farmer_earnings (
        earning_id   INT AUTO_INCREMENT PRIMARY KEY,
        farmer_id    INT NOT NULL,
        order_id     INT NOT NULL,
        amount       DECIMAL(10,2) NOT NULL,
        payment_type VARCHAR(30) DEFAULT 'UPI',
        earned_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id) ON DELETE CASCADE,
        FOREIGN KEY (order_id)  REFERENCES orders(order_id)   ON DELETE CASCADE,
        UNIQUE KEY uq_order (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$order_ids = $_SESSION['upi_order_ids'] ?? [];
$customer_id = (int)$_SESSION['customer_id'];

if (empty($order_ids)) {
    header('Location: ' . BASE_URL . 'customer/marketplace.php');
    exit();
}

// Generate a simulated transaction reference
$txn_ref = 'UPI' . strtoupper(substr(md5(uniqid()), 0, 12));

mysqli_begin_transaction($conn);
try {
    $ids_str = implode(',', array_map('intval', $order_ids));

    // 1. Update orders → Confirmed + mark payment paid
    $update_sql = "UPDATE orders
                   SET order_status   = 'Confirmed',
                       payment_status = 'UPI - Paid',
                       transaction_id = '$txn_ref',
                       updated_at     = NOW()
                   WHERE order_id IN ($ids_str)
                     AND customer_id = $customer_id
                     AND order_status = 'Pending Payment'";
    if (!mysqli_query($conn, $update_sql)) {
        throw new Exception("Failed to confirm orders: " . mysqli_error($conn));
    }

    // 2. Insert farmer_earnings records for each confirmed order
    $fetch = mysqli_query($conn, "SELECT order_id, farmer_id, total_amount FROM orders WHERE order_id IN ($ids_str) AND customer_id = $customer_id");
    $earn_stmt = mysqli_prepare($conn,
        "INSERT IGNORE INTO farmer_earnings (farmer_id, order_id, amount, payment_type) VALUES (?, ?, ?, 'UPI')"
    );
    if (!$earn_stmt) throw new Exception("Earnings stmt failed: " . mysqli_error($conn));
    while ($row = mysqli_fetch_assoc($fetch)) {
        mysqli_stmt_bind_param($earn_stmt, 'iid', $row['farmer_id'], $row['order_id'], $row['total_amount']);
        mysqli_stmt_execute($earn_stmt);
    }
    mysqli_stmt_close($earn_stmt);

    mysqli_commit($conn);

    // Clear UPI session data
    unset($_SESSION['upi_order_ids']);
    unset($_SESSION['upi_total']);

    // Redirect to success page
    header('Location: ' . BASE_URL . 'customer/payment_success.php?txn=' . urlencode($txn_ref) . '&ids=' . $ids_str);
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    die('Payment confirmation failed: ' . htmlspecialchars($e->getMessage()));
}
