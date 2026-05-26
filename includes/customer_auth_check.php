<?php
// Customer auth guard - include at top of any customer-protected page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_URL . 'customer/login.php');
    exit();
}
?>
