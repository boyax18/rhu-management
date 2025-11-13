<?php
session_start();

// Database connection
$conn = new mysqli("localhost:3306", "gtolomea_saiua", "09363553973Gabrieltolomea", "gtolomea_system_rhu");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check credentials in database
    $sql = "SELECT * FROM admin_accounts WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['username'] = $username;
        header("Location: homepage.php");
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>Invalid username or password.</p>";
        echo "<p style='text-align:center;'><a href='login.html'>Try again</a></p>";
    }
}

$conn->close();
?>
