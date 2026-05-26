<?php
require_once 'config.php';

// Disable strict reporting to prevent Fatal Errors on connection (handle manually)
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
    // Set charset
    mysqli_set_charset($conn, "utf8mb4");
} catch (Exception $e) {
    // If connection fails, show simple error and stop
    die("Database Connection Error. Please try again later.");
    // For debugging (uncomment if needed):
    // die("Connection failed: " . $e->getMessage());
}
?>
