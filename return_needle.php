<?php
// Database connection
$servername = "localhost"; // Change if needed
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "login_system"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employer_number = $_POST['employer_number'];
    $return_date = $_POST['return_date'];
    $return_quantity = $_POST['return_quantity'];
    $machine_number = $_POST['machine_number'];
    $return_condition = $_POST['return_condition'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO return_needle (employer_number, return_date, return_quantity, machine_number, return_condition) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $employer_number, $return_date, $return_quantity, $machine_number, $return_condition);

    if ($stmt->execute()) {
        $success_message = "Needle returned successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Needle</title>
    <link rel="stylesheet" href="stylereturn.css"> <!-- Link to your CSS file -->
    <link rel="icon" type="image/png" href="icon.png"/>
</head>
<body>
    <div class="update-stock-container">
        <h3>Return Needle</h3>
        <?php if (isset($success_message)) : ?>
            <div class="success"><?= $success_message; ?></div>
        <?php elseif (isset($error_message)) : ?>
            <div class="error"><?= $error_message; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <label for="employer_number">Employer Number</label>
            <input type="text" id="employer_number" name="employer_number" required>

            <label for="return_date">Return Date</label>
            <input type="date" id="return_date" name="return_date" required>

            <label for="return_quantity">Return Quantity</label>
            <input type="number" id="return_quantity" name="return_quantity" required>

            <label for="machine_number">Machine Number</label>
            <input type="text" id="machine_number" name="machine_number" required>

            <label for="return_condition">Return Needle Condition</label>
            <select id="return_condition" name="return_condition" required>
                <option value="Good">Good</option>
                <option value="Damaged">Damaged</option>
                <option value="Defective">Defective</option>
            </select>

            <button type="submit">Return Needle</button>
        </form>
    </div>
    <div class="shortcut-container">
    <a href="view_needle_stock.php" class="shortcut-button">Return to View Needle Stock</a>
</div>

<!-- Rest of your content -->

</body>
</html>
