<?php
// Database configuration
$servername = "localhost"; // Change if your server is different
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "washing_garment";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$style_filter = isset($_GET['style']) ? $_GET['style'] : '';
$color_filter = isset($_GET['color']) ? $_GET['color'] : '';
$cont_number_filter = isset($_GET['cont_number']) ? $_GET['cont_number'] : '';

// Prepare SQL queries with filters
$sql_receiving = "SELECT date, vehicle_number, invoice_number, cont_number, style, color, invoice_quantity, actual_quantity, remarks FROM washing_receiving WHERE 1=1";
$sql_sending = "SELECT date, vehicle_number, invoice_number, cont_number, style, color, actual, remarks FROM washing_sending WHERE 1=1";

if ($date_filter) {
    $sql_receiving .= " AND date = '" . $conn->real_escape_string($date_filter) . "'";
    $sql_sending .= " AND date = '" . $conn->real_escape_string($date_filter) . "'";
}

if ($style_filter) {
    $sql_receiving .= " AND style = '" . $conn->real_escape_string($style_filter) . "'";
    $sql_sending .= " AND style = '" . $conn->real_escape_string($style_filter) . "'";
}

if ($color_filter) {
    $sql_receiving .= " AND color = '" . $conn->real_escape_string($color_filter) . "'";
    $sql_sending .= " AND color = '" . $conn->real_escape_string($color_filter) . "'";
}

if ($cont_number_filter) {
    $sql_receiving .= " AND cont_number = '" . $conn->real_escape_string($cont_number_filter) . "'";
    $sql_sending .= " AND cont_number = '" . $conn->real_escape_string($cont_number_filter) . "'";
}

// Execute queries
$result_receiving = $conn->query($sql_receiving);
$result_sending = $conn->query($sql_sending);

// Initialize an array to hold merged results
$merged_results = []; 
$total_sent = 0; // Initialize total sent quantity
$total_received = 0; // Initialize total received quantity
$total_missing = 0; // Initialize total missing quantity

// Fetch receiving data
if ($result_receiving->num_rows > 0) {
    while($row = $result_receiving->fetch_assoc()) {
        $cont_number = htmlspecialchars($row["cont_number"]);
        $style = htmlspecialchars($row["style"]);
        $color = htmlspecialchars($row["color"]);

        // Initialize or update the merged row
        if (!isset($merged_results[$cont_number][$style][$color])) {
            $merged_results[$cont_number][$style][$color] = [
                'date' => htmlspecialchars($row["date"]),
                'actual_quantity' => (int)htmlspecialchars($row["actual_quantity"]),
                'invoice_quantity' => (int)htmlspecialchars($row["invoice_quantity"]),
                'remarks_receiving' => htmlspecialchars($row["remarks"]),
                'actual_sending' => 0,
                'remarks_sending' => '',
            ];
        } else {
            // Update existing row for additional receiving data
            $merged_results[$cont_number][$style][$color]['actual_quantity'] += (int)htmlspecialchars($row["actual_quantity"]);
            $merged_results[$cont_number][$style][$color]['invoice_quantity'] += (int)htmlspecialchars($row["invoice_quantity"]);
        }
        $total_received += (int)htmlspecialchars($row["actual_quantity"]); // Add to total received
    }
}

// Fetch sending data and merge with receiving data
if ($result_sending->num_rows > 0) {
    while($row = $result_sending->fetch_assoc()) {
        $cont_number = htmlspecialchars($row["cont_number"]);
        $style = htmlspecialchars($row["style"]);
        $color = htmlspecialchars($row["color"]);

        // Initialize or update the merged row
        if (!isset($merged_results[$cont_number][$style][$color])) {
            $merged_results[$cont_number][$style][$color] = [
                'date' => htmlspecialchars($row["date"]),
                'actual_quantity' => 0,
                'invoice_quantity' => 0,
                'remarks_receiving' => '',
                'actual_sending' => (int)htmlspecialchars($row["actual"]),
                'remarks_sending' => htmlspecialchars($row["remarks"]),
            ];
        } else {
            // Update existing row for additional sending data
            $merged_results[$cont_number][$style][$color]['actual_sending'] += (int)htmlspecialchars($row["actual"]);
            $merged_results[$cont_number][$style][$color]['remarks_sending'] = htmlspecialchars($row["remarks"]);
        }
        $total_sent += (int)htmlspecialchars($row["actual"]); // Add to total sent
    }
}

// Excel export functionality
if (isset($_POST['export_excel'])) {
    // Open output stream for the CSV file
    if ($output = fopen('php://output', 'w')) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="washing_summary.csv"');

        // Add column headers
        fputcsv($output, ['Date', 'Cont Number', 'Style', 'Color', 'Actual (Sending)', 'Remarks (Sending)', 'Invoice Quantity', 'Actual (Receiving)', 'Remarks (Receiving)', 'Quantity Difference']);

        // Add data rows
        foreach ($merged_results as $cont_number => $styles) {
            foreach ($styles as $style => $colors) {
                foreach ($colors as $color => $data) {
                    $quantity_difference = $data['actual_sending'] - $data['actual_quantity'];
                    fputcsv($output, [
                        $data['date'],
                        $cont_number,
                        $style,
                        $color,
                        $data['actual_sending'],
                        $data['remarks_sending'],
                        $data['invoice_quantity'],
                        $data['actual_quantity'],
                        $data['remarks_receiving'],
                        $quantity_difference
                    ]);
                }
            }
        }
        fclose($output); // Close the output stream
        exit();
    } else {
        // Display an error message if the file cannot be created
        echo "<script>alert('Error: Unable to create the Excel file. Please try again later.');</script>";
    }
}

// Display summary
?>
<!DOCTYPE html>
<html>
<head>
    <title>Washing Garment Summary</title>
    <link rel="stylesheet" type="text/css" href="washing_summary.css">
    <style>
        @media print {
            button {
                display: none; /* Hide the print button during printing */
            }
            #filter-options {
                display: none; /* Hide filter options during printing */
            }
        }
    </style>
    <script>
        function printSummary() {
            window.print(); // This will trigger the print dialog
        }
    </script>
</head>
<body>

<h2>Filter Options</h2>
<div id="filter-options">
    <form method="GET" action="">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
        
        <label for="cont_number">Cont Number:</label>
        <select id="cont_number" name="cont_number">
            <option value="">All Cont Numbers</option>
            <?php echo $cont_number_options; ?>
        </select>
        
        <label for="style">Style:</label>
        <select id="style" name="style">
            <option value="">All Styles</option>
            <?php echo $style_options; ?>
        </select>
        
        <label for="color">Color:</label>
        <select id="color" name="color">
            <option value="">All Colors</option>
            <?php echo $color_options; ?>
        </select>
        
        <input type="submit" value="Filter" style="margin-left: 10px;">
    </form>
</div>

<h2>Washing Summary</h2>
<form method="POST" action="">
    <button type="submit" name="export_excel" class="export-button">Export to Excel</button>
    <button onclick="printSummary()" class="print-button">Print Summary</button>
</form>
<table>
    <tr>
        <th>Date</th>
        <th>Cont Number</th>
        <th>Style</th>
        <th>Color</th>
        <th>Actual (Sending)</th>
        <th>Remarks (Sending)</th>
        <th>Invoice Quantity</th>
        <th>Actual (Receiving)</th>
        <th>Remarks (Receiving)</th>
        <th>Quantity Difference</th>
    </tr>
    <?php
    // Display the merged results
    foreach ($merged_results as $cont_number => $styles) {
        foreach ($styles as $style => $colors) {
            foreach ($colors as $color => $data) {
                $quantity_difference = $data['actual_sending'] - $data['actual_quantity'];
                echo "<tr>
                    <td>{$data['date']}</td>
                    <td>{$cont_number}</td>
                    <td>{$style}</td>
                    <td>{$color}</td>
                    <td>{$data['actual_sending']}</td>
                    <td>{$data['remarks_sending']}</td>
                    <td>{$data['invoice_quantity']}</td>
                    <td>{$data['actual_quantity']}</td>
                    <td>{$data['remarks_receiving']}</td>
                    <td>{$quantity_difference}</td>
                </tr>";
            }
        }
    }
    ?>
</table>

</body>
</html>
<?php
$conn->close(); // Close the database connection
?>
