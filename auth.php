<?php
// Redirect to the new role select page
require_once __DIR__ . '/includes/config.php';
header('Location: ' . BASE_URL . 'auth/role_select.php');
exit();
