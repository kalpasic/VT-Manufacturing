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

// Retrieve all records from the washing_sending table
$washing_sending_sql = "SELECT * FROM washing_sending";
$washing_sending_result = $conn->query($washing_sending_sql);

// Retrieve all records from the washing_receiving table
$washing_receiving_sql = "SELECT * FROM washing_receiving";
$washing_receiving_result = $conn->query($washing_receiving_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Washing Summary</title>
    <link rel="stylesheet" type="text/css" href="summary.css">
</head>
<body>

    <h1>Washing Summary Records</h1>

    <h2>Washing Sending Records</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Vehicle Number</th>
                <th>Invoice Number</th>
                <th>Container Number</th>
                <th>Style</th>
                <th>Color</th>
                <th>Actual Quantity</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($washing_sending_result->num_rows > 0) {
                while ($row = $washing_sending_result->fetch_assoc()) {
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

    <h2>Washing Receiving Records</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Vehicle Number</th>
                <th>Invoice Number</th>
                <th>Container Number</th>
                <th>Style</th>
                <th>Color</th>
                <th>Invoice Quantity</th>
                <th>Received Quantity</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($washing_receiving_result->num_rows > 0) {
                while ($row = $washing_receiving_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["id"] . "</td>
                            <td>" . $row["date"] . "</td>
                            <td>" . $row["vehicle_number"] . "</td>
                            <td>" . $row["invoice_number"] . "</td>
                            <td>" . $row["cont_number"] . "</td>
                            <td>" . $row["style"] . "</td>
                            <td>" . $row["color"] . "</td>
                            <td>" . $row["invoice_quantity"] . "</td>
                            <td>" . $row["received_quantity"] . "</td>
                            <td>" . $row["remarks"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>

<?php
// Close connections
$conn->close();
$vtm_loading_conn->close();
?>
