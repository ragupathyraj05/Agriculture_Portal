<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/admin_auth_check.php';
checkAdmin();

$sql = "SELECT o.*, c.name AS customer_name, f.name AS farmer_name, cr.crop_name 
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN farmers f ON o.farmer_id = f.farmer_id
        JOIN crops cr ON o.crop_id = cr.crop_id
        ORDER BY o.order_date DESC";
$orders = mysqli_query($conn, $sql);

// Badge maps
$orderBadge = [
    'Placed'    => ['bg' => '#e3f2fd', 'text' => '#1565c0'],
    'Approved'  => ['bg' => '#e0f2f1', 'text' => '#00695c'],
    'Shipped'   => ['bg' => '#fff3e0', 'text' => '#e65100'],
    'Delivered' => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
    'Cancelled' => ['bg' => '#ffebee', 'text' => '#c62828'],
];
$payBadge = [
    'Pending'                  => ['bg' => '#f5f5f5', 'text' => '#616161'],
    'COD - Awaiting Delivery'  => ['bg' => '#fffde7', 'text' => '#f9a825'],
    'Confirmed (COD Collected)'=> ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
    'Paid'                     => ['bg' => '#e8f5e9', 'text' => '#2e7d32'],
    'Failed'                   => ['bg' => '#ffebee', 'text' => '#c62828'],
    'Refunded'                 => ['bg' => '#fce4ec', 'text' => '#ad1457'],
    'Cancelled'                => ['bg' => '#ffebee', 'text' => '#c62828'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders – Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .orders-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
        .orders-header h1 { color:var(--text-main); margin:0; font-size:1.6rem; }
        .order-stats-mini { display:flex; gap:1rem; }
        .order-stats-mini .mini-stat { background:#fff; padding:0.6rem 1.2rem; border-radius:10px; border:1px solid var(--border-color); font-size:0.85rem; font-weight:600; }
        .order-stats-mini .mini-stat span { color:var(--primary); font-size:1.1rem; margin-right:4px; }
        
        .manage-table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
        .manage-table th { background:linear-gradient(135deg,#1a1a2e,#16213e); color:#fff; padding:0.9rem 1rem; text-align:left; font-weight:600; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.5px; }
        .manage-table td { padding:0.8rem 1rem; border-bottom:1px solid #f0f0f0; font-size:0.88rem; vertical-align:middle; }
        .manage-table tbody tr:hover { background:rgba(232,245,233,0.3); }
        .manage-table tbody tr:last-child td { border-bottom:none; }
        
        .status-badge { padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; display:inline-block; white-space:nowrap; }
        
        .action-select { padding:6px 10px; border:1px solid #ddd; border-radius:8px; font-size:0.82rem; font-family:inherit; background:#fff; cursor:pointer; min-width:140px; }
        .action-select:focus { outline:none; border-color:var(--accent); }
        
        .action-btn { padding:6px 14px; border:none; border-radius:8px; font-size:0.8rem; font-weight:600; cursor:pointer; font-family:inherit; transition:all 0.2s; }
        .action-btn-go { background:linear-gradient(135deg,#1B5E20,#2E7D32); color:#fff; }
        .action-btn-go:hover { transform:translateY(-1px); box-shadow:0 3px 10px rgba(27,94,32,0.3); }
        .action-btn-go:disabled { opacity:0.5; cursor:not-allowed; transform:none; box-shadow:none; }
        
        .order-id-cell { font-weight:700; color:var(--primary); }
        .customer-cell .name { font-weight:600; }
        .customer-cell .id { font-size:0.75rem; color:#999; }
        
        .toast { position:fixed; top:80px; right:20px; padding:1rem 1.5rem; border-radius:10px; font-weight:600; font-size:0.9rem; z-index:9999; box-shadow:0 4px 20px rgba(0,0,0,0.15); transform:translateX(120%); transition:transform 0.3s cubic-bezier(0.4,0,0.2,1); display:flex; align-items:center; gap:8px; }
        .toast.show { transform:translateX(0); }
        .toast-success { background:#e8f5e9; color:#1B5E20; border:1px solid #a5d6a7; }
        .toast-error { background:#ffebee; color:#c62828; border:1px solid #ef9a9a; }
        
        .table-wrap { overflow-x:auto; border-radius:12px; }
        
        @media (max-width:900px) {
            .manage-table th:nth-child(4), .manage-table td:nth-child(4) { display:none; }
        }
    </style>
</head>
<body style="background:#f4f6f9;padding-top:0;">
<?php include '../includes/admin_header.php'; ?>

<div id="toast" class="toast"></div>

<div class="container mb-2">
    <div class="orders-header">
        <h1><i class="fas fa-clipboard-list" style="color:#333;"></i> Manage Orders</h1>
        <div class="order-stats-mini">
            <?php
            $stat_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders"))['c'];
            $stat_placed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE order_status='Placed'"))['c'];
            $stat_shipped = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE order_status='Shipped'"))['c'];
            $stat_delivered = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE order_status='Delivered'"))['c'];
            ?>
            <div class="mini-stat"><span><?php echo $stat_total; ?></span> Total</div>
            <div class="mini-stat" style="border-color:#bbdefb;"><span style="color:#1565c0;"><?php echo $stat_placed; ?></span> Placed</div>
            <div class="mini-stat" style="border-color:#ffe0b2;"><span style="color:#e65100;"><?php echo $stat_shipped; ?></span> Shipped</div>
            <div class="mini-stat" style="border-color:#c8e6c9;"><span style="color:#2e7d32;"><?php echo $stat_delivered; ?></span> Delivered</div>
        </div>
    </div>
    
    <div class="table-wrap">
    <table class="manage-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Farmer</th>
                <th>Crop</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($orders)): 
                $os = $orderBadge[$row['order_status']] ?? ['bg'=>'#f5f5f5','text'=>'#616161'];
                $ps = $payBadge[$row['payment_status']] ?? ['bg'=>'#f5f5f5','text'=>'#616161'];
                $is_final = in_array($row['order_status'], ['Delivered', 'Cancelled']);
            ?>
            <tr id="order-row-<?php echo $row['order_id']; ?>">
                <td class="order-id-cell">#<?php echo $row['order_id']; ?></td>
                <td class="customer-cell">
                    <div class="name"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                    <div class="id">ID: <?php echo $row['customer_id']; ?></div>
                </td>
                <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['crop_name']); ?> (<?php echo $row['quantity']; ?>kg)</td>
                <td>
                    <i class="fas <?php echo strtoupper($row['payment_method']) === 'COD' ? 'fa-money-bill-wave' : 'fa-credit-card'; ?>" 
                       style="margin-right:4px;color:#777;"></i>
                    <?php echo htmlspecialchars($row['payment_method']); ?>
                </td>
                <td>
                    <span class="status-badge" id="pay-badge-<?php echo $row['order_id']; ?>" 
                          style="background:<?php echo $ps['bg']; ?>;color:<?php echo $ps['text']; ?>;">
                        <?php echo htmlspecialchars($row['payment_status']); ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge" id="order-badge-<?php echo $row['order_id']; ?>" 
                          style="background:<?php echo $os['bg']; ?>;color:<?php echo $os['text']; ?>;">
                        <?php echo htmlspecialchars($row['order_status']); ?>
                    </span>
                </td>
                <td>
                    <?php if (!$is_final): ?>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <select class="action-select" id="action-<?php echo $row['order_id']; ?>">
                            <option value="">Select...</option>
                            <?php if ($row['order_status'] === 'Placed'): ?>
                                <option value="approve">✓ Approve</option>
                            <?php endif; ?>
                            <?php if (in_array($row['order_status'], ['Placed', 'Approved'])): ?>
                                <option value="ship">🚚 Mark as Shipped</option>
                            <?php endif; ?>
                            <?php if (in_array($row['order_status'], ['Shipped', 'Approved'])): ?>
                                <option value="deliver">📦 Mark as Delivered</option>
                            <?php endif; ?>
                            <option value="cancel">✕ Cancel</option>
                        </select>
                        <button class="action-btn action-btn-go" onclick="updateOrder(<?php echo $row['order_id']; ?>)">Go</button>
                    </div>
                    <?php else: ?>
                        <span style="color:#999;font-size:0.82rem;"><i class="fas fa-lock"></i> Final</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
const orderBadgeMap = <?php echo json_encode($orderBadge); ?>;
const payBadgeMap = <?php echo json_encode($payBadge); ?>;

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.className = 'toast toast-' + type + ' show';
    t.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + msg;
    setTimeout(() => t.classList.remove('show'), 3500);
}

function updateOrder(orderId) {
    const sel = document.getElementById('action-' + orderId);
    if (!sel) return;
    const action = sel.value;
    if (!action) { showToast('Please select an action', 'error'); return; }
    
    const btn = sel.nextElementSibling;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    const fd = new FormData();
    fd.append('order_id', orderId);
    fd.append('action', action);
    
    fetch('<?php echo BASE_URL; ?>admin/update_order.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Update badges inline
            const ob = document.getElementById('order-badge-' + orderId);
            const pb = document.getElementById('pay-badge-' + orderId);
            const oColors = orderBadgeMap[data.new_order_status] || {bg:'#f5f5f5',text:'#616161'};
            const pColors = payBadgeMap[data.new_payment_status] || {bg:'#f5f5f5',text:'#616161'};
            
            ob.textContent = data.new_order_status;
            ob.style.background = oColors.bg;
            ob.style.color = oColors.text;
            
            pb.textContent = data.new_payment_status;
            pb.style.background = pColors.bg;
            pb.style.color = pColors.text;
            
            // If final state, replace action cell
            if (data.new_order_status === 'Delivered' || data.new_order_status === 'Cancelled') {
                const row = document.getElementById('order-row-' + orderId);
                const actionCell = row.querySelector('td:last-child');
                actionCell.innerHTML = '<span style="color:#999;font-size:0.82rem;"><i class="fas fa-lock"></i> Final</span>';
            } else {
                // Refresh page to update dropdown options
                setTimeout(() => location.reload(), 800);
            }
        } else {
            showToast(data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Go';
        }
    })
    .catch(() => {
        showToast('Network error', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Go';
    });
}
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
