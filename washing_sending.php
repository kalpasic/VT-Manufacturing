<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "washing_garment";
$vtm_loading_db = "vtm_loading";

// Create connection for washing_garment database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create connection for vtm_loading database
$vtm_loading_conn = new mysqli($servername, $username, $password, $vtm_loading_db);

// Check connection
if ($vtm_loading_conn->connect_error) {
    die("Connection failed: " . $vtm_loading_conn->connect_error);
}

// Insert data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $vehicle_number = $_POST['vehicle_number'];
    $invoice_number = $_POST['invoice_number'];
    $cont_number = $_POST['cont_number'];
    $style = $_POST['style'];
    $color = $_POST['color'];
    $actual = $_POST['actual'];
    $remarks = $_POST['remarks'];

    // Insert query
    $sql = "INSERT INTO washing_sending (date, vehicle_number, invoice_number, cont_number, style, color, actual, remarks)
            VALUES ('$date', '$vehicle_number', '$invoice_number', '$cont_number', '$style', '$color', '$actual', '$remarks')";

    if ($conn->query($sql) === TRUE) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Retrieve all records from the washing_sending table
$sql = "SELECT * FROM washing_sending";
$result = $conn->query($sql);

// Retrieve style, color, and cont_number from order_details table for JavaScript use
$order_details_sql = "SELECT style, color, cont_number FROM order_details";
$order_details_result = $vtm_loading_conn->query($order_details_sql);
$order_details = [];
if ($order_details_result->num_rows > 0) {
    while ($row = $order_details_result->fetch_assoc()) {
        $order_details[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Washing Sending Records</title>
    <link rel="stylesheet" type="text/css" href="washing_sending.css">
</head>
<body>

    <!-- Form to insert new record -->
    <h2>Add New Washing Sending Record</h2>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th>Date</th>
                <th>Vehicle Number</th>
                <th>Invoice Number</th>
                <th>Cont Number</th>
                <th>Style</th>
                <th>Color</th>
                <th>Actual Quantity</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <td><input type="date" name="date" required></td>
                <td><input type="text" name="vehicle_number" required></td>
                <td><input type="text" name="invoice_number" required></td>
                
                <!-- Cont Number Dropdown -->
                <td>
                    <select name="cont_number" id="cont_number" onchange="updateStyleAndColor()" required>
                        <option value="">Select Cont Number</option>
                        <!-- JavaScript will populate options here -->
                    </select>
                </td>
                
                <!-- Style Dropdown -->
                <td>
                    <select name="style" id="style" required>
                        <option value="">Select Style</option>
                        <!-- JavaScript will populate options here -->
                    </select>
                </td>
                
                <!-- Color Dropdown -->
                <td>
                    <select name="color" id="color" required>
                        <option value="">Select Color</option>
                        <!-- JavaScript will populate options here -->
                    </select>
                </td>
                
                <td><input type="number" name="actual" required></td>
                <td><textarea name="remarks"></textarea></td>
            </tr>
            <tr>
                <td colspan="8"><input type="submit" value="Submit" style="width: 100%;"></td>
            </tr>
        </table>
    </form>


    <!-- Table to display records -->
    <h2>Washing Sending Records</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Vehicle Number</th>
                <th>Invoice Number</th>
                <th>Cont Number</th>
                <th>Style</th>
                <th>Color</th>
                <th>Actual Quantity</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["id"] . "</td>
                            <td>" . $row["date"] . "</td>
                            <td>" . $row["vehicle_number"] . "</td>
                            <td>" . $row["invoice_number"] . "</td>
                            <td>" . $row["cont_number"] . "</td>
                            <td>" . $row["style"] . "</td>
                            <td>" . $row["color"] . "</td>
                            <td>" . $row["actual"] . "</td>
                            <td>" . $row["remarks"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script type="text/javascript">
        // Pass PHP array to JavaScript
        const orderDetails = <?php echo json_encode($order_details); ?>;

        function updateStyleAndColor() {
            const contNumberSelect = document.getElementById('cont_number');
            const styleSelect = document.getElementById('style');
            const colorSelect = document.getElementById('color');

            // Clear previous options
            styleSelect.innerHTML = '<option value="">Select Style</option>';
            colorSelect.innerHTML = '<option value="">Select Color</option>';

            const selectedContNumber = contNumberSelect.value;

            // Find matching order details based on selected cont_number
            orderDetails.forEach(detail => {
                if (detail.cont_number === selectedContNumber) {
                    if (!styleSelect.querySelector(`option[value="${detail.style}"]`)) {
                        const option = document.createElement('option');
                        option.value = detail.style;
                        option.textContent = detail.style;
                        styleSelect.appendChild(option);
                    }
                    if (!colorSelect.querySelector(`option[value="${detail.color}"]`)) {
                        const option = document.createElement('option');
                        option.value = detail.color;
                        option.textContent = detail.color;
                        colorSelect.appendChild(option);
                    }
                }
            });
        }

        // Populate Cont Number dropdown on page load
        window.onload = function() {
            const contNumberSelect = document.getElementById('cont_number');
            orderDetails.forEach(detail => {
                if (!contNumberSelect.querySelector(`option[value="${detail.cont_number}"]`)) {
                    const option = document.createElement('option');
                    option.value = detail.cont_number;
                    option.textContent = detail.cont_number;
                    contNumberSelect.appendChild(option);
                }
            });
        };
    </script>
<!-- Excel Export Button -->
<h2>Export Washing Sending Records</h2>
    <a href="export_excel.php" style="text-decoration: none;">
        <button style="padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;">
            <img src="excel.png" alt="Export to Excel" style="width: 20px; vertical-align: middle; margin-right: 5px;">
            Export to Excel
        </button>
    </a>
</body>
</html>
