<?php
require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear admin session keys
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);

// If no other role is logged in, destroy the session entirely
if (!isset($_SESSION['farmer_id']) && !isset($_SESSION['customer_id'])) {
    session_unset();
    session_destroy();
}

header('Location: ' . BASE_URL . 'admin/login.php');
exit();
?>
