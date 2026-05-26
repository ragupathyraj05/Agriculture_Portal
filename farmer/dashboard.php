<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

// Fetch full farmer profile
$stmt = mysqli_prepare($conn, "SELECT * FROM farmers WHERE farmer_id = ?");
if (!$stmt) { session_destroy(); header('Location: ' . BASE_URL . 'farmer/login.php'); exit(); }
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['farmer_id']);
mysqli_stmt_execute($stmt);
$farmer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$farmer) {
    session_destroy();
    header('Location: ' . BASE_URL . 'farmer/login.php');
    exit();
}

$farmer_id = (int)$farmer['farmer_id'];

// ── Crop Stats ──
$crop_stats = ['total' => 0, 'available' => 0, 'sold' => 0, 'pending' => 0];
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) total, SUM(status='available') avail, SUM(status='sold') sold, SUM(status='reserved') pending FROM crops WHERE farmer_id=?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $farmer_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $crop_stats = ['total' => (int)$r['total'], 'available' => (int)$r['avail'], 'sold' => (int)$r['sold'], 'pending' => (int)$r['pending']];
    mysqli_stmt_close($stmt);
}

// ── Order Stats by Status ──
$order_stats = ['placed' => 0, 'shipped' => 0, 'delivered' => 0, 'total' => 0];
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) total, SUM(order_status='Placed' OR order_status='Approved') placed, SUM(order_status='Shipped') shipped, SUM(order_status='Delivered') delivered FROM orders WHERE farmer_id=?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $farmer_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $order_stats = ['total' => (int)$r['total'], 'placed' => (int)$r['placed'], 'shipped' => (int)$r['shipped'], 'delivered' => (int)$r['delivered']];
    mysqli_stmt_close($stmt);
}

// ── Delivered Earnings Only ──
$delivered_earnings = 0;
$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(total_amount),0) earn FROM orders WHERE farmer_id=? AND order_status='Delivered'");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $farmer_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $delivered_earnings = (float)$r['earn'];
    mysqli_stmt_close($stmt);
}

// ── Pending COD Count ──
$pending_cod = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) c FROM orders WHERE farmer_id=? AND payment_status='COD - Awaiting Delivery'");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $farmer_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $pending_cod = (int)$r['c'];
    mysqli_stmt_close($stmt);
}

// ── Sales History (last 10) ──
$sales = [];
$stmt = mysqli_prepare($conn, "SELECT o.order_id, o.quantity, o.total_amount, o.order_status, o.payment_status, o.payment_method, o.order_date, cr.crop_name, c.name AS customer_name FROM orders o JOIN crops cr ON o.crop_id=cr.crop_id JOIN customers c ON o.customer_id=c.customer_id WHERE o.farmer_id=? ORDER BY o.order_date DESC LIMIT 10");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $farmer_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $sales[] = $row;
    mysqli_stmt_close($stmt);
}

$statusColors = [
    'Placed'    => ['bg'=>'#e3f2fd','text'=>'#1565c0'],
    'Approved'  => ['bg'=>'#e0f2f1','text'=>'#00695c'],
    'Shipped'   => ['bg'=>'#fff3e0','text'=>'#e65100'],
    'Delivered' => ['bg'=>'#e8f5e9','text'=>'#2e7d32'],
    'Cancelled' => ['bg'=>'#ffebee','text'=>'#c62828'],
];
?>
<?php include '../includes/header.php'; ?>

<!-- Dashboard Hero -->
<div class="dashboard-hero">
    <div class="avatar"><i class="fas fa-user"></i></div>
    <h1>Welcome, <?php echo htmlspecialchars($farmer['name']); ?>!</h1>
    <p><i class="fas fa-calendar-alt"></i> Member since <?php echo date('F Y', strtotime($farmer['created_at'])); ?></p>
</div>

<div class="dashboard-wrapper">

    <!-- ── Glass Stats Row ── -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-seedling"></i></div>
            <div class="stat-num"><?php echo $crop_stats['total']; ?></div>
            <div class="stat-label">Total Crops</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(21,101,192,0.12);color:#1565c0;"><i class="fas fa-inbox"></i></div>
            <div class="stat-num" style="color:#1565c0;"><?php echo $order_stats['placed']; ?></div>
            <div class="stat-label">Pending Orders</div>
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
            <div class="stat-icon" style="background:rgba(249,168,37,0.12);color:#f9a825;"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-num" style="color:#f9a825;"><?php echo $pending_cod; ?></div>
            <div class="stat-label">Pending COD</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(2,119,189,0.12);color:#0277bd;"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-num" style="color:#0277bd;">₹<?php echo number_format($delivered_earnings, 0); ?></div>
            <div class="stat-label">Earnings (Delivered)</div>
        </div>
    </div>

    <div class="dashboard-layout">

        <!-- LEFT: Smart AI Tools + Sales History -->
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

            <!-- Sales History -->
            <h3 class="section-title" style="margin-top:2rem;"><i class="fas fa-chart-line"></i> Sales History</h3>
            <div class="glass" style="padding:1rem;">
                <?php if (empty($sales)): ?>
                    <div class="text-center" style="padding:2rem;color:var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        No sales yet. Start listing your crops!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Order</th><th>Crop</th><th>Customer</th><th>Qty</th><th>Amount</th><th>Order Status</th><th>Payment</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sales as $s):
                            $sc = $statusColors[$s['order_status']] ?? ['bg'=>'#f5f5f5','text'=>'#616161'];
                        ?>
                            <tr>
                                <td>#<?php echo $s['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($s['crop_name']); ?></td>
                                <td><?php echo htmlspecialchars($s['customer_name']); ?></td>
                                <td><?php echo $s['quantity']; ?> kg</td>
                                <td>₹<?php echo number_format($s['total_amount'], 2); ?></td>
                                <td><span class="badge" style="background:<?php echo $sc['bg']; ?>;color:<?php echo $sc['text']; ?>;"><?php echo $s['order_status']; ?></span></td>
                                <td><span class="badge" style="font-size:0.72rem;"><?php echo htmlspecialchars($s['payment_status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($s['order_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Profile Card + Earnings -->
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
                        <span style="font-weight:600;"><?php echo htmlspecialchars($farmer['mobile']); ?></span>
                    </li>
                    <li style="padding:0.5rem 0;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Email</span>
                        <span style="font-weight:600;"><?php echo $farmer['email'] ? htmlspecialchars($farmer['email']) : '-'; ?></span>
                    </li>
                    <li style="padding:0.5rem 0;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Farm Size</span>
                        <span style="font-weight:600;"><?php echo $farmer['farm_size'] ? htmlspecialchars($farmer['farm_size']).' Acres' : '-'; ?></span>
                    </li>
                    <li style="padding:0.5rem 0;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Location</span>
                        <span style="font-weight:600;"><?php echo htmlspecialchars(($farmer['district'] ?? '') . ', ' . ($farmer['state'] ?? '')); ?></span>
                    </li>
                    <li style="padding:0.5rem 0;display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);font-size:0.85rem;">Main Crops</span>
                        <span style="font-weight:600;"><?php echo htmlspecialchars($farmer['main_crops'] ?? '-'); ?></span>
                    </li>
                </ul>
                <div style="margin-top:1.2rem;">
                    <a href="<?php echo BASE_URL; ?>farmer/edit_profile.php" class="btn btn-outline" style="width:100%;">
                        <i class="fas fa-pen"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Earnings Summary -->
            <div class="glass" style="margin-bottom:1.5rem;">
                <h3 class="section-title" style="margin-bottom:1rem;"><i class="fas fa-wallet"></i> Earnings Summary</h3>
                <div style="text-align:center;padding:1rem 0;">
                    <div style="font-size:2.2rem;font-weight:700;color:var(--primary);">₹<?php echo number_format($delivered_earnings, 2); ?></div>
                    <div style="color:var(--text-muted);font-size:0.88rem;text-transform:uppercase;letter-spacing:0.5px;">Confirmed Earnings</div>
                    <div style="font-size:0.78rem;color:#999;margin-top:4px;">Only from delivered orders</div>
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
                        <div class="amount" style="color:#f9a825;"><?php echo $pending_cod; ?></div>
                        <div class="label">COD Pending</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass" style="margin-bottom:1.5rem;">
                <h3 class="section-title" style="margin-bottom:1rem;"><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;">
                    <a href="<?php echo BASE_URL; ?>farmer/crops.php" class="action-card">
                        <i class="fas fa-carrot"></i><span>My Crops</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>farmer/crops.php?action=add" class="action-card">
                        <i class="fas fa-plus-circle"></i><span>Add Crop</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>weather.php" class="action-card">
                        <i class="fas fa-cloud-sun-rain"></i><span>Weather</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>news.php" class="action-card">
                        <i class="fas fa-newspaper"></i><span>Agri News</span>
                    </a>
                </div>
            </div>

            <!-- Logout -->
            <a href="<?php echo BASE_URL; ?>farmer/logout.php" class="btn btn-outline" style="border-color:#E53935;color:#E53935;width:100%;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>



<?php include '../includes/footer.php'; ?>
