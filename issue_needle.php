<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Database connection for login_system
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "login_system"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection for login_system
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Database connection for vtm_loading
$vtm_conn = new mysqli($servername, $username, $password, "vtm_loading");

// Check connection for vtm_loading
if ($vtm_conn->connect_error) {
    die("Connection failed: " . $vtm_conn->connect_error);
}

// Initialize message and error variables
$message = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure all fields are set and not empty
    $employer_number = isset($_POST['employer_number']) ? trim($_POST['employer_number']) : null;
    $needle_type = isset($_POST['needle_type']) ? trim($_POST['needle_type']) : null;
    $issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : date("Y-m-d");
    $issue_quantity = isset($_POST['issue_quantity']) ? intval(trim($_POST['issue_quantity'])) : null;
    $machine_number = isset($_POST['machine_number']) ? trim($_POST['machine_number']) : null;
    $insurance_name = isset($_POST['insurance_name']) ? trim($_POST['insurance_name']) : null;
    $style = isset($_POST['style']) ? trim($_POST['style']) : null; // Ongoing style from the form

    // Validate input
    if ($employer_number && $needle_type && $issue_quantity && $machine_number && $insurance_name && $style) {
        // Check if the previous needle has been returned for the specified machine
        $return_check_sql = "SELECT return_quantity FROM return_needle WHERE machine_number = ? AND return_condition = 'returned' ORDER BY return_date DESC LIMIT 1";
        $return_check_stmt = $conn->prepare($return_check_sql);
        $return_check_stmt->bind_param("s", $machine_number);
        $return_check_stmt->execute();
        $return_check_stmt->bind_result($return_quantity);
        $return_check_stmt->fetch();
        $return_check_stmt->close();

        // If return_quantity is NULL, it means no needles have been returned yet
        if ($return_quantity === null) {
            $error = "You must return the previous needle before issuing a new one.";
        } else {
            // Check if sufficient stock is available
            $sql = "SELECT stock_quantity FROM needle_stock WHERE needle_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $needle_type);
            $stmt->execute();
            $stmt->bind_result($current_stock);
            $stmt->fetch();
            $stmt->close();

            if ($current_stock >= $issue_quantity) {
                // Update stock quantity
                $new_stock = $current_stock - $issue_quantity;
                $sql = "UPDATE needle_stock SET stock_quantity = ? WHERE needle_type = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $new_stock, $needle_type);
                $stmt->execute();
                $stmt->close();

                // Insert issue record with ongoing_style
                $sql = "INSERT INTO issued_needles (employer_number, needle_type, issue_date, issue_quantity, machine_number, insurance_name, issued_by, ongoing_style) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ississss", $employer_number, $needle_type, $issue_date, $issue_quantity, $machine_number, $insurance_name, $_SESSION['username'], $style); // Bind ongoing_style
                $stmt->execute();
                $stmt->close();

                $message = "Needles issued successfully!";
            } else {
                $error = "Insufficient stock available.";
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Fetch needle types for the dropdown
$sql = "SELECT needle_type FROM needle_stock";
$result = $conn->query($sql);

// Fetch ongoing styles from vtm_loading database
$style_sql = "SELECT DISTINCT style FROM order_details";
$style_result = $vtm_conn->query($style_sql);

// Fetch machine numbers for the datalist
$machine_sql = "SELECT machine_number FROM machine_list";
$machine_result = $conn->query($machine_sql);
$machine_numbers = [];
while ($row = $machine_result->fetch_assoc()) {
    $machine_numbers[] = htmlspecialchars($row['machine_number']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Needle</title>
    <link rel="stylesheet" type="text/css" href="issue_needle.css">
</head>
<body>
    <h2>Issue Needle</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="employer_number">Employer Number:</label>
        <input type="text" name="employer_number" id="employer_number" required>

        <label for="needle_type">Needle Type:</label>
        <select name="needle_type" id="needle_type" required>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['needle_type']) . "'>" . htmlspecialchars($row['needle_type']) . "</option>";
            }
            ?>
        </select>

        <label for="style">Ongoing Style:</label>
        <select name="style" id="style" required>
            <?php
            while ($style_row = $style_result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($style_row['style']) . "'>" . htmlspecialchars($style_row['style']) . "</option>";
            }
            ?>
        </select>

        <label for="issue_date">Issue Date:</label>
        <input type="date" name="issue_date" id="issue_date" required>

        <label for="issue_quantity">Quantity to Issue:</label>
        <input type="number" name="issue_quantity" id="issue_quantity" required min="1">

        <label for="machine_number">Machine Number:</label>
        <input list="machine-numbers" name="machine_number" id="machine_number" required autocomplete="off">
        <datalist id="machine-numbers">
            <?php foreach ($machine_numbers as $machine_number): ?>
                <option value="<?php echo $machine_number; ?>"><?php echo $machine_number; ?></option>
            <?php endforeach; ?>
        </datalist>

        <label for="insurance_name">Issuer:</label>
        <input type="text" name="insurance_name" id="insurance_name" required>

        <button type="submit">Issue Needle</button>
    </form>

    <div class="shortcut-container">
        <a href="return_needle.php" class="shortcut-button">Return Needle</a>
        <a href="view_needle_stock.php" class="shortcut-button">Return to View Needle Stock</a>
    </div>
</body>
</html>

<?php
$conn->close();
$vtm_conn->close();
?>
