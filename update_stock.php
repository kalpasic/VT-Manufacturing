<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $needle_type = $_POST['needle_type'];
    $new_quantity = (int) $_POST['stock_quantity'];

    // Fetch the current stock quantity for the selected needle type
    $sql = "SELECT stock_quantity FROM needle_stock WHERE needle_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $needle_type);
    $stmt->execute();
    $stmt->bind_result($current_quantity);
    $stmt->fetch();
    $stmt->close();

    // Calculate the updated quantity
    $updated_quantity = $current_quantity + $new_quantity;

    // Update stock quantity with the new total
    $sql = "UPDATE needle_stock SET stock_quantity = ? WHERE needle_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $updated_quantity, $needle_type);
    $stmt->execute();
    $stmt->close();

    $message = "Stock updated successfully!";
}

// Fetch existing needle stock data for the form
$sql = "SELECT * FROM needle_stock";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Needle Stock</title>
    <link rel="stylesheet" type="text/css" href="styleupdate.css">
    <link rel="icon" type="image/png" href="icon.png"/>
</head>
<body>
    <h2>Update Needle Stock</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

    <?php if (isset($message)): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="needle_type">Needle Type:</label>
        <select name="needle_type" id="needle_type" required>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['needle_type']}'>{$row['needle_type']}</option>";
            }
            ?>
        </select>
        <label for="stock_quantity">New Quantity to Add:</label>
        <input type="number" name="stock_quantity" id="stock_quantity" required min="1">
        <button type="submit">Update Stock</button>
    </form>

    <div class="shortcut-container">
        <a href="view_needle_stock.php" class="shortcut-button">Return to View Needle Stock</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
