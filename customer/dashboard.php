<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

$customer_id = (int)$_SESSION['customer_id'];

// Fetch customer profile
$stmt = mysqli_prepare($conn, "SELECT * FROM customers WHERE customer_id = ?");
if (!$stmt) { session_destroy(); header('Location: ' . BASE_URL . 'customer/login.php'); exit(); }
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$customer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$customer) {
    session_destroy();
    header('Location: ' . BASE_URL . 'customer/login.php');
    exit();
}

// ── Order Stats ──
$order_stats = ['total' => 0, 'delivered' => 0, 'pending' => 0, 'shipped' => 0];
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) total, SUM(order_status='Delivered') dlvd, SUM(order_status='Placed' OR order_status='Approved') pndg, SUM(order_status='Shipped') shpd FROM orders WHERE customer_id=?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $order_stats = ['total' => (int)$r['total'], 'delivered' => (int)$r['dlvd'], 'pending' => (int)$r['pndg'], 'shipped' => (int)$r['shpd']];
    mysqli_stmt_close($stmt);
}

// ── Total Spent ──
$total_spent = 0;
$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(total_amount),0) spent FROM orders WHERE customer_id=?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $total_spent = (float)$r['spent'];
    mysqli_stmt_close($stmt);
}

// ── Cart count ──
$cart = $_SESSION['cart'] ?? [];
$cart_count = count($cart);

// ── Purchase History (last 10) ──
$orders = [];
$stmt = mysqli_prepare($conn, "SELECT o.order_id, o.quantity, o.total_amount, o.order_status, o.payment_status, o.payment_method, o.order_date, cr.crop_name, cr.image, f.name AS farmer_name FROM orders o JOIN crops cr ON o.crop_id=cr.crop_id JOIN farmers f ON o.farmer_id=f.farmer_id WHERE o.customer_id=? ORDER BY o.order_date DESC LIMIT 10");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $orders[] = $row;
    mysqli_stmt_close($stmt);
}

$statusColors = [
    'Placed'    => ['bg'=>'#e3f2fd','text'=>'#1565c0'],
    'Approved'  => ['bg'=>'#e0f2f1','text'=>'#00695c'],
    'Shipped'   => ['bg'=>'#fff3e0','text'=>'#e65100'],
    'Delivered' => ['bg'=>'#e8f5e9','text'=>'#2e7d32'],
    'Cancelled' => ['bg'=>'#ffebee','text'=>'#c62828'],
];

$progressSteps = ['Placed','Approved','Shipped','Delivered'];

function getStepIndex($status) {
    $map = ['Placed'=>0,'Approved'=>1,'Shipped'=>2,'Delivered'=>3,'Cancelled'=>-1];
    return $map[$status] ?? 0;
}
?>
<?php include '../includes/header.php'; ?>

<!-- Dashboard Hero -->
<div class="dashboard-hero">
    <div class="avatar" style="color:var(--accent);"><i class="fas fa-user-circle"></i></div>
    <h1>Welcome, <?php echo htmlspecialchars($customer['name']); ?>!</h1>
    <p><i class="fas fa-calendar-alt"></i> Member since <?php echo date('F Y', strtotime($customer['created_at'])); ?></p>
</div>

<div class="dashboard-wrapper">

    <!-- ── Glass Stats Row ── -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(102,187,106,0.12);color:var(--accent);"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-num" style="color:var(--accent);"><?php echo $order_stats['total']; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(21,101,192,0.12);color:#1565c0;"><i class="fas fa-clock"></i></div>
            <div class="stat-num" style="color:#1565c0;"><?php echo $order_stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(230,81,0,0.12);color:#e65100;"><i class="fas fa-truck"></i></div>
            <div class="stat-num" style="color:#e65100;"><?php echo $order_stats['shipped']; ?></div>
            <div class="stat-label">Shipped</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(46,125,50,0.12);color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
            <div class="stat-num" style="color:#2e7d32;"><?php echo $order_stats['delivered']; ?></div>
            <div class="stat-label">Delivered</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(27,94,32,0.12);color:var(--primary);"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-num">₹<?php echo number_format($total_spent, 0); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <div class="dashboard-layout">

        <!-- LEFT: AI Tools + Purchase History -->
        <div class="left-content">
            <h3 class="section-title" style="margin-bottom:1rem;"><i class="fas fa-brain" style="color:#1B5E20;"></i> Smart Tools</h3>
            <div class="quick-grid">
                <a href="<?php echo BASE_URL; ?>crop_prediction.php" class="quick-card">
                    <i class="fas fa-seedling"></i>
                    <span>Crop Prediction</span>
                </a>
                <a href="<?php echo BASE_URL; ?>crop_recommendation.php" class="quick-card">
                    <i class="fas fa-lightbulb"></i>
                    <span>Crop Recommend</span>
                </a>
                <a href="<?php echo BASE_URL; ?>yield_prediction.php" class="quick-card">
                    <i class="fas fa-chart-bar"></i>
                    <span>Yield Prediction</span>
                </a>
                <a href="<?php echo BASE_URL; ?>rainfall_prediction.php" class="quick-card">
                    <i class="fas fa-cloud-rain"></i>
                    <span>Rainfall Predict</span>
                </a>
                <a href="<?php echo BASE_URL; ?>fertilizer_recommendation.php" class="quick-card">
                    <i class="fas fa-flask"></i>
                    <span>Fertilizer Recommendation</span>
                </a>
            </div>

            <!-- Purchase History with Progress Bars -->
            <h3 class="section-title" style="margin-top:2rem;"><i class="fas fa-history" style="color:var(--accent);"></i> Purchase History</h3>
            <div class="glass" style="padding:1rem;">
                <?php if (empty($orders)): ?>
                    <div class="text-center" style="padding:2rem;color:var(--text-muted);">
                        <i class="fas fa-shopping-bag" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        No purchases yet. <a href="<?php echo BASE_URL; ?>customer/marketplace.php" style="color:var(--accent);">Start shopping!</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $o):
                        $sc = $statusColors[$o['order_status']] ?? $statusColors['Placed'];
                        $stepIdx = getStepIndex($o['order_status']);
                        $isCancelled = ($o['order_status'] === 'Cancelled');
                    ?>
                    <div style="padding:1rem 0;border-bottom:1px solid rgba(0,0,0,0.06);">
                        <div style="display:flex;align-items:center;gap:1rem;">
                            <?php if ($o['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($o['image']); ?>" style="width:45px;height:45px;border-radius:10px;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:45px;height:45px;border-radius:10px;background:rgba(102,187,106,0.1);display:flex;align-items:center;justify-content:center;color:var(--accent);"><i class="fas fa-leaf"></i></div>
                            <?php endif; ?>
                            <div style="flex:1;">
                                <div style="font-weight:600;font-size:0.92rem;"><?php echo htmlspecialchars($o['crop_name']); ?></div>
                                <div style="font-size:0.8rem;color:var(--text-muted);"><?php echo htmlspecialchars($o['farmer_name']); ?> · <?php echo $o['quantity']; ?> kg · <?php echo date('d M Y', strtotime($o['order_date'])); ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:700;font-size:0.92rem;">₹<?php echo number_format($o['total_amount'], 2); ?></div>
                                <span class="badge" style="background:<?php echo $sc['bg']; ?>;color:<?php echo $sc['text']; ?>;font-size:0.72rem;"><?php echo $o['order_status']; ?></span>
                            </div>
                        </div>
                        <!-- Payment Status -->
                        <div style="margin-top:0.5rem;display:flex;justify-content:space-between;align-items:center;">
                            <div style="font-size:0.78rem;color:#999;">
                                <i class="fas <?php echo strtoupper($o['payment_method'])==='COD' ? 'fa-money-bill-wave' : 'fa-credit-card'; ?>" style="margin-right:3px;"></i>
                                <?php echo htmlspecialchars($o['payment_method']); ?> — 
                                <span style="font-weight:600;"><?php echo htmlspecialchars($o['payment_status']); ?></span>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <?php if (!$isCancelled): ?>
                        <div class="progress-track" style="margin-top:0.5rem;">
                            <?php foreach ($progressSteps as $i => $step): ?>
                                <?php if ($i > 0): ?>
                                <div class="progress-line <?php echo ($i <= $stepIdx) ? 'done' : ''; ?>"></div>
                                <?php endif; ?>
                                <div class="progress-step <?php echo ($i <= $stepIdx) ? 'active' : ''; ?>">
                                    <div class="step-dot"><i class="fas fa-check"></i></div>
                                    <div class="step-label"><?php echo $step; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div style="margin-top:0.4rem;font-size:0.78rem;color:#c62828;font-weight:600;">
                            <i class="fas fa-times-circle"></i> Order Cancelled
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <div class="text-center" style="padding:0.8rem;">
                        <a href="<?php echo BASE_URL; ?>customer/my_orders.php" style="color:var(--accent);font-size:0.9rem;font-weight:600;">View All Orders →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Profile + Spending Summary -->
        <div class="right-content">
            <!-- Profile -->
            <div class="profile-card" style="margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 class="section-title" style="margin-bottom:0;"><i class="fas fa-user-circle"></i> My Profile</h3>
                    <span class="badge badge-success">Verified</span>
                </div>
                <ul style="list-style:none;text-align:left;">
                    <li style="padding:0.5rem 0;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Mobile</span>
                        <span style="font-weight:600;"><?php echo htmlspecialchars($customer['mobile']); ?></span>
                    </li>
                    <li style="padding:0.5rem 0;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Email</span>
                        <span style="font-weight:600;"><?php echo $customer['email'] ? htmlspecialchars($customer['email']) : '-'; ?></span>
                    </li>
                    <li style="padding:0.5rem 0;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Location</span>
                        <span style="font-weight:600;"><?php echo htmlspecialchars(($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '')); ?></span>
                    </li>
                    <li style="padding:0.5rem 0;display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Pincode</span>
                        <span style="font-weight:600;"><?php echo htmlspecialchars($customer['pincode'] ?? '-'); ?></span>
                    </li>
                </ul>
                <?php if ($customer['address']): ?>
                <div style="margin-top:0.8rem;font-size:0.88rem;color:var(--text-muted);">
                    <i class="fas fa-map-marker-alt"></i> <?php echo nl2br(htmlspecialchars($customer['address'])); ?>
                </div>
                <?php endif; ?>
                <div style="margin-top:1.2rem;">
                    <a href="<?php echo BASE_URL; ?>customer/edit_profile.php" class="btn btn-outline" style="width:100%;">
                        <i class="fas fa-pen"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Spending Summary -->
            <div class="glass" style="margin-bottom:1.5rem;">
                <h3 class="section-title" style="margin-bottom:1rem;"><i class="fas fa-chart-pie"></i> Spending Summary</h3>
                <div style="text-align:center;padding:1rem 0;">
                    <div style="font-size:2.2rem;font-weight:700;color:var(--primary);">₹<?php echo number_format($total_spent, 2); ?></div>
                    <div style="color:var(--text-muted);font-size:0.88rem;text-transform:uppercase;letter-spacing:0.5px;">Total Spent</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.8rem;margin-top:1rem;">
                    <div class="earnings-card">
                        <div class="amount" style="color:#2e7d32;"><?php echo $order_stats['delivered']; ?></div>
                        <div class="label">Delivered</div>
                    </div>
                    <div class="earnings-card">
                        <div class="amount" style="color:#e65100;"><?php echo $order_stats['shipped']; ?></div>
                        <div class="label">Shipped</div>
                    </div>
                    <div class="earnings-card">
                        <div class="amount" style="color:#1565c0;"><?php echo $order_stats['pending']; ?></div>
                        <div class="label">Pending</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass" style="margin-bottom:1.5rem;">
                <h3 class="section-title" style="margin-bottom:1rem;"><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;">
                    <a href="<?php echo BASE_URL; ?>customer/marketplace.php" class="action-card">
                        <i class="fas fa-store-alt" style="color:var(--accent);"></i><span>Buy Crops</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>customer/cart.php" class="action-card" style="position:relative;">
                        <i class="fas fa-shopping-cart" style="color:var(--accent);"></i><span>My Cart</span>
                        <?php if ($cart_count > 0): ?>
                            <span style="position:absolute;top:8px;right:8px;background:#ff4d4d;color:#fff;font-size:0.7rem;padding:2px 6px;border-radius:50%;font-weight:700;"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>customer/my_orders.php" class="action-card">
                        <i class="fas fa-box-open" style="color:var(--primary);"></i><span>My Orders</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>weather.php" class="action-card">
                        <i class="fas fa-cloud-sun-rain" style="color:#fbc02d;"></i><span>Weather</span>
                    </a>
                </div>
            </div>

            <!-- Logout -->
            <a href="<?php echo BASE_URL; ?>customer/logout.php" class="btn btn-outline" style="border-color:#E53935;color:#E53935;width:100%;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>



<?php include '../includes/footer.php'; ?>
