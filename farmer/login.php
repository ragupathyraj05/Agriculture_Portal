<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['farmer_id'])) {
    header('Location: ' . BASE_URL . 'farmer/dashboard.php');
    exit();
}

$errors  = [];
$success = '';
$old     = [];

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'registered') {
        $success = 'Registration successful! Please log in.';
    } elseif ($_GET['msg'] === 'logged_out') {
        $success = 'You have been logged out successfully.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile   = trim($_POST['mobile']   ?? '');
    $password = $_POST['password']      ?? '';
    $old      = ['mobile' => $mobile];

    if (empty($mobile)) {
        $errors[] = 'Mobile number is required.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors[] = 'Enter a valid 10-digit Indian mobile number.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT farmer_id, name, password FROM farmers WHERE mobile = ?");
        if (!$stmt) {
            $errors[] = 'Database error. Please try again later.';
        } else {
            mysqli_stmt_bind_param($stmt, 's', $mobile);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $farmer = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($farmer && password_verify($password, $farmer['password'])) {
                session_regenerate_id(true);
                $_SESSION['farmer_id']   = $farmer['farmer_id'];
                $_SESSION['farmer_name'] = $farmer['name'];
                header('Location: ' . BASE_URL . 'farmer/dashboard.php');
                exit();
            } else {
                $errors[] = 'Invalid mobile number or password.';
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-form-container">
    <div class="auth-form-card">
        <div class="auth-form-header">
            <i class="fas fa-tractor"></i>
            <h2>Farmer Login</h2>
            <p>Welcome back! Sign in to manage your crops.</p>
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

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" name="mobile" placeholder="10-digit mobile number"
                       value="<?php echo htmlspecialchars($old['mobile'] ?? ''); ?>" maxlength="10" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" onclick="togglePwd('password')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#999;cursor:pointer;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;margin-top:0.5rem;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="auth-toggle">
            Don't have an account? <a href="<?php echo BASE_URL; ?>farmer/signup.php">Sign Up</a>
        </div>
        <div class="text-center" style="margin-top:1rem;">
            <a href="<?php echo BASE_URL; ?>auth/role_select.php" style="color:var(--text-muted);font-size:0.85rem;">
                <i class="fas fa-arrow-left"></i> Back to Role Selection
            </a>
        </div>
    </div>
</div>

<script>
function togglePwd(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>

<?php include '../includes/footer.php'; ?>
