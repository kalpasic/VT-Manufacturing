<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "login_system"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sendMessage = "";

// Handle sending data submission
if (isset($_POST['send'])) {
    $date = $_POST['date'];
    $vehicle_number = $_POST['vehicle_number'];
    $invoice_number = $_POST['invoice_number'];
    $cont_number = $_POST['cont_number'];
    $style = $_POST['style'];
    $color = $_POST['color'];
    $sending_quantity = $_POST['sending_quantity'];

    // Insert data into the washing_sending table
    $sql = "INSERT INTO washing_sending (date, vehicle_number, invoice_number, cont_number, style, color, sending_quantity) 
            VALUES ('$date', '$vehicle_number', '$invoice_number', '$cont_number', '$style', '$color', '$sending_quantity')";
    
    if ($conn->query($sql) === TRUE) {
        $sendMessage = "Data sent successfully!";
    } else {
        $sendMessage = "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Washing Sending</title>
    <link rel="stylesheet" type="text/css" href="stylelogin.css">
</head>
<body>
    <div class="company-name">VT MANUFACTURING [PVT] LIMITED - Washing Sending</div>
    <h1>Send Washing Data</h1>
    
    <?php if ($sendMessage): ?>
        <div class="success"><?php echo htmlspecialchars($sendMessage); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="date" name="date" required>
        <input type="text" name="vehicle_number" placeholder="Vehicle Number" required>
        <input type="text" name="invoice_number" placeholder="Invoice Number" required>
        <input type="text" name="cont_number" placeholder="Cont#" required>
        <input type="text" name="style" placeholder="Style" required>
        <input type="text" name="color" placeholder="Color" required>
        <input type="number" name="sending_quantity" placeholder="Sending Quantity" required>
        <button type="submit" name="send">Send Data</button>
    </form>
</body>
</html>
