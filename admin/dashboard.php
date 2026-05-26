<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/admin_auth_check.php';
checkAdmin();

// Stats
$stats = [];
$stats['farmers']   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM farmers"))['c'];
$stats['customers'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM customers"))['c'];
$stats['crops']     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM crops WHERE status='available'"))['c'];
$stats['orders']    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$stats['pending']   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE order_status='Placed'"))['c'];
$stats['cod_awaiting'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE payment_status='COD - Awaiting Delivery'"))['c'];
$stats['delivered_today'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE order_status='Delivered' AND DATE(delivered_at) = CURDATE()"))['c'];

// Recent Activity Logs
$date_filter = isset($_GET['log_date']) ? $_GET['log_date'] : '';
$log_sql = "SELECT l.*, a.username AS admin_name FROM admin_activity_logs l LEFT JOIN admins a ON l.admin_id = a.id";
if ($date_filter) {
    $log_sql .= " WHERE DATE(l.created_at) = '" . mysqli_real_escape_string($conn, $date_filter) . "'";
}
$log_sql .= " ORDER BY l.created_at DESC LIMIT 15";
$logs = mysqli_query($conn, $log_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Agriculture Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-top: 1.5rem; }
        .stat-card { background: #fff; padding: 1.5rem; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1rem; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
        .stat-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .stat-info h3 { font-size: 1.8rem; margin: 0; line-height: 1; color: var(--text-main); }
        .stat-info p { margin: 0; color: var(--text-muted); font-size: 0.82rem; margin-top: 0.2rem; }
        
        .c-green { background: #e8f5e9; color: #2e7d32; }
        .c-blue { background: #e3f2fd; color: #1976d2; }
        .c-orange { background: #fff3e0; color: #ef6c00; }
        .c-purple { background: #f3e5f5; color: #7b1fa2; }
        .c-teal { background: #e0f2f1; color: #00695c; }
        .c-amber { background: #fffde7; color: #f9a825; }
        .c-red { background: #fce4ec; color: #c62828; }
        
        .dash-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
        @media (max-width: 800px) { .dash-two-col { grid-template-columns: 1fr; } }
        
        .section-card { background: #fff; border-radius: 14px; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
        .section-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
        .section-card h3 i { color: #555; }
        
        .log-table { width: 100%; border-collapse: collapse; }
        .log-table th { text-align: left; padding: 0.7rem 0.8rem; font-size: 0.78rem; text-transform: uppercase; color: #999; border-bottom: 1px solid #eee; letter-spacing: 0.5px; }
        .log-table td { padding: 0.7rem 0.8rem; font-size: 0.85rem; border-bottom: 1px solid #f5f5f5; }
        .log-table tbody tr:hover { background: rgba(232,245,233,0.2); }
        .log-action-badge { padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
        
        .date-filter { display: flex; align-items: center; gap: 8px; margin-bottom: 1rem; }
        .date-filter input { padding: 6px 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 0.85rem; }
        .date-filter button { padding: 6px 16px; border: none; background: linear-gradient(135deg,#1B5E20,#2E7D32); color: #fff; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.82rem; font-weight: 600; }
        
        .quick-nav { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.8rem; }
        .quick-nav-item { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1rem; border-radius: 12px; background: #f9f9f9; border: 1px solid #eee; text-decoration: none; color: var(--text-main); font-size: 0.82rem; font-weight: 600; transition: all 0.2s; }
        .quick-nav-item:hover { background: #e8f5e9; border-color: var(--accent); transform: translateY(-2px); }
        .quick-nav-item i { font-size: 1.3rem; color: var(--primary); }
    </style>
</head>
<body style="background:#f4f6f9;padding-top:0;">
<?php include '../includes/admin_header.php'; ?>

<div class="container mb-2">
    <h1 style="color:var(--text-main);margin-bottom:0.2rem;"><i class="fas fa-tachometer-alt" style="color:#333;"></i> Admin Dashboard</h1>
    <p style="color:var(--text-muted);font-size:0.9rem;">Overview of your agriculture marketplace</p>
    
    <!-- Stats Grid -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon c-purple"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['orders']; ?></h3>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon c-blue"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon c-amber"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['cod_awaiting']; ?></h3>
                <p>COD Awaiting</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon c-green"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['delivered_today']; ?></h3>
                <p>Delivered Today</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon c-green"><i class="fas fa-tractor"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['farmers']; ?></h3>
                <p>Total Farmers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon c-teal"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['customers']; ?></h3>
                <p>Total Customers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon c-orange"><i class="fas fa-leaf"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['crops']; ?></h3>
                <p>Active Crops</p>
            </div>
        </div>
    </div>
    
    <!-- Two-column: Activity + Quick Nav -->
    <div class="dash-two-col">
        <!-- Recent Activity -->
        <div class="section-card" id="activity">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <form class="date-filter" method="GET">
                <input type="date" name="log_date" value="<?php echo htmlspecialchars($date_filter); ?>">
                <button type="submit"><i class="fas fa-filter"></i> Filter</button>
                <?php if ($date_filter): ?>
                <a href="dashboard.php" style="font-size:0.82rem;color:var(--primary);margin-left:8px;">Clear</a>
                <?php endif; ?>
            </form>
            <?php if ($logs && mysqli_num_rows($logs) > 0): ?>
            <div style="overflow-x:auto;">
            <table class="log-table">
                <thead>
                    <tr><th>Action</th><th>Description</th><th>Time</th></tr>
                </thead>
                <tbody>
                <?php 
                $actionColors = [
                    'Order Approved' => ['bg'=>'#e0f2f1','text'=>'#00695c'],
                    'Order Shipped'  => ['bg'=>'#fff3e0','text'=>'#e65100'],
                    'Order Delivered' => ['bg'=>'#e8f5e9','text'=>'#2e7d32'],
                    'Order Cancelled' => ['bg'=>'#ffebee','text'=>'#c62828'],
                    'User Deleted'   => ['bg'=>'#fce4ec','text'=>'#ad1457'],
                    'Crop Approved'  => ['bg'=>'#e3f2fd','text'=>'#1565c0'],
                ];
                while ($log = mysqli_fetch_assoc($logs)):
                    $ac = $actionColors[$log['action_type']] ?? ['bg'=>'#f5f5f5','text'=>'#616161'];
                ?>
                <tr>
                    <td>
                        <span class="log-action-badge" style="background:<?php echo $ac['bg']; ?>;color:<?php echo $ac['text']; ?>;">
                            <?php echo htmlspecialchars($log['action_type']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                    <td style="white-space:nowrap;color:#999;font-size:0.8rem;">
                        <?php echo date('d M, h:i A', strtotime($log['created_at'])); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                <i class="fas fa-clipboard-list" style="font-size:2rem;margin-bottom:0.5rem;display:block;opacity:0.4;"></i>
                <?php echo $date_filter ? 'No activity found for this date.' : 'No activity logged yet. Actions will appear here.'; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Navigation + System Status -->
        <div>
            <div class="section-card" style="margin-bottom:1.2rem;">
                <h3><i class="fas fa-th-large"></i> Quick Navigation</h3>
                <div class="quick-nav">
                    <a href="<?php echo BASE_URL; ?>admin/orders.php" class="quick-nav-item">
                        <i class="fas fa-clipboard-list"></i> Manage Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/farmers.php" class="quick-nav-item">
                        <i class="fas fa-tractor"></i> Farmers
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/customers.php" class="quick-nav-item">
                        <i class="fas fa-users"></i> Customers
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/crops.php" class="quick-nav-item">
                        <i class="fas fa-leaf"></i> Crops
                    </a>
                </div>
            </div>
            <div class="section-card">
                <h3><i class="fas fa-server"></i> System Status</h3>
                <div style="display:flex;align-items:center;gap:0.5rem;color:#2e7d32;font-weight:600;margin-bottom:0.8rem;">
                    <i class="fas fa-check-circle"></i> All Systems Operational
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted);">
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px solid #f5f5f5;">
                        <span>Database</span><span style="color:#2e7d32;">● Online</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px solid #f5f5f5;">
                        <span>Web Server</span><span style="color:#2e7d32;">● Online</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0;">
                        <span>ML Services</span><span style="color:#2e7d32;">● Available</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
