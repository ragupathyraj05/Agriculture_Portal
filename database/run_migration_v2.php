<?php
/**
 * Run migration_v2.sql — execute via CLI: c:\xampp\php\php.exe run_migration_v2.php
 */
$conn = mysqli_connect('127.0.0.1', 'root', '', 'agriculture_portal');
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error() . "\n");
}
echo "Connected to database.\n";

$sql = file_get_contents(__DIR__ . '/migration_v2.sql');
if (!$sql) {
    die("Could not read migration_v2.sql\n");
}

if (mysqli_multi_query($conn, $sql)) {
    $i = 0;
    do {
        $i++;
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
        if (mysqli_errno($conn)) {
            echo "Statement $i Error: " . mysqli_error($conn) . "\n";
        }
    } while (mysqli_next_result($conn));
}

if (mysqli_errno($conn)) {
    echo "Final Error: " . mysqli_error($conn) . "\n";
} else {
    echo "Migration V2 completed successfully!\n";
}

mysqli_close($conn);
