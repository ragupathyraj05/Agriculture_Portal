<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/admin_auth_check.php';
checkAdmin();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Orders have ON DELETE RESTRICT. So we cannot delete farmer if they have orders.
    $check = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE farmer_id=$id");
    $count = mysqli_fetch_assoc($check)['c'];

    if ($count > 0) {
        $error = "Cannot delete farmer with existing orders.";
    } else {
        // Fetch farmer name for the log before deleting
        $fname_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM farmers WHERE farmer_id=$id"));
        $fname = $fname_row ? $fname_row['name'] : "ID #$id";

        if (mysqli_query($conn, "DELETE FROM farmers WHERE farmer_id=$id")) {
            $success = "Farmer deleted successfully.";
            // Log activity
            $admin_id   = (int)($_SESSION['admin_id'] ?? 0);
            $action_type = 'User Deleted';
            $desc        = "Farmer \"$fname\" (ID #$id) deleted by admin";
            $log_stmt = mysqli_prepare($conn,
                "INSERT INTO admin_activity_logs (admin_id, action_type, description, target_id) VALUES (?, ?, ?, ?)");
            if ($log_stmt) {
                mysqli_stmt_bind_param($log_stmt, 'issi', $admin_id, $action_type, $desc, $id);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);
            }
        } else {
            $error = "Failed to delete farmer.";
        }
    }
}

$farmers = mysqli_query($conn, "SELECT * FROM farmers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Farmers – Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; color: #555; }
    </style>
</head>
<body style="background:#f9f9f9;">
<?php include '../includes/admin_header.php'; ?>

<div class="container mb-2">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 style="color:var(--text-main);">Farmers Configuration</h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="mb-1" style="background:#ffebee;color:#c62828;padding:1rem;border-radius:8px;"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="mb-1" style="background:#e8f5e9;color:#2e7d32;padding:1rem;border-radius:8px;"><?php echo $success; ?></div>
    <?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Location</th>
                <th>Farm Size</th>
                <th>Join Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($farmers)): ?>
            <tr>
                <td>#<?php echo $row['farmer_id']; ?></td>
                <td>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div style="font-size:0.85rem;color:#888;"><?php echo htmlspecialchars($row['email']); ?></div>
                </td>
                <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                <td><?php echo htmlspecialchars($row['district'] . ', ' . $row['state']); ?></td>
                <td><?php echo $row['farm_size']; ?> acres</td>
                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="?delete=<?php echo $row['farmer_id']; ?>" onclick="return confirm('Delete this farmer? This will delete their crops too.')" style="color:#ff4d4d;">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
