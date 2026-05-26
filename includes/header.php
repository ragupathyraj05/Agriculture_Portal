<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$farmerLoggedIn   = isset($_SESSION['farmer_id']);
$customerLoggedIn = isset($_SESSION['customer_id']);
$farmerName       = $farmerLoggedIn   ? htmlspecialchars($_SESSION['farmer_name'])   : '';
$customerName     = $customerLoggedIn ? htmlspecialchars($_SESSION['customer_name']) : '';
$currentPage      = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriculture Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar" id="main-navbar">
        <a href="<?php echo BASE_URL; ?>index.php" class="navbar-brand">
            <i class="fas fa-leaf"></i> AgriPortal
        </a>

        <ul class="nav-links">
            <li><a href="<?php echo BASE_URL; ?>index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>contact.php" class="nav-link <?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">About Us</a></li>
            <li><a href="<?php echo BASE_URL; ?>news.php" class="nav-link <?php echo $currentPage === 'news.php' ? 'active' : ''; ?>">News</a></li>
            <li><a href="<?php echo BASE_URL; ?>chatbot.php" class="nav-link <?php echo $currentPage === 'chatbot.php' ? 'active' : ''; ?>">AI Chat</a></li>
            <?php if ($farmerLoggedIn || $customerLoggedIn): ?>
            <li><a href="<?php echo BASE_URL; ?>crop_prediction.php" class="nav-link">Prediction</a></li>
            <li><a href="<?php echo BASE_URL; ?>crop_recommendation.php" class="nav-link">Recommendation</a></li>
            <?php endif; ?>
            <?php if ($customerLoggedIn): ?>
            <li><a href="<?php echo BASE_URL; ?>customer/marketplace.php" class="nav-link" style="color:var(--primary);font-weight:600;"><i class="fas fa-store-alt"></i> Buy Crops</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-auth">
            <?php if ($farmerLoggedIn): ?>
                <span class="nav-user-name">Hi, <?php echo $farmerName; ?></span>
                <a href="<?php echo BASE_URL; ?>farmer/dashboard.php" class="btn-nav-login">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>farmer/logout.php" class="btn-nav-logout">Logout</a>
            <?php elseif ($customerLoggedIn): ?>
                <span class="nav-user-name">Hi, <?php echo $customerName; ?></span>
                <a href="<?php echo BASE_URL; ?>customer/dashboard.php" class="btn-nav-login">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>customer/logout.php" class="btn-nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/role_select.php" class="btn-nav-login">Login</a>
                <a href="<?php echo BASE_URL; ?>auth/role_select.php" class="btn-nav-signup">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>
