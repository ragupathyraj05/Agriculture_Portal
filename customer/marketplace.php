<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$customerLoggedIn = isset($_SESSION['customer_id']);

if (!$customerLoggedIn) {
    header('Location: ../auth.php?role=customer&msg=Please login to view marketplace');
    exit();
}

// ── Fetch available crops with farmer info ──
$search = trim($_GET['search'] ?? '');
$sql = "SELECT c.*, f.name AS farmer_name, f.district, f.state
        FROM crops c
        INNER JOIN farmers f ON c.farmer_id = f.farmer_id
        WHERE c.status = 'available'";
$params = [];
$types  = '';

if ($search !== '') {
    $sql    .= " AND c.crop_name LIKE ?";
    $params[] = '%' . $search . '%';
    $types   .= 's';
}
$sql .= " ORDER BY c.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$crops  = [];
while ($row = mysqli_fetch_assoc($result)) $crops[] = $row;
mysqli_stmt_close($stmt);

// ── Cart count ──
$cartCount = 0;
if ($customerLoggedIn) {
    $cart = $_SESSION['cart'] ?? [];
    foreach ($cart as $item) $cartCount += $item['qty'];
}
?>
<?php include '../includes/header.php'; ?>
<style>
    .market-toolbar { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .search-form { display: flex; gap: 0.5rem; flex: 1; max-width: 500px; }
    .crops-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; max-width: 1200px; margin: 0 auto 3rem; padding: 0 1rem; }
    .crop-card { background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; }
    .crop-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--accent); }
    .crop-img { height: 180px; width: 100%; object-fit: cover; background: #f0f0f0; }
    .crop-body { padding: 1.2rem; flex: 1; display: flex; flex-direction: column; }
    .crop-price { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-top: auto; padding-top: 0.5rem; }
    .crop-actions { padding: 1.2rem; padding-top: 0; display: flex; gap: 0.5rem; }
    .modal-overlay { display: none; position: fixed; inset: 0; z-index: 2000; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; padding: 1rem; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: var(--radius-lg); max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-md); }
</style>

<div class="market-hero">
    <h1><i class="fas fa-store"></i> Crop Marketplace</h1>
    <p>Fresh produce, directly from farmers to your table.</p>
</div>

<!-- Toolbar -->
<div class="market-toolbar">
    <form class="search-form" method="GET">
        <input type="text" name="search" placeholder="Search crops..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1;">
        <button type="submit" class="btn btn-accent"><i class="fas fa-search"></i></button>
        <?php if($search): ?>
            <a href="marketplace.php" class="btn btn-outline" style="border-color:#ccc;color:#666;"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </form>

    <div style="display:flex; gap:1rem; align-items:center;">
        <span style="color:var(--text-muted);font-size:0.9rem;"><?php echo count($crops); ?> crops</span>
        <?php if ($customerLoggedIn): ?>
            <a href="cart.php" class="btn btn-outline" style="border-color:var(--accent);color:var(--accent);">
                <i class="fas fa-shopping-cart"></i> Cart
                <?php if ($cartCount > 0): ?>
                    <span class="badge badge-danger" style="margin-left:5px;"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline" style="border-color:var(--accent);color:var(--accent);">Login to Buy</a>
        <?php endif; ?>
    </div>
</div>

<!-- Grid -->
<div class="crops-grid">
    <?php if (empty($crops)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
            <i class="fas fa-search" style="font-size:3rem;margin-bottom:1rem;opacity:0.3;"></i>
            <p>No crops found matching your search.</p>
        </div>
    <?php else: ?>
        <?php foreach ($crops as $crop): ?>
            <div class="crop-card">
                <?php if ($crop['image']): ?>
                    <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($crop['image']); ?>" class="crop-img" alt="<?php echo htmlspecialchars($crop['crop_name']); ?>">
                <?php else: ?>
                    <div class="crop-img" style="display:flex;align-items:center;justify-content:center;color:#ccc;font-size:3rem;"><i class="fas fa-leaf"></i></div>
                <?php endif; ?>
                
                <div class="crop-body">
                    <h3 style="font-size:1.1rem;margin-bottom:0.2rem;"><?php echo htmlspecialchars($crop['crop_name']); ?></h3>
                    <div style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.5rem;">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($crop['farmer_name']); ?>
                    </div>
                    <div style="font-size:0.9rem;color:var(--text-main);">
                        Available: <strong><?php echo $crop['quantity']; ?> kg</strong>
                    </div>
                    <div class="crop-price">₹<?php echo $crop['price_per_kg']; ?> / kg</div>
                </div>

                <div class="crop-actions">
                    <button onclick="openModal(<?php echo $crop['crop_id']; ?>)" class="btn btn-outline" style="flex:1;border-color:var(--border-color);color:var(--text-main);">Details</button>
                    <?php if ($customerLoggedIn): ?>
                        <button onclick="addToCart(<?php echo $crop['crop_id']; ?>, 1)" class="btn btn-accent" style="flex:1;">
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-accent" style="flex:1;"><i class="fas fa-lock"></i> Login</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal-overlay" id="cropModal" onclick="if(event.target===this)closeModal()">
    <div class="modal-box" id="modalBox">
        <div id="modalContent" style="padding:2rem;text-align:center;">Loading...</div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
function openModal(id) {
    document.getElementById('cropModal').classList.add('open');
    fetch('crop_detail.php?id='+id).then(r=>r.text()).then(h=>{
        document.getElementById('modalContent').innerHTML=h;
    });
}
function closeModal() {
    document.getElementById('cropModal').classList.remove('open');
}
function addToCart(id, qty) {
    fetch('cart_action.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({action:'add', crop_id:id, qty:qty})
    }).then(r=>r.json()).then(d=>{
        if(d.success) location.reload(); else alert(d.message);
    });
}
</script>
