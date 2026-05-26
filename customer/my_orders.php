<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

$sql = "SELECT o.order_id, o.quantity, o.total_amount, o.order_status, o.payment_status, 
               o.payment_method, o.order_date, o.delivered_at,
               c.crop_name, c.image, f.name AS farmer_name
        FROM orders o
        JOIN crops c ON o.crop_id = c.crop_id
        JOIN farmers f ON o.farmer_id = f.farmer_id
        WHERE o.customer_id = ?
        ORDER BY o.order_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['customer_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) $orders[] = $row;
mysqli_stmt_close($stmt);

$statusBadge = [
    'Placed'           => ['bg'=>'#e3f2fd', 'text'=>'#1565c0'],
    'Approved'         => ['bg'=>'#e0f2f1', 'text'=>'#00695c'],
    'Shipped'          => ['bg'=>'#fff3e0', 'text'=>'#e65100'],
    'Delivered'        => ['bg'=>'#e8f5e9', 'text'=>'#2e7d32'],
    'Confirmed'        => ['bg'=>'#f3e5f5', 'text'=>'#6a1b9a'],
    'Pending Payment'  => ['bg'=>'#fff8e1', 'text'=>'#f57f17'],
    'Cancelled'        => ['bg'=>'#ffebee', 'text'=>'#c62828'],
];

$progressSteps = ['Placed','Approved','Shipped','Delivered'];

function getStepIdx($status) {
    $map = ['Pending Payment'=>0,'Placed'=>0,'Confirmed'=>0,'Approved'=>1,'Shipped'=>2,'Delivered'=>3,'Cancelled'=>-1];
    return $map[$status] ?? 0;
}
?>
<?php include '../includes/header.php'; ?>
<style>
    .order-card { 
        background: #fff; border: 1px solid var(--border-color);
        border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .order-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--primary); }
    .order-img { width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-sm); background: #eee; }
    .order-top { display: flex; gap: 1.5rem; align-items: center; }
    .order-info { flex: 1; }
    .order-h { font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.3rem; }
    .order-sub { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.2rem; }
    .order-meta { font-size: 0.85rem; color: #888; margin-top: 0.5rem; }
    .order-stat { text-align: right; min-width: 120px; }
    .order-price { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem; }
    .status-pill { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
    .payment-info { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; font-size: 0.82rem; color: #777; }
    .payment-info .pay-badge { padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
    @media (max-width: 600px) {
        .order-top { flex-direction: column; align-items: flex-start; }
        .order-stat { text-align: left; margin-top: 1rem; }
    }
</style>

<div class="container mb-2">
    <div class="text-center mb-1">
        <h1 style="color:var(--text-main);"><i class="fas fa-box-open" style="color:var(--primary);"></i> My Orders</h1>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card text-center" style="padding:4rem;">
            <i class="fas fa-shopping-bag" style="font-size:3rem;color:var(--border-color);margin-bottom:1rem;"></i>
            <p style="color:var(--text-muted);margin-bottom:1.5rem;">You haven't placed any orders yet.</p>
            <a href="marketplace.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div style="max-width:900px;margin:0 auto;">
            <?php foreach ($orders as $order):
                $st = $statusBadge[$order['order_status']] ?? $statusBadge['Placed'];
                $stepIdx = getStepIdx($order['order_status']);
                $isCancelled = ($order['order_status'] === 'Cancelled');
            ?>
                <div class="order-card">
                    <div class="order-top">
                        <?php if ($order['image']): ?>
                            <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($order['image']); ?>" class="order-img">
                        <?php else: ?>
                            <div class="order-img" style="display:flex;align-items:center;justify-content:center;color:#ccc;font-size:1.5rem;"><i class="fas fa-leaf"></i></div>
                        <?php endif; ?>
                        
                        <div class="order-info">
                            <div class="order-h"><?php echo htmlspecialchars($order['crop_name']); ?></div>
                            <div class="order-sub">Farmer: <?php echo htmlspecialchars($order['farmer_name']); ?></div>
                            <div class="order-sub">Qty: <strong><?php echo $order['quantity']; ?> kg</strong></div>
                            <div class="order-meta">Ordered on: <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></div>
                        </div>
                        
                        <div class="order-stat">
                            <div class="order-price">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                            <span class="status-pill" style="background:<?php echo $st['bg']; ?>;color:<?php echo $st['text']; ?>;">
                                <?php echo htmlspecialchars($order['order_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Payment Info -->
                    <div class="payment-info">
                        <i class="fas <?php echo strtoupper($order['payment_method'])==='COD' ? 'fa-money-bill-wave' : 'fa-credit-card'; ?>"></i>
                        <?php echo htmlspecialchars($order['payment_method']); ?> — 
                        <span class="pay-badge" style="background:<?php 
                            $ps = $order['payment_status'];
                            if (strpos($ps, 'COD') !== false) echo '#fffde7;color:#f9a825';
                            elseif (strpos($ps, 'Paid') !== false || $ps === 'Paid') echo '#e8f5e9;color:#2e7d32';
                            elseif (strpos($ps, 'Pending') !== false) echo '#fff8e1;color:#f57f17';
                            elseif ($ps === 'Cancelled' || $ps === 'Failed') echo '#ffebee;color:#c62828';
                            else echo '#f5f5f5;color:#616161';
                        ?>;">
                            <?php echo htmlspecialchars($order['payment_status']); ?>
                        </span>
                        <?php if ($order['delivered_at']): ?>
                            <span style="margin-left:auto;font-size:0.78rem;color:#2e7d32;">
                                <i class="fas fa-check-circle"></i> Delivered <?php echo date('d M Y', strtotime($order['delivered_at'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Progress Bar -->
                    <?php if (!$isCancelled): ?>
                    <div class="progress-track" style="margin-top:0.8rem;">
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
                    <div style="margin-top:0.8rem;padding:0.5rem;background:#ffebee;border-radius:8px;font-size:0.82rem;color:#c62828;font-weight:600;">
                        <i class="fas fa-times-circle"></i> This order has been cancelled
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
