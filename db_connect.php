<?php
$servername = "localhost:3306";
$username = "gtolomea_saiua";
$password = "09363553973Gabrieltolomea";
$dbname = "gtolomea_system_rhu";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
