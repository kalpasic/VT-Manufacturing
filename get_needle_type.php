<?php
// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "login_system"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if machine_number is set in the request
if (isset($_POST['machine_number'])) {
    $machine_number = trim($_POST['machine_number']);

    // Query to fetch the needle_type based on machine_number
    $sql = "SELECT machine_type FROM machine_list WHERE machine_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $machine_number);
    $stmt->execute();
    $stmt->bind_result($machine_type);
    $stmt->fetch();
    $stmt->close();

    // Output the needle_type or an empty string if not found
    echo $machine_type ?: '';
}

$conn->close();
?>
