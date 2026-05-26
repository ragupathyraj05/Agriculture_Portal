<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$total = 0;

if (!empty($cart)) {
    $cart_keys = array_map('intval', array_keys($cart));
    $ids = implode(',', $cart_keys);
    $sql = "SELECT c.crop_id, c.crop_name, c.price_per_kg, c.quantity AS stock, c.image, f.name AS farmer_name 
            FROM crops c 
            JOIN farmers f ON c.farmer_id = f.farmer_id 
            WHERE c.crop_id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $cid = $row['crop_id'];
        $qty = $cart[$cid]['qty'];
        
        // Stock check
        if ($qty > $row['stock']) {
            $qty = $row['stock'];
            $_SESSION['cart'][$cid]['qty'] = $qty; // auto adjust
        }
        
        $subtotal = $qty * $row['price_per_kg'];
        $total += $subtotal;
        
        $row['qty'] = $qty;
        $row['subtotal'] = $subtotal;
        $cartItems[] = $row;
    }
}
?>
<?php include '../includes/header.php'; ?>
<style>
    .cart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem; }
    @media (max-width: 768px) { .cart-grid { grid-template-columns: 1fr; } }
    .cart-item { display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid #eee; align-items: center; }
    .cart-item:last-child { border-bottom: none; }
    .item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background: #f0f0f0; }
    .item-details { flex: 1; }
    .item-title { font-weight: 600; font-size: 1rem; color: var(--text-main); }
    .item-farmer { font-size: 0.85rem; color: var(--text-muted); }
    .item-price { font-weight: 600; color: var(--primary); }
    .qty-controls { display: flex; align-items: center; gap: 0.5rem; }
    .qty-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border-color); background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .qty-btn:hover { background: #f5f5f5; }
    .qty-val { width: 30px; text-align: center; font-weight: 600; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.95rem; }
    .summary-total { border-top: 1px solid var(--border-color); padding-top: 1rem; font-weight: 700; font-size: 1.2rem; color: var(--text-main); }
</style>

<div class="container mb-2">
    <div class="text-center mb-1">
        <h1 style="color:var(--text-main);"><i class="fas fa-shopping-cart" style="color:var(--accent);"></i> My Cart</h1>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="card text-center" style="padding:4rem;">
            <i class="fas fa-shopping-basket" style="font-size:3rem;color:var(--border-color);margin-bottom:1rem;"></i>
            <p style="color:var(--text-muted);margin-bottom:1.5rem;">Your cart is empty.</p>
            <a href="marketplace.php" class="btn btn-accent">Browse Crops</a>
        </div>
    <?php else: ?>
        <div class="cart-grid">
            <!-- Items -->
            <div class="card" style="padding:0;">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" id="item-<?php echo $item['crop_id']; ?>">
                    <?php if ($item['image']): ?>
                        <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($item['image']); ?>" class="item-img">
                    <?php else: ?>
                        <div class="item-img" style="display:flex;align-items:center;justify-content:center;color:#ccc;"><i class="fas fa-leaf"></i></div>
                    <?php endif; ?>
                    
                    <div class="item-details">
                        <div class="item-title"><?php echo htmlspecialchars($item['crop_name']); ?></div>
                        <div class="item-farmer">Sold by: <?php echo htmlspecialchars($item['farmer_name']); ?></div>
                        <div class="item-price">₹<?php echo $item['price_per_kg']; ?> / kg</div>
                    </div>
                    
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty(<?php echo $item['crop_id']; ?>, -1)"><i class="fas fa-minus" style="font-size:0.7rem;"></i></button>
                        <span class="qty-val" id="qty-<?php echo $item['crop_id']; ?>"><?php echo $item['qty']; ?></span>
                        <button class="qty-btn" onclick="updateQty(<?php echo $item['crop_id']; ?>, 1)"><i class="fas fa-plus" style="font-size:0.7rem;"></i></button>
                    </div>
                    
                    <div style="text-align:right;min-width:80px;">
                        <div style="font-weight:700;" id="subtotal-<?php echo $item['crop_id']; ?>">₹<?php echo number_format($item['subtotal'], 2); ?></div>
                        <button onclick="removeItem(<?php echo $item['crop_id']; ?>)" style="background:none;border:none;color:#ff4d4d;font-size:0.8rem;cursor:pointer;margin-top:4px;">Remove</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Summary -->
            <div>
                <div class="card">
                    <h3 style="margin-bottom:1.5rem;">Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="cart-subtotal">₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Convenience Fee</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="cart-total" style="color:var(--primary);">₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary" style="width:100%;margin-top:1.5rem;padding:0.8rem;">
                        Proceed to Checkout <i class="fas fa-arrow-right"></i>
                    </a>
                    
                    <a href="marketplace.php" style="display:block;text-align:center;margin-top:1rem;font-size:0.9rem;color:var(--text-muted);">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
<script>
function updateQty(id, change) {
    let qtyEl = document.getElementById('qty-'+id);
    let newQty = parseInt(qtyEl.innerText) + change;
    if (newQty < 1) return;
    fetch('cart_action.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({action:'update', crop_id:id, qty:newQty})
    }).then(r=>r.json()).then(d=>{
        if(d.success) location.reload(); else alert(d.message);
    });
}
function removeItem(id) {
    if(!confirm('Remove this item?')) return;
    fetch('cart_action.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({action:'remove', crop_id:id})
    }).then(r=>r.json()).then(d=>{
        if(d.success) location.reload();
    });
}
</script>
