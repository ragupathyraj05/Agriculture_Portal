<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

$farmer_id = (int)$_SESSION['farmer_id'];
$action    = $_GET['action'] ?? 'list';
$errors    = [];
$success   = '';
$old       = [];

// ─────────────────────────────────────────────
// Helper: upload a crop image
// Returns filename on success, null on skip, error string on failure
// ─────────────────────────────────────────────
function uploadCropImage(array $file): ?string {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return '__error__:Upload failed (error code ' . $file['error'] . ').';
    }

    $maxSize  = 2 * 1024 * 1024; // 2 MB
    $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
    $extMap   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if ($file['size'] > $maxSize) {
        return '__error__:Image must be under 2 MB.';
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        return '__error__:Only JPG, PNG, and WebP images are allowed.';
    }

    $ext      = $extMap[$mime];
    $filename = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest     = __DIR__ . '/../uploads/crops/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return '__error__:Could not save the image. Check folder permissions.';
    }
    return $filename;
}

function deleteCropImage(?string $filename): void {
    if ($filename) {
        $path = __DIR__ . '/../uploads/crops/' . $filename;
        if (file_exists($path)) unlink($path);
    }
}

// ─────────────────────────────────────────────
// ACTION: store (POST add)
// ─────────────────────────────────────────────
if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_name    = trim($_POST['crop_name']    ?? '');
    $quantity     = trim($_POST['quantity']     ?? '');
    $price_per_kg = trim($_POST['price_per_kg'] ?? '');
    $harvest_date = trim($_POST['harvest_date'] ?? '');
    $description  = trim($_POST['description']  ?? '');
    $status       = $_POST['status']            ?? 'available';
    $old          = compact('crop_name','quantity','price_per_kg','harvest_date','description','status');

    $validStatuses = ['available','sold','reserved','expired'];
    if (empty($crop_name))                          $errors[] = 'Crop name is required.';
    if (!is_numeric($quantity) || $quantity <= 0)   $errors[] = 'Quantity must be a positive number.';
    if (!is_numeric($price_per_kg) || $price_per_kg <= 0) $errors[] = 'Price per kg must be a positive number.';
    if (!in_array($status, $validStatuses))         $errors[] = 'Invalid status.';
    if (!empty($harvest_date) && !strtotime($harvest_date)) $errors[] = 'Invalid harvest date.';

    $imageFile = null;
    if (isset($_FILES['image'])) {
        $result = uploadCropImage($_FILES['image']);
        if ($result && str_starts_with($result, '__error__:')) {
            $errors[] = substr($result, 10);
        } else {
            $imageFile = $result;
        }
    }

    if (empty($errors)) {
        $hd = $harvest_date ?: null;
        $stmt = mysqli_prepare($conn,
            "INSERT INTO crops (farmer_id, crop_name, quantity, price_per_kg, harvest_date, description, image, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'isddssss',
                $farmer_id, $crop_name, $quantity, $price_per_kg, $hd, $description, $imageFile, $status
            );
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: ' . BASE_URL . 'farmer/crops.php?success=added');
                exit();
            }
            $errors[] = 'Failed to save crop. Please try again.';
            mysqli_stmt_close($stmt);
        }
    }
    $action = 'add'; 

// ─────────────────────────────────────────────
// ACTION: update (POST edit)
// ─────────────────────────────────────────────
} elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_id      = (int)($_POST['crop_id'] ?? 0);
    $crop_name    = trim($_POST['crop_name']    ?? '');
    $quantity     = trim($_POST['quantity']     ?? '');
    $price_per_kg = trim($_POST['price_per_kg'] ?? '');
    $harvest_date = trim($_POST['harvest_date'] ?? '');
    $description  = trim($_POST['description']  ?? '');
    $status       = $_POST['status']            ?? 'available';
    $old          = compact('crop_id','crop_name','quantity','price_per_kg','harvest_date','description','status');

    // Ownership check
    $chk = mysqli_prepare($conn, "SELECT image FROM crops WHERE crop_id = ? AND farmer_id = ?");
    if (!$chk) {
        $errors[] = 'Database error: ' . mysqli_error($conn);
        $existing = null;
    } else {
        mysqli_stmt_bind_param($chk, 'ii', $crop_id, $farmer_id);
        mysqli_stmt_execute($chk);
        $chkRes = mysqli_stmt_get_result($chk);
        $existing = mysqli_fetch_assoc($chkRes);
        mysqli_stmt_close($chk);
    }

    if (!$existing) {
        header('Location: ' . BASE_URL . 'farmer/crops.php?error=notfound');
        exit();
    }

    $validStatuses = ['available','sold','reserved','expired'];
    if (empty($crop_name))                          $errors[] = 'Crop name is required.';
    if (!is_numeric($quantity) || $quantity <= 0)   $errors[] = 'Quantity must be a positive number.';
    if (!is_numeric($price_per_kg) || $price_per_kg <= 0) $errors[] = 'Price per kg must be a positive number.';
    if (!in_array($status, $validStatuses))         $errors[] = 'Invalid status.';
    if (!empty($harvest_date) && !strtotime($harvest_date)) $errors[] = 'Invalid harvest date.';

    $newImage = $existing['image']; 
    if (isset($_FILES['image'])) {
        $result = uploadCropImage($_FILES['image']);
        if ($result && str_starts_with($result, '__error__:')) {
            $errors[] = substr($result, 10);
        } elseif ($result) {
            deleteCropImage($existing['image']); 
            $newImage = $result;
        }
    }

    if (empty($errors)) {
        $hd = $harvest_date ?: null;
        $stmt = mysqli_prepare($conn,
            "UPDATE crops SET crop_name=?, quantity=?, price_per_kg=?, harvest_date=?, description=?, image=?, status=?
             WHERE crop_id=? AND farmer_id=?"
        );
        if (!$stmt) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'sddssssii',
                $crop_name, $quantity, $price_per_kg, $hd, $description, $newImage, $status, $crop_id, $farmer_id
            );
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: ' . BASE_URL . 'farmer/crops.php?success=updated');
                exit();
            }
            $errors[] = 'Failed to update crop. Please try again.';
            mysqli_stmt_close($stmt);
        }
    }
    $action = 'edit'; 

// ─────────────────────────────────────────────
// ACTION: delete (GET with ownership check)
// ─────────────────────────────────────────────
} elseif ($action === 'delete') {
    $crop_id = (int)($_GET['id'] ?? 0);

    $chk = mysqli_prepare($conn, "SELECT image FROM crops WHERE crop_id = ? AND farmer_id = ?");
    if (!$chk) {
        header('Location: ' . BASE_URL . 'farmer/crops.php?error=notfound');
        exit();
    }
    mysqli_stmt_bind_param($chk, 'ii', $crop_id, $farmer_id);
    mysqli_stmt_execute($chk);
    $chkRes  = mysqli_stmt_get_result($chk);
    $toDelete = mysqli_fetch_assoc($chkRes);
    mysqli_stmt_close($chk);

    if ($toDelete) {
        $stmt = mysqli_prepare($conn, "DELETE FROM crops WHERE crop_id = ? AND farmer_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $crop_id, $farmer_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            deleteCropImage($toDelete['image']);
        }
        header('Location: ' . BASE_URL . 'farmer/crops.php?success=deleted');
    } else {
        header('Location: ' . BASE_URL . 'farmer/crops.php?error=notfound');
    }
    exit();
}

// ─────────────────────────────────────────────
// DATA for edit form
// ─────────────────────────────────────────────
$editCrop = null;
if ($action === 'edit') {
    $crop_id = (int)($_GET['id'] ?? $old['crop_id'] ?? 0);
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM crops WHERE crop_id = ? AND farmer_id = ?"
    );
    if (!$stmt) {
        header('Location: ' . BASE_URL . 'farmer/crops.php?error=notfound');
        exit();
    }
    mysqli_stmt_bind_param($stmt, 'ii', $crop_id, $farmer_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $editCrop = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$editCrop) {
        header('Location: ' . BASE_URL . 'farmer/crops.php?error=notfound');
        exit();
    }
    // If we fell back from a failed update, overlay POST values
    if (!empty($old)) {
        $editCrop = array_merge($editCrop, $old);
    }
}

// ─────────────────────────────────────────────
// DATA for list view
// ─────────────────────────────────────────────
$crops = [];
if ($action === 'list') {
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM crops WHERE farmer_id = ? ORDER BY created_at DESC"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $farmer_id);
        mysqli_stmt_execute($stmt);
        $res   = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) $crops[] = $row;
        mysqli_stmt_close($stmt);
    } else {
        $errors[] = 'Could not load crops: ' . mysqli_error($conn);
    }

    if (isset($_GET['success'])) {
        $msgs = ['added' => 'Crop added successfully!', 'updated' => 'Crop updated successfully!', 'deleted' => 'Crop deleted successfully!'];
        $success = $msgs[$_GET['success']] ?? '';
    }
    if (isset($_GET['error']) && $_GET['error'] === 'notfound') {
        $errors[] = 'Crop not found or access denied.';
    }
}

// Status badge colours
$statusColors = [
    'available' => ['bg' => '#e8f5e9',  'text' => '#2e7d32'],
    'sold'      => ['bg' => '#ffebee',  'text' => '#c62828'],
    'reserved'  => ['bg' => '#fff8e1',  'text' => '#f9a825'],
    'expired'   => ['bg' => '#f8f9fa',  'text' => '#6c757d'],
];
?>
<?php include '../includes/header.php'; ?>
<style>
    .container-fluid { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .crop-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); }
    .crop-table th { background: #f8f9fa; color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.8rem; padding: 1rem; text-align: left; }
    .crop-table td { padding: 1rem; border-bottom: 1px solid #eee; vertical-align: middle; }
    .crop-table tr:last-child td { border-bottom: none; }
    .status-badge { padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #eee; }
    .form-card { background: #fff; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); max-width: 800px; margin: 0 auto; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
</style>

<div class="container-fluid">

    <?php if ($action === 'list'): ?>
    
    <div class="page-header">
        <h1><i class="fas fa-seedling" style="color:var(--primary);"></i> My Crops</h1>
        <div style="display:flex; gap:1rem;">
             <a href="<?php echo BASE_URL; ?>farmer/dashboard.php" class="btn btn-outline" style="border-color:var(--border-color);color:var(--text-muted);">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>farmer/crops.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Crop
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="mb-1" style="background:#e8f5e9;color:#2e7d32;padding:0.75rem 1rem;border-radius:8px;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($crops)): ?>
        <div class="card text-center" style="padding:4rem;">
            <i class="fas fa-seedling" style="font-size:3rem;color:var(--border-color);margin-bottom:1rem;"></i>
            <p style="color:var(--text-muted);margin-bottom:1.5rem;">You haven't posted any crops yet.</p>
            <a href="<?php echo BASE_URL; ?>farmer/crops.php?action=add" class="btn btn-primary">Post Your First Crop</a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="crop-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Crop Name</th>
                        <th>Qty (kg)</th>
                        <th>Price/kg (₹)</th>
                        <th>Harvest Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($crops as $crop): ?>
                    <tr>
                        <td>
                            <?php if ($crop['image']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/crops/' . htmlspecialchars($crop['image']); ?>" class="img-thumb">
                            <?php else: ?>
                                <div class="img-thumb" style="display:flex;align-items:center;justify-content:center;color:#ccc;"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight:600;color:var(--text-main);"><?php echo htmlspecialchars($crop['crop_name']); ?></div>
                            <?php if ($crop['description']): ?>
                                <div style="font-size:0.8rem;color:var(--text-muted);"><?php echo htmlspecialchars(mb_strimwidth($crop['description'], 0, 40, '...')); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $crop['quantity']; ?></td>
                        <td>₹<?php echo $crop['price_per_kg']; ?></td>
                        <td><?php echo $crop['harvest_date'] ? date('d M Y', strtotime($crop['harvest_date'])) : '-'; ?></td>
                        <td>
                            <?php $s = $statusColors[$crop['status']] ?? $statusColors['expired']; ?>
                            <span class="status-badge" style="background:<?php echo $s['bg']; ?>;color:<?php echo $s['text']; ?>;">
                                <?php echo htmlspecialchars($crop['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:0.5rem;">
                                <a href="?action=edit&id=<?php echo $crop['crop_id']; ?>" class="btn btn-outline" style="padding:0.3rem 0.6rem;font-size:0.8rem;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=delete&id=<?php echo $crop['crop_id']; ?>" class="btn btn-outline" style="padding:0.3rem 0.6rem;font-size:0.8rem;border-color:#ff8a8a;color:#ff8a8a;" onclick="return confirm('Delete this crop?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php elseif ($action === 'add' || $action === 'store' || $action === 'edit' || $action === 'update'): ?>
    
    <div class="page-header" style="justify-content:center;">
        <div style="width:100%;max-width:800px;display:flex;justify-content:space-between;align-items:center;">
            <h1><i class="fas fa-<?php echo ($action==='add'||$action==='store')?'plus-circle':'edit'; ?>"></i> <?php echo ($action==='add'||$action==='store')?'Add New Crop':'Edit Crop'; ?></h1>
            <a href="<?php echo BASE_URL; ?>farmer/crops.php" class="btn btn-outline" style="border-color:var(--border-color);color:var(--text-muted);">Cancel</a>
        </div>
    </div>

    <div class="form-card">
        <?php if (!empty($errors)): ?>
            <div class="mb-1" style="background:#ffebee;color:#c62828;padding:0.75rem 1rem;border-radius:8px;">
                <?php foreach ($errors as $e): ?>
                    <div><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php 
            $isEdit = ($action === 'edit' || $action === 'update');
            $target = $isEdit ? "?action=update" : "?action=store";
            $data   = $isEdit ? $editCrop : $old;
        ?>

        <form method="POST" action="<?php echo BASE_URL . 'farmer/crops.php' . $target; ?>" enctype="multipart/form-data" novalidate>
            <?php if ($isEdit): ?><input type="hidden" name="crop_id" value="<?php echo $data['crop_id']; ?>"><?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Crop Name <span style="color:red">*</span></label>
                    <input type="text" name="crop_name" value="<?php echo htmlspecialchars($data['crop_name']??''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['available','sold','reserved','expired'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo (($data['status']??'available') === $s) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($s); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantity (kg) <span style="color:red">*</span></label>
                    <input type="number" name="quantity" step="0.01" value="<?php echo htmlspecialchars($data['quantity']??''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Price per kg (₹) <span style="color:red">*</span></label>
                    <input type="number" name="price_per_kg" step="0.01" value="<?php echo htmlspecialchars($data['price_per_kg']??''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Harvest Date</label>
                    <input type="date" name="harvest_date" value="<?php echo htmlspecialchars($data['harvest_date']??''); ?>">
                </div>

                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" accept="image/*" onchange="previewImg(this)">
                    <img id="preview" src="<?php echo ($isEdit && $data['image']) ? BASE_URL.'uploads/crops/'.$data['image'] : ''; ?>" 
                         style="display:<?php echo ($isEdit && $data['image']) ? 'block' : 'none'; ?>;margin-top:0.5rem;height:80px;border-radius:8px;">
                </div>
            </div>

            <div class="form-group" style="margin-top:1.5rem;">
                <label>Description</label>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($data['description']??''); ?></textarea>
            </div>

            <div style="margin-top:2rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;padding:0.8rem;">
                    <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update Crop' : 'Save Crop'; ?>
                </button>
            </div>
        </form>
    </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
<script>
function previewImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
