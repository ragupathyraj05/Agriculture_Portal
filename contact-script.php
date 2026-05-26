<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submit"])) {
    $user_name    = trim($_POST['user_name'] ?? '');
    $user_mobile  = trim($_POST['user_mobile'] ?? '');
    $user_email   = trim($_POST['user_email'] ?? '');
    $user_address = trim($_POST['user_address'] ?? '');
    $user_message = trim($_POST['user_message'] ?? '');

    // Server-side validation
    $errors = [];
    if (empty($user_name))    $errors[] = 'Name is required.';
    if (empty($user_mobile))  $errors[] = 'Mobile number is required.';
    if (empty($user_email))   $errors[] = 'Email is required.';
    if ($user_email && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (empty($user_message)) $errors[] = 'Message is required.';

    if (!empty($errors)) {
        // Redirect back with error
        header('Location: contact.php?error=' . urlencode(implode(' ', $errors)));
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO contactus (c_name, c_mobile, c_email, c_address, c_message) VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        header('Location: contact.php?error=' . urlencode('Database error. Please try again.'));
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sssss", $user_name, $user_mobile, $user_email, $user_address, $user_message);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header('Location: index.php?msg=contact_success');
        exit();
    } else {
        mysqli_stmt_close($stmt);
        header('Location: contact.php?error=' . urlencode('Failed to send message. Please try again.'));
        exit();
    }
}

// If accessed via GET, redirect to contact page
header('Location: contact.php');
exit();
?>