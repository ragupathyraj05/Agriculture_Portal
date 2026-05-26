<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if admin is logged in.
 * Redirects to login page if not authenticated.
 */
function checkAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit();
    }
}
?>
