<?php
require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear customer session data only
unset($_SESSION['customer_id'], $_SESSION['customer_name']);

// If no other session data remains, destroy fully
if (empty($_SESSION)) {
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

header('Location: ' . BASE_URL . 'customer/login.php?msg=logged_out');
exit();
?>
