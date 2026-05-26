<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, send to their dashboard
if (isset($_SESSION['farmer_id'])) {
    header('Location: ' . BASE_URL . 'farmer/dashboard.php');
    exit();
}
if (isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_URL . 'customer/dashboard.php');
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit();
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="auth-form-container" style="min-height:calc(100vh - 140px);">
    <div style="width:100%;max-width:1000px;">
        <div class="text-center" style="margin-bottom:2.5rem;">
            <h1 style="font-size:2.2rem;font-weight:800;color:var(--text-main);margin-bottom:0.5rem;">
                Welcome to AgriPortal
            </h1>
            <p style="color:var(--text-muted);font-size:1.05rem;">Choose your role to get started</p>
        </div>

        <div class="role-grid">
            <!-- Farmer Card -->
            <div class="role-card">
                <div class="role-card-icon" style="background:rgba(27,94,32,0.1);color:var(--primary);">
                    <i class="fas fa-tractor"></i>
                </div>
                <h3>Farmer</h3>
                <p>List your crops, manage orders, and access AI-powered predictions for better yields.</p>
                <div class="role-card-actions">
                    <a href="<?php echo BASE_URL; ?>farmer/login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>farmer/signup.php" class="btn btn-outline">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                </div>
            </div>

            <!-- Customer Card -->
            <div class="role-card">
                <div class="role-card-icon" style="background:rgba(102,187,106,0.15);color:var(--accent);">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <h3>Customer</h3>
                <p>Buy fresh crops directly from local farmers at the best prices, delivered to your door.</p>
                <div class="role-card-actions">
                    <a href="<?php echo BASE_URL; ?>customer/login.php" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>customer/signup.php" class="btn btn-outline" style="border-color:var(--accent);color:var(--accent);">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                </div>
            </div>

            <!-- Admin Card -->
            <div class="role-card">
                <div class="role-card-icon" style="background:rgba(51,51,51,0.08);color:#333;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Admin</h3>
                <p>Manage the platform, monitor users, oversee crops and orders system-wide.</p>
                <div class="role-card-actions">
                    <a href="<?php echo BASE_URL; ?>admin/login.php" class="btn btn-admin">
                        <i class="fas fa-sign-in-alt"></i> Admin Login
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center" style="margin-top:2.5rem;">
            <a href="<?php echo BASE_URL; ?>index.php" style="color:var(--text-muted);font-size:0.9rem;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
