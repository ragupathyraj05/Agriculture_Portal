<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_URL . 'customer/dashboard.php');
    exit();
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $mobile   = trim($_POST['mobile'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $state    = trim($_POST['state'] ?? '');
    $pincode  = trim($_POST['pincode'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_pw = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : $password;

    $old = $_POST;

    if (empty($name)) $errors[] = "Full Name is required.";
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) $errors[] = "Valid 10-digit mobile number required.";
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_pw) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE mobile = ? OR email = ?");
        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $mobile, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = "Mobile number or Email already registered.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (empty($errors)) {
        $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO customers (name, mobile, email, address, city, state, pincode, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "ssssssss", $name, $mobile, $email, $address, $city, $state, $pincode, $hashed_pw);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: ' . BASE_URL . 'customer/login.php?msg=registered');
                exit();
            } else {
                $errors[] = "Database error: " . mysqli_error($conn);
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-form-container">
    <div class="auth-form-card wide">
        <div class="auth-form-header">
            <i class="fas fa-shopping-basket" style="color:var(--secondary);"></i>
            <h2 style="color:var(--text-main);">Customer Registration</h2>
            <p>Create an account to order directly from farmers.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <div>
                    <?php foreach ($errors as $e): ?>
                        <div><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="form-group">
                <label>Full Name <span style="color:red">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($old['name']??''); ?>" required>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Mobile Number <span style="color:red">*</span></label>
                    <input type="tel" name="mobile" maxlength="10" value="<?php echo htmlspecialchars($old['mobile']??''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($old['email']??''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="2"><?php echo htmlspecialchars($old['address']??''); ?></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($old['city']??''); ?>">
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" value="<?php echo htmlspecialchars($old['state']??''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Pincode</label>
                <input type="text" name="pincode" maxlength="6" value="<?php echo htmlspecialchars($old['pincode']??''); ?>">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Password <span style="color:red">*</span></label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password <span style="color:red">*</span></label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-secondary" style="width:100%;margin-top:0.5rem;">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <div class="auth-toggle">
            Already have an account? <a href="<?php echo BASE_URL; ?>customer/login.php" style="color:var(--secondary);">Login</a>
        </div>
        <div class="text-center" style="margin-top:1rem;">
            <a href="<?php echo BASE_URL; ?>auth/role_select.php" style="color:var(--text-muted);font-size:0.85rem;">
                <i class="fas fa-arrow-left"></i> Back to Role Selection
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
