<?php
/**
 * crop_detail.php — AJAX partial for the crop detail modal.
 * Returns HTML fragment (no full page), loaded via fetch() in marketplace.php.
 */
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$customerLoggedIn = isset($_SESSION['customer_id']);
$crop_id = (int)($_GET['id'] ?? 0);

if (!$crop_id) {
    echo '<p style="padding:2rem;color:#ff8a8a;"><i class="fas fa-exclamation-circle"></i> Invalid crop.</p>';
    exit();
}

$stmt = mysqli_prepare($conn,
    "SELECT c.*, f.name AS farmer_name, f.district, f.state, f.mobile AS farmer_mobile
     FROM crops c
     INNER JOIN farmers f ON c.farmer_id = f.farmer_id
     WHERE c.crop_id = ? AND c.status = 'available'"
);
mysqli_stmt_bind_param($stmt, 'i', $crop_id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$crop = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$crop) {
    echo '<p style="padding:2rem;color:#ff8a8a;"><i class="fas fa-exclamation-circle"></i> Crop not found or no longer available.</p>';
    exit();
}

$inCart = $customerLoggedIn && isset($_SESSION['cart'][$crop_id]);
?>

<?php if ($crop['image']): ?>
    <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($crop['image']); ?>"
         alt="<?php echo htmlspecialchars($crop['crop_name']); ?>"
         class="modal-img">
<?php else: ?>
    <div class="modal-img-placeholder"><i class="fas fa-seedling"></i></div>
<?php endif; ?>

<div class="modal-body">
    <div class="modal-title"><?php echo htmlspecialchars($crop['crop_name']); ?></div>
    <div class="modal-farmer">
        <i class="fas fa-tractor" style="color:#a8e063;"></i>
        Sold by <strong><?php echo htmlspecialchars($crop['farmer_name']); ?></strong>
        <?php if ($crop['district'] || $crop['state']): ?>
            &mdash; <?php echo htmlspecialchars(implode(', ', array_filter([$crop['district'], $crop['state']]))); ?>
        <?php endif; ?>
    </div>

    <div class="modal-stats">
        <div class="modal-stat">
            <div class="label">Price per kg</div>
            <div class="val" style="color:#a8e063;">₹<?php echo number_format((float)$crop['price_per_kg'], 2); ?></div>
        </div>
        <div class="modal-stat">
            <div class="label">Available Qty</div>
            <div class="val"><?php echo number_format((float)$crop['quantity'], 0); ?> kg</div>
        </div>
        <?php if ($crop['harvest_date']): ?>
        <div class="modal-stat">
            <div class="label">Harvest Date</div>
            <div class="val"><?php echo date('d M Y', strtotime($crop['harvest_date'])); ?></div>
        </div>
        <?php endif; ?>
        <div class="modal-stat">
            <div class="label">Listed On</div>
            <div class="val"><?php echo date('d M Y', strtotime($crop['created_at'])); ?></div>
        </div>
    </div>

    <?php if ($crop['description']): ?>
        <p class="modal-desc"><?php echo nl2br(htmlspecialchars($crop['description'])); ?></p>
    <?php endif; ?>

    <div class="modal-footer">
        <?php if ($customerLoggedIn): ?>
            <div class="qty-wrap">
                <label for="modalQty">Qty (kg):</label>
                <input type="number" id="modalQty" class="qty-input"
                       value="1" min="1" max="<?php echo (int)$crop['quantity']; ?>" step="1">
            </div>
            <button class="btn-modal-cart <?php echo $inCart ? 'in-cart' : ''; ?>"
                    id="modalCartBtn"
                    onclick="addToCartModal(<?php echo $crop['crop_id']; ?>)">
                <i class="fas fa-<?php echo $inCart ? 'check' : 'cart-plus'; ?>"></i>
                <?php echo $inCart ? 'In Cart' : 'Add to Cart'; ?>
            </button>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>customer/login.php" class="btn-modal-cart" style="text-decoration:none;">
                <i class="fas fa-lock"></i> Login to Buy
            </a>
        <?php endif; ?>
        <button class="btn-modal-close" onclick="closeModal()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</div>

<script>
function addToCartModal(cropId) {
    const qty = parseInt(document.getElementById('modalQty')?.value) || 1;
    const btn = document.getElementById('modalCartBtn');
    addToCart(cropId, qty, btn);
    // Also update the card button in the grid
    const cardBtn = document.getElementById('cartBtn-' + cropId);
    if (cardBtn) {
        cardBtn.classList.add('in-cart');
        cardBtn.innerHTML = '<i class="fas fa-check"></i> In Cart';
    }
}
</script>
