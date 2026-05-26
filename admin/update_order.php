<?php
/**
 * update_order.php — Admin AJAX endpoint for order status management.
 * Accepts POST: order_id, action (approve|ship|deliver|cancel)
 * Updates order_status, payment_status, delivered_at as needed.
 * Logs the action to admin_activity_logs.
 * Returns JSON response.
 */
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/admin_auth_check.php';
checkAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$action   = isset($_POST['action']) ? trim($_POST['action']) : '';
$admin_id = (int)$_SESSION['admin_id'];

if (!$order_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id or action']);
    exit();
}

// Fetch current order
$stmt = mysqli_prepare($conn, "SELECT order_id, order_status, payment_status, payment_method FROM orders WHERE order_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Determine new statuses based on action
$new_order_status   = $order['order_status'];
$new_payment_status = $order['payment_status'];
$set_delivered_at   = false;
$action_type        = '';
$description        = '';

switch ($action) {
    case 'approve':
        if ($order['order_status'] !== 'Placed') {
            echo json_encode(['success' => false, 'message' => 'Order can only be approved from Placed status']);
            exit();
        }
        $new_order_status = 'Approved';
        $action_type = 'Order Approved';
        $description = "Order #$order_id approved by admin";
        break;

    case 'ship':
        if (!in_array($order['order_status'], ['Placed', 'Approved'])) {
            echo json_encode(['success' => false, 'message' => 'Order must be Placed or Approved to ship']);
            exit();
        }
        $new_order_status = 'Shipped';
        $action_type = 'Order Shipped';
        $description = "Order #$order_id marked as shipped";
        break;

    case 'deliver':
        if (!in_array($order['order_status'], ['Shipped', 'Approved'])) {
            echo json_encode(['success' => false, 'message' => 'Order must be Shipped or Approved to deliver']);
            exit();
        }
        $new_order_status = 'Delivered';
        $set_delivered_at = true;
        // COD: confirm payment when delivered
        if (strtoupper($order['payment_method']) === 'COD') {
            $new_payment_status = 'Confirmed (COD Collected)';
        } else {
            $new_payment_status = 'Paid';
        }
        $action_type = 'Order Delivered';
        $description = "Order #$order_id marked as delivered" . 
                       (strtoupper($order['payment_method']) === 'COD' ? ' — COD payment confirmed' : '');
        break;

    case 'cancel':
        if ($order['order_status'] === 'Delivered') {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel a delivered order']);
            exit();
        }
        $new_order_status = 'Cancelled';
        $new_payment_status = ($order['payment_status'] === 'Paid') ? 'Refunded' : 'Cancelled';
        $action_type = 'Order Cancelled';
        $description = "Order #$order_id cancelled by admin";
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . htmlspecialchars($action)]);
        exit();
}

// Begin transaction
mysqli_begin_transaction($conn);

try {
    // Update order
    if ($set_delivered_at) {
        $stmt = mysqli_prepare($conn, 
            "UPDATE orders SET order_status = ?, payment_status = ?, delivered_at = NOW(), updated_at = NOW() WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ssi', $new_order_status, $new_payment_status, $order_id);
    } else {
        $stmt = mysqli_prepare($conn, 
            "UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ssi', $new_order_status, $new_payment_status, $order_id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to update order: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);

    // Log activity
    $log_stmt = mysqli_prepare($conn, 
        "INSERT INTO admin_activity_logs (admin_id, action_type, description, target_id) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($log_stmt, 'issi', $admin_id, $action_type, $description, $order_id);
    if (!mysqli_stmt_execute($log_stmt)) {
        throw new Exception('Failed to log activity: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($log_stmt);

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => $action_type . ' successfully',
        'new_order_status' => $new_order_status,
        'new_payment_status' => $new_payment_status
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
