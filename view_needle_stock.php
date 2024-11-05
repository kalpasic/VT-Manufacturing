<?php
session_start(); // Ensure this is at the very top of your file

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

// Fetch needle stock data
$sql = "SELECT * FROM needle_stock"; // Replace with your actual needle stock table name
$result = $conn->query($sql);

// Check for errors in the SQL query
if (!$result) {
    die("Error fetching needle stock: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Needle Stock</title>
    <link rel="stylesheet" type="text/css" href="stylestock.css">
    <link rel="icon" type="image/png" href="icon.png"/>
    <script>
        function checkLowStock() {
            const stockQuantities = document.querySelectorAll('.stock-quantity');

            stockQuantities.forEach((element) => {
                const quantity = parseInt(element.innerText, 10);
                if (quantity < 200) {
                    alert('Warning: Needle stock for type "' + element.dataset.needleType + '" is low (' + quantity + '). Please restock.');
                }
            });
        }

        window.onload = checkLowStock;
    </script>
</head>
<body>
    <h2>Needle Stock Management</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

    <div class="tabs">
        <a href="view_needle_stock.php">View Needle Stock</a>
        <a href="update_stock.php">Update Needle Stock</a>
        <a href="issue_needle.php">Issue Needle</a>
        <a href="return_needle.php">Return Needle</a>
        <a href="view_summary.php">Summary</a>
    </div>

    <div class="content-container">
        <table>
            <thead>
                <tr>
                    <th>Needle Type</th>
                    <th>Stock Quantity</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Determine the class based on stock quantity
                        $stockClass = $row['stock_quantity'] < 200 ? 'low-stock' : '';
                        echo "<tr>
                                <td>{$row['needle_type']}</td>
                                <td class='stock-quantity {$stockClass}' data-needle-type='{$row['needle_type']}'>{$row['stock_quantity']}</td>
                                <td>{$row['last_updated']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No needle stock available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="logout-container">
        <a href="login.php">Logout</a>
    </div>
</body>
</html>

<?php
$conn->close(); // This should only be called if $conn is defined
?>
