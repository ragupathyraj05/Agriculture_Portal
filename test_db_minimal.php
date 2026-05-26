<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_connect('127.0.0.1', 'root', '', 'agriculture_portal');
    echo "Connected successfully via 127.0.0.1";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
