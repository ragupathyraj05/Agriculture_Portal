<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/admin_auth_check.php';
checkAdmin();

$success = '';
$error   = '';

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $status = ($action === 'approve') ? 'available' : (($action === 'reject') ? 'rejected' : '');
    
    if ($status) {
        $stmt = mysqli_prepare($conn, "UPDATE crops SET status=? WHERE crop_id=?");
        if (!$stmt) {
            $error = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "si", $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Crop status updated to " . ucfirst($status) . ".";
                // Log activity
                $admin_id   = (int)($_SESSION['admin_id'] ?? 0);
                $action_type = ($action === 'approve') ? 'Crop Approved' : 'Crop Rejected';
                $desc        = "Crop #$id " . ($action === 'approve' ? 'approved and made available' : 'rejected by admin');
                $log_stmt = mysqli_prepare($conn,
                    "INSERT INTO admin_activity_logs (admin_id, action_type, description, target_id) VALUES (?, ?, ?, ?)");
                if ($log_stmt) {
                    mysqli_stmt_bind_param($log_stmt, 'issi', $admin_id, $action_type, $desc, $id);
                    mysqli_stmt_execute($log_stmt);
                    mysqli_stmt_close($log_stmt);
                }
            } else {
                $error = "Failed to update status.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Filter
$filter = $_GET['status'] ?? 'all';
$query = "SELECT c.*, f.name AS farmer_name FROM crops c JOIN farmers f ON c.farmer_id = f.farmer_id";
if ($filter !== 'all') {
    $query .= " WHERE c.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}
$query .= " ORDER BY c.created_at DESC";
$crops = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Crops – Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; color: #555; }
        .crop-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }
        .bg-available { background: #e8f5e9; color: #2e7d32; }
        .bg-rejected { background: #ffebee; color: #c62828; }
        .bg-sold { background: #e3f2fd; color: #1976d2; }
    </style>
</head>
<body style="background:#f9f9f9;">
<?php include '../includes/admin_header.php'; ?>

<div class="container mb-2">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 style="color:var(--text-main);">Crop Moderation</h1>
        <div>
            <a href="?status=all" class="btn btn-outline" style="padding:0.5rem 1rem;">All</a>
            <a href="?status=available" class="btn btn-outline" style="padding:0.5rem 1rem;color:green;border-color:green;">Available</a>
            <a href="?status=rejected" class="btn btn-outline" style="padding:0.5rem 1rem;color:red;border-color:red;">Rejected</a>
        </div>
    </div>

    <?php if (isset($success)): ?><div class="mb-1" style="background:#e8f5e9;color:#2e7d32;padding:1rem;border-radius:8px;"><?php echo $success; ?></div><?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Crop Name</th>
                <th>Farmer</th>
                <th>Price/Qty</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($crops)): ?>
            <tr>
                <td>
                    <?php if ($row['image']): ?>
                        <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($row['image']); ?>" class="crop-img">
                    <?php else: ?>
                        <div class="crop-img" style="background:#eee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-leaf"></i></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($row['crop_name']); ?></div>
                    <div style="font-size:0.85rem;color:#888;"><?php echo date('d M Y', strtotime($row['created_at'])); ?></div>
                </td>
                <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                <td>₹<?php echo $row['price_per_kg']; ?> / <?php echo $row['quantity']; ?>kg</td>
                <td>
                    <span class="badge bg-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
                </td>
                <td>
                    <?php if ($row['status'] !== 'rejected'): ?>
                        <a href="?action=reject&id=<?php echo $row['crop_id']; ?>" class="btn btn-outline" style="padding:0.3rem 0.6rem;color:red;border-color:red;font-size:0.8rem;">Reject</a>
                    <?php endif; ?>
                    <?php if ($row['status'] === 'rejected' || $row['status'] === 'expired'): ?>
                        <a href="?action=approve&id=<?php echo $row['crop_id']; ?>" class="btn btn-outline" style="padding:0.3rem 0.6rem;color:green;border-color:green;font-size:0.8rem;">Approve</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
