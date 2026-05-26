<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: ' . BASE_URL . 'customer/marketplace.php');
    exit();
}

// Compute total
$total = 0;
$cart_keys = array_map('intval', array_keys($cart));
$ids = implode(',', $cart_keys);
if (empty($cart_keys)) {
    header('Location: ' . BASE_URL . 'customer/marketplace.php');
    exit();
}
$sql = "SELECT c.crop_id, c.crop_name, c.price_per_kg, c.quantity FROM crops c WHERE c.crop_id IN ($ids)";
$result = mysqli_query($conn, $sql);
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $qty = $cart[$row['crop_id']]['qty'];
    $sub = $qty * $row['price_per_kg'];
    $total += $sub;
    $items[] = ['name' => $row['crop_name'], 'qty' => $qty, 'price' => $sub];
}

// Pre-fill customer address
$cust = ['name' => '', 'mobile' => '', 'address' => '', 'city' => '', 'state' => '', 'pincode' => ''];
$stmt = mysqli_prepare($conn, "SELECT name, mobile, address, city, state, pincode FROM customers WHERE customer_id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['customer_id']);
    mysqli_stmt_execute($stmt);
    $cust_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($cust_row) {
        $cust = array_merge($cust, $cust_row);
    }
    mysqli_stmt_close($stmt);
}
?>
<?php include '../includes/header.php'; ?>
<style>
    .checkout-grid { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 2rem; margin-top: 2rem; }
    @media (max-width: 800px) { .checkout-grid { grid-template-columns: 1fr; } }
    .checkout-card { background: #fff; padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
    .checkout-header { margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.8rem; font-size: 1.2rem; font-weight: 700; color: var(--text-main); }
    .summary-item { display: flex; justify-content: space-between; margin-bottom: 0.6rem; font-size: 0.9rem; color: var(--text-muted); }
    .summary-total { border-top: 1px solid #eee; padding-top: 1rem; margin-top: 1rem; font-weight: 700; font-size: 1.2rem; color: var(--primary); display: flex; justify-content: space-between; }
    .payment-option { display: flex; align-items: center; gap: 0.8rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: 0.2s; margin-bottom: 0.8rem; }
    .payment-option:hover { background: #f9f9f9; border-color: var(--accent); }
    .payment-option input[type="radio"] { accent-color: var(--accent); transform: scale(1.2); }
    .payment-option.selected { border-color: var(--primary); background: #f0faf0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .upi-badge { display:inline-flex;align-items:center;gap:4px;background:linear-gradient(135deg,#6a1b9a,#1565c0);color:#fff;font-size:0.7rem;font-weight:700;padding:2px 7px;border-radius:20px;letter-spacing:0.4px; }
</style>

<div class="container mb-2">
    <div class="text-center mb-1">
        <h1 style="color:var(--text-main);"><i class="fas fa-lock" style="color:var(--primary);"></i> Checkout</h1>
    </div>

    <?php if (empty(trim($cust['address']))): ?>
    <div class="mb-1" style="background:#fff8e1;color:#ff6f00;padding:0.8rem 1rem;border-radius:8px;font-size:0.9rem;border:1px solid #ffcc80;">
        <i class="fas fa-info-circle"></i> <strong>Please fill in your delivery address below.</strong> This will be saved for future orders.
    </div>
    <?php endif; ?>

    <form action="place_order.php" method="POST" class="checkout-grid" id="checkoutForm">
        
        <!-- Address & Payment -->
        <div class="checkout-card">
            <h3 class="checkout-header">Shipping Details</h3>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($cust['name']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Mobile</label>
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($cust['mobile']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" value="<?php echo htmlspecialchars($cust['pincode']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="2" required><?php echo htmlspecialchars($cust['address']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($cust['city']); ?>" required>
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" value="<?php echo htmlspecialchars($cust['state']); ?>" required>
                </div>
            </div>

            <h3 class="checkout-header" style="margin-top:2rem;">Payment Method</h3>

            <!-- COD Option -->
            <label class="payment-option" id="lbl-cod">
                <input type="radio" name="payment_method" value="COD" id="pay-cod" checked>
                <div>
                    <div style="font-weight:600;"><i class="fas fa-truck" style="color:#e65100;margin-right:6px;"></i>Cash on Delivery</div>
                    <div style="font-size:0.85rem;color:var(--text-muted);">Pay when you receive your crops.</div>
                </div>
            </label>

            <!-- UPI Option -->
            <label class="payment-option" id="lbl-upi">
                <input type="radio" name="payment_method" value="UPI" id="pay-upi">
                <div>
                    <div style="font-weight:600;">
                        <i class="fas fa-qrcode" style="color:#6a1b9a;margin-right:6px;"></i>UPI Payment
                        <span class="upi-badge">INSTANT</span>
                    </div>
                    <div style="font-size:0.85rem;color:var(--text-muted);">
                        Pay via Google Pay, PhonePe, Paytm &amp; more.
                    </div>
                </div>
            </label>
        </div>

        <!-- Order Summary -->
        <div class="checkout-card" style="height:fit-content;">
            <h3 class="checkout-header">Order Summary</h3>
            
            <?php foreach ($items as $item): ?>
            <div class="summary-item">
                <span><?php echo $item['qty']; ?> x <?php echo htmlspecialchars($item['name']); ?></span>
                <span>₹<?php echo number_format($item['price'], 2); ?></span>
            </div>
            <?php endforeach; ?>
            
            <div class="summary-item">
                <span>Delivery Charge</span>
                <span class="text-primary">FREE</span>
            </div>
            
            <div class="summary-total">
                <span>Total Amount</span>
                <span>₹<?php echo number_format($total, 2); ?></span>
            </div>
            
            <button type="submit" class="btn btn-primary" id="placeOrderBtn" style="width:100%;margin-top:1.5rem;padding:0.9rem;font-size:1.1rem;">
                <i class="fas fa-lock" id="btnIcon"></i> <span id="btnText">Place Order</span>
            </button>
            <a href="cart.php" style="display:block;text-align:center;margin-top:1rem;color:var(--text-muted);">Back to Cart</a>
        </div>
        
    </form>
</div>

<script>
(function(){
    const codRadio = document.getElementById('pay-cod');
    const upiRadio = document.getElementById('pay-upi');
    const lblCod    = document.getElementById('lbl-cod');
    const lblUpi   = document.getElementById('lbl-upi');
    const btnText  = document.getElementById('btnText');
    const btnIcon  = document.getElementById('btnIcon');

    function updateUI() {
        if (upiRadio.checked) {
            lblUpi.classList.add('selected');
            lblCod.classList.remove('selected');
            btnText.textContent = 'Proceed to Pay via UPI';
            btnIcon.className = 'fas fa-qrcode';
        } else {
            lblCod.classList.add('selected');
            lblUpi.classList.remove('selected');
            btnText.textContent = 'Place Order';
            btnIcon.className = 'fas fa-lock';
        }
    }
    codRadio.addEventListener('change', updateUI);
    upiRadio.addEventListener('change', updateUI);
    // Set initial state
    lblCod.classList.add('selected');
})();
</script>

<?php include '../includes/footer.php'; ?>
