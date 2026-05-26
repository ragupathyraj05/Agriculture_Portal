<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/customer_auth_check.php';

$customer_id = (int)$_SESSION['customer_id'];
$errors  = [];
$success = '';

// Fetch current profile
$stmt = mysqli_prepare($conn, "SELECT * FROM customers WHERE customer_id = ?");
if (!$stmt) { header('Location: ' . BASE_URL . 'customer/dashboard.php'); exit(); }
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$customer = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$customer) { session_destroy(); header('Location: ' . BASE_URL . 'customer/login.php'); exit(); }

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if (empty($name)) $errors[] = 'Name is required.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE customers SET name=?, email=?, address=?, city=?, state=?, pincode=? WHERE customer_id=?");
        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $email, $address, $city, $state, $pincode, $customer_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['customer_name'] = $name;
                $success = 'Profile updated successfully!';
                $customer['name'] = $name; $customer['email'] = $email; $customer['address'] = $address;
                $customer['city'] = $city; $customer['state'] = $state; $customer['pincode'] = $pincode;
            } else {
                $errors[] = 'Failed to update profile.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<style>
    .edit-container { max-width: 600px; margin: 2rem auto; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
</style>

<div class="container mb-2">
    <div class="edit-container">
        <div class="text-center mb-1">
            <h1 style="color:var(--text-main);"><i class="fas fa-user-edit" style="color:var(--accent);"></i> Edit Profile</h1>
            <p style="color:var(--text-muted);">Update your customer profile information</p>
        </div>

        <?php if ($success): ?>
            <div class="mb-1" style="background:#e8f5e9;color:#2e7d32;padding:0.8rem;border-radius:8px;font-size:0.9rem;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-1" style="background:#ffebee;color:#c62828;padding:0.8rem;border-radius:8px;font-size:0.9rem;">
                <?php foreach ($errors as $e): ?>
                    <div><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" maxlength="10" value="<?php echo htmlspecialchars($customer['pincode'] ?? ''); ?>">
                </div>

                <div class="form-group" style="margin-top:1rem;">
                    <label>Mobile (cannot be changed)</label>
                    <input type="text" value="<?php echo htmlspecialchars($customer['mobile']); ?>" disabled style="opacity:0.6;">
                </div>

                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="submit" class="btn btn-accent" style="flex:1;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="<?php echo BASE_URL; ?>customer/dashboard.php" class="btn btn-outline" style="flex:1;border-color:var(--text-muted);color:var(--text-muted);">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
