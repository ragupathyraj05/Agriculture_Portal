<?php
/**
 * cart_action.php — JSON API endpoint for cart operations.
 * Accepts POST with JSON body: { action, crop_id, qty }
 * Actions: add | remove | update | clear
 * Cart is stored in $_SESSION['cart'] as [ crop_id => ['name','price','qty','image'] ]
 */
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Must be logged-in customer
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your cart.']);
    exit();
}

$body   = json_decode(file_get_contents('php://input'), true);
$action  = $body['action']  ?? '';
$crop_id = (int)($body['crop_id'] ?? 0);
$qty     = max(1, (int)($body['qty'] ?? 1));

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ── Helper: total item count ──
function cartCount(): int {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) $total += $item['qty'];
    return $total;
}

// ── ADD ──
if ($action === 'add') {
    if (!$crop_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid crop.']);
        exit();
    }

    // Fetch crop from DB to verify it's still available
    $stmt = mysqli_prepare($GLOBALS['conn'],
        "SELECT crop_id, crop_name, price_per_kg, quantity, image FROM crops WHERE crop_id = ? AND status = 'available'"
    );
    mysqli_stmt_bind_param($stmt, 'i', $crop_id);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $crop = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$crop) {
        echo json_encode(['success' => false, 'message' => 'Crop not available.']);
        exit();
    }

    $maxQty = (int)$crop['quantity'];
    if (isset($_SESSION['cart'][$crop_id])) {
        $newQty = $_SESSION['cart'][$crop_id]['qty'] + $qty;
        $_SESSION['cart'][$crop_id]['qty'] = min($newQty, $maxQty);
    } else {
        $_SESSION['cart'][$crop_id] = [
            'name'      => $crop['crop_name'],
            'price'     => (float)$crop['price_per_kg'],
            'qty'       => min($qty, $maxQty),
            'image'     => $crop['image'],
            'max_qty'   => $maxQty,
        ];
    }

    echo json_encode([
        'success'    => true,
        'message'    => htmlspecialchars($crop['crop_name']) . ' added to cart!',
        'cart_count' => cartCount(),
    ]);
    exit();
}

// ── REMOVE ──
if ($action === 'remove') {
    unset($_SESSION['cart'][$crop_id]);
    echo json_encode(['success' => true, 'message' => 'Item removed.', 'cart_count' => cartCount()]);
    exit();
}

// ── UPDATE QTY ──
if ($action === 'update') {
    if (isset($_SESSION['cart'][$crop_id])) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$crop_id]);
        } else {
            $max = $_SESSION['cart'][$crop_id]['max_qty'] ?? 9999;
            $_SESSION['cart'][$crop_id]['qty'] = min($qty, $max);
        }
    }
    // Recalculate subtotals for response
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $id => $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    echo json_encode([
        'success'    => true,
        'message'    => 'Cart updated.',
        'cart_count' => cartCount(),
        'subtotal'   => number_format($subtotal, 2),
        'item_total' => isset($_SESSION['cart'][$crop_id])
            ? number_format($_SESSION['cart'][$crop_id]['price'] * $_SESSION['cart'][$crop_id]['qty'], 2)
            : '0.00',
    ]);
    exit();
}

// ── CLEAR ──
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode(['success' => true, 'message' => 'Cart cleared.', 'cart_count' => 0]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
