<?php
// Auth guard - include at top of any farmer-protected page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['farmer_id'])) {
    header('Location: ' . BASE_URL . 'farmer/login.php');
    exit();
}
?>
