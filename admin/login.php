<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check DB connection
if (!$conn) {
    die("Database connection failed. Please check your MySQL server.");
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    }

    if (empty($error)) {
        // Database lookup using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT admin_id, username, password FROM admins WHERE username = ?");
        if (!$stmt) {
            $error = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $admin  = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($admin && password_verify($password, $admin['password'])) {
                // Secure session
                session_regenerate_id(true);
                $_SESSION['admin_id']    = $admin['admin_id'];
                $_SESSION['admin_name']  = $admin['username'];
                header('Location: ' . BASE_URL . 'admin/dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="auth-form-container">
    <div class="auth-form-card">
        <div class="auth-form-header">
            <i class="fas fa-user-shield"></i>
            <h2>Admin Login</h2>
            <p>Sign in to manage the platform</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate id="admin-login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
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

        <div class="text-center" style="margin-top:1.5rem;">
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
