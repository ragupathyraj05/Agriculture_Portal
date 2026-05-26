<?php
/**
 * run_upi_migration.php
 * Run once to apply the UPI payment migration.
 * Access via: http://localhost/Agriculture-Portal/database/run_upi_migration.php
 */
require_once '../includes/config.php';
require_once '../includes/db.php';

$sql = file_get_contents(__DIR__ . '/upi_migration.sql');
$queries = array_filter(array_map('trim', explode(';', $sql)));

$errors = [];
$success = 0;
foreach ($queries as $query) {
    if (empty($query) || str_starts_with($query, '--')) continue;
    if (mysqli_query($conn, $query)) {
        $success++;
    } else {
        $errors[] = mysqli_error($conn) . " | Query: " . substr($query, 0, 80);
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>UPI Migration</title><style>body{font-family:sans-serif;padding:2rem;}
.ok{color:green;} .err{color:red;}</style></head>
<body>
<h2>UPI Migration Results</h2>
<p class="ok">✅ <?= $success ?> statement(s) executed successfully.</p>
<?php foreach ($errors as $e): ?>
<p class="err">❌ <?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>
<?php if (empty($errors)): ?>
<p><strong>Migration complete! You can delete this file.</strong></p>
<?php endif; ?>
</body>
</html>
