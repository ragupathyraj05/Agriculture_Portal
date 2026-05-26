<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$adminLoggedIn = isset($_SESSION['admin_id']);
$adminName = $adminLoggedIn ? htmlspecialchars($_SESSION['admin_email'] ?? 'Admin') : '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
    .admin-nav { background: #1a1a2e; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 15px rgba(0,0,0,0.2); }
    .admin-nav-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; height: 60px; padding: 0 1.5rem; }
    .admin-brand { font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
    .admin-brand i { color: #4fc3f7; }
    .admin-badge { font-size: 0.65rem; background: #4fc3f7; color: #1a1a2e; padding: 2px 8px; border-radius: 4px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .admin-links { display: flex; list-style: none; gap: 0; margin: 0; padding: 0; }
    .admin-links a { color: rgba(255,255,255,0.7); text-decoration: none; padding: 0.5rem 1rem; font-size: 0.88rem; font-weight: 500; border-radius: 6px; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
    .admin-links a:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .admin-links a.active { color: #4fc3f7; background: rgba(79,195,247,0.1); }
    .admin-user { display: flex; align-items: center; gap: 1rem; }
    .admin-user-name { color: rgba(255,255,255,0.6); font-size: 0.85rem; }
    .admin-logout { color: #ff6b6b !important; border: 1px solid rgba(255,107,107,0.3); padding: 0.4rem 1rem !important; border-radius: 6px; font-size: 0.85rem; text-decoration: none; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
    .admin-logout:hover { background: rgba(255,107,107,0.1); border-color: #ff6b6b; }
    @media (max-width: 768px) { .admin-links { display: none; } .admin-nav-inner { padding: 0 1rem; } }
</style>
<nav class="admin-nav">
    <div class="admin-nav-inner">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="admin-brand">
            <i class="fas fa-seedling"></i> AgriPortal <span class="admin-badge">Admin</span>
        </a>
        
        <?php if ($adminLoggedIn): ?>
        <div class="admin-links">
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo $currentPage=='dashboard.php'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?php echo BASE_URL; ?>admin/farmers.php" class="<?php echo $currentPage=='farmers.php'?'active':''; ?>"><i class="fas fa-tractor"></i> Farmers</a>
            <a href="<?php echo BASE_URL; ?>admin/customers.php" class="<?php echo $currentPage=='customers.php'?'active':''; ?>"><i class="fas fa-users"></i> Customers</a>
            <a href="<?php echo BASE_URL; ?>admin/crops.php" class="<?php echo $currentPage=='crops.php'?'active':''; ?>"><i class="fas fa-leaf"></i> Crops</a>
            <a href="<?php echo BASE_URL; ?>admin/orders.php" class="<?php echo $currentPage=='orders.php'?'active':''; ?>"><i class="fas fa-shopping-bag"></i> Orders</a>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php#activity" class=""><i class="fas fa-history"></i> Activity</a>
        </div>
        <div class="admin-user">
            <span class="admin-user-name"><i class="fas fa-user-shield"></i> <?php echo $adminName; ?></span>
            <a href="<?php echo BASE_URL; ?>admin/logout.php" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <?php endif; ?>
    </div>
</nav>
