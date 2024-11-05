<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['department'] !== 'Production' && $_SESSION['department'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "washing_garment";

// Create connections
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$vtm_conn = new mysqli($servername, $username, $password, "vtm_loading");
if ($vtm_conn->connect_error) {
    die("Connection failed: " . $vtm_conn->connect_error);
}

// Fetch styles, colors, and container numbers
$styleOptions = [];
$sql_vtm = "SELECT DISTINCT style, color, cont_number FROM order_details";
$result_vtm = $vtm_conn->query($sql_vtm);

if ($result_vtm->num_rows > 0) {
    while ($row = $result_vtm->fetch_assoc()) {
        $styleOptions[] = $row;
    }
}

// Get unique container numbers
$uniqueContainers = array_unique(array_column($styleOptions, 'cont_number'));

// Insert data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $vehicle_number = $_POST['vehicle_number'];
    $invoice_number = $_POST['invoice_number'];
    $cont_number = $_POST['cont_number'];
    $style = $_POST['style'];
    $color = $_POST['color'];
    $invoice_quantity = $_POST['invoice_quantity'];
    $actual_quantity = $_POST['actual_quantity'];
    $remarks = $_POST['remarks'];

    $sql = "INSERT INTO washing_receiving (date, vehicle_number, invoice_number, cont_number, style, color, invoice_quantity, actual_quantity, remarks)
            VALUES ('$date', '$vehicle_number', '$invoice_number', '$cont_number', '$style', '$color', '$invoice_quantity', '$actual_quantity', '$remarks')";

    if ($conn->query($sql) === TRUE) {
        echo "Record added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$sql = "SELECT * FROM washing_receiving";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Washing Receiving Records</title>
    <link rel="stylesheet" type="text/css" href="washing_receiving.css">
    <script>
        const styleOptions = <?php echo json_encode($styleOptions); ?>;

        function filterOptions() {
            const contSelect = document.getElementById("cont_number");
            const styleSelect = document.getElementById("style");
            const colorSelect = document.getElementById("color");

            const selectedCont = contSelect.value;

            // Clear and reset Style and Color dropdowns
            styleSelect.innerHTML = "<option value=''>Select Style</option>";
            colorSelect.innerHTML = "<option value=''>Select Color</option>";

            // Filter based on Container Number
            const filteredOptions = styleOptions.filter(option => option.cont_number === selectedCont);

            // Populate Style dropdown
            filteredOptions.forEach(option => {
                const styleOption = document.createElement("option");
                styleOption.value = option.style;
                styleOption.textContent = option.style;
                styleSelect.appendChild(styleOption);
            });

            styleSelect.addEventListener("change", function () {
                colorSelect.innerHTML = "<option value=''>Select Color</option>";
                const selectedStyle = styleSelect.value;

                filteredOptions
                    .filter(option => option.style === selectedStyle)
                    .forEach(option => {
                        const colorOption = document.createElement("option");
                        colorOption.value = option.color;
                        colorOption.textContent = option.color;
                        colorSelect.appendChild(colorOption);
                    });
            });
        }
    </script>
</head>
<body>

    <h2>Add New Washing Receiving Record</h2>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th>Date</th>
                <th>Vehicle Number</th>
                <th>Invoice Number</th>
                <th>Container Number</th>
                <th>Style</th>
                <th>Color</th>
                <th>Invoice Quantity</th>
                <th>Actual Quantity</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <td><input type="date" name="date" required></td>
                <td><input type="text" name="vehicle_number" required></td>
                <td><input type="text" name="invoice_number" required></td>

                <td>
                    <select name="cont_number" id="cont_number" onchange="filterOptions()" required>
                        <option value="">Select Container Number</option>
                        <?php foreach ($uniqueContainers as $cont_number) { ?>
                            <option value="<?php echo $cont_number; ?>"><?php echo $cont_number; ?></option>
                        <?php } ?>
                    </select>
                </td>

                <td>
                    <select name="style" id="style" required>
                        <option value="">Select Style</option>
                    </select>
                </td>

                <td>
                    <select name="color" id="color" required>
                        <option value="">Select Color</option>
                    </select>
                </td>

                <td><input type="number" name="invoice_quantity" required></td>
                <td><input type="number" name="actual_quantity" required></td>
                <td><textarea name="remarks"></textarea></td>
            </tr>
            <tr>
                <td colspan="9"><input type="submit" value="Submit" style="width: 100%;"></td>
            </tr>
        </table>
    </form>

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
                            <td>" . $row["invoice_quantity"] . "</td>
                            <td>" . $row["actual_quantity"] . "</td>
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
$conn->close();
$vtm_conn->close();
?>
