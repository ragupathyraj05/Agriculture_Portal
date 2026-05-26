<?php
/**
 * place_order.php — Backend order processor.
 * Handles both COD and UPI payment flows.
 * For UPI: stores orders as 'Pending UPI' and redirects to the QR payment page.
 * For COD: creates confirmed orders and redirects to success.
 */
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'customer/cart.php');
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$customer_id = (int)$_SESSION['customer_id'];

$addr_name    = trim($_POST['name']    ?? '');
$addr_mobile  = trim($_POST['mobile']  ?? '');
$addr_address = trim($_POST['address'] ?? '');
$addr_city    = trim($_POST['city']    ?? '');
$addr_state   = trim($_POST['state']   ?? '');
$addr_pincode = trim($_POST['pincode'] ?? '');
$payment_method = strtoupper(trim($_POST['payment_method'] ?? 'COD'));

$delivery_address = "$addr_name, $addr_address, $addr_city, $addr_state - $addr_pincode (Mob: $addr_mobile)";

if (empty($cart)) {
    header('Location: ' . BASE_URL . 'customer/marketplace.php?error=empty_cart');
    exit();
}
if (empty($addr_address)) {
    die('Delivery address is required. Please go back and fill in your address.');
}

// Determine payment and order status
$is_upi    = ($payment_method === 'UPI');
$pay_status   = $is_upi ? 'UPI - Pending'         : 'COD - Awaiting Delivery';
$order_status = $is_upi ? 'Pending Payment'        : 'Placed';

mysqli_begin_transaction($conn);

try {
    $order_ids = [];

    $stmt_insert = mysqli_prepare($conn,
        "INSERT INTO orders (customer_id, farmer_id, crop_id, quantity, total_amount,
                            payment_status, order_status, delivery_address, payment_method)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt_insert) throw new Exception("Failed to prepare order insert: " . mysqli_error($conn));

    $stmt_stock = mysqli_prepare($conn,
        "UPDATE crops SET quantity = quantity - ? WHERE crop_id = ? AND quantity >= ?"
    );
    if (!$stmt_stock) throw new Exception("Failed to prepare stock update: " . mysqli_error($conn));

    $stmt_crop = mysqli_prepare($conn, "SELECT farmer_id, quantity FROM crops WHERE crop_id = ?");
    if (!$stmt_crop) throw new Exception("Failed to prepare crop query: " . mysqli_error($conn));

    foreach ($cart as $crop_id => $item) {
        $crop_id = (int)$crop_id;
        $qty     = (float)$item['qty'];
        $price   = (float)$item['price'];
        $amount  = $qty * $price;

        mysqli_stmt_bind_param($stmt_crop, 'i', $crop_id);
        mysqli_stmt_execute($stmt_crop);
        $crop_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_crop));

        if (!$crop_data) throw new Exception("Crop ID $crop_id not found.");
        if ($crop_data['quantity'] < $qty) throw new Exception("Insufficient stock for crop ID $crop_id.");

        $farmer_id = (int)$crop_data['farmer_id'];

        mysqli_stmt_bind_param($stmt_insert, 'iiiddsss' . 's',
            $customer_id, $farmer_id, $crop_id, $qty, $amount,
            $pay_status, $order_status, $delivery_address, $payment_method
        );

        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception("Failed to create order for crop ID $crop_id: " . mysqli_error($conn));
        }
        $order_ids[] = mysqli_insert_id($conn);

        // Reduce stock
        mysqli_stmt_bind_param($stmt_stock, 'did', $qty, $crop_id, $qty);
        if (!mysqli_stmt_execute($stmt_stock)) {
            throw new Exception("Failed to update stock for crop ID $crop_id.");
        }
        if (mysqli_stmt_affected_rows($stmt_stock) === 0) {
            throw new Exception("Stock mismatch during update for crop ID $crop_id.");
        }
    }

    mysqli_stmt_close($stmt_insert);
    mysqli_stmt_close($stmt_stock);
    mysqli_stmt_close($stmt_crop);
    mysqli_commit($conn);

    // Save/update customer address
    $addr_update = mysqli_prepare($conn, "UPDATE customers SET address=?, city=?, state=?, pincode=? WHERE customer_id=?");
    if ($addr_update) {
        mysqli_stmt_bind_param($addr_update, 'ssssi', $addr_address, $addr_city, $addr_state, $addr_pincode, $customer_id);
        mysqli_stmt_execute($addr_update);
        mysqli_stmt_close($addr_update);
    }

    $ids_str = implode(',', $order_ids);

    if ($is_upi) {
        // Save order IDs in session for UPI page
        $_SESSION['upi_order_ids'] = $order_ids;
        $_SESSION['upi_total']     = array_sum(array_map(fn($it) => $it['qty'] * $it['price'], $cart));
        // Clear cart now so it won't be double-processed
        unset($_SESSION['cart']);
        header('Location: ' . BASE_URL . 'customer/upi_payment.php');
    } else {
        unset($_SESSION['cart']);
        header('Location: ' . BASE_URL . 'customer/order_success.php?ids=' . $ids_str . '&method=COD');
    }
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    die('Order failed: ' . htmlspecialchars($e->getMessage()));
}
