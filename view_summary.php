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

// Initialize filter variables
$employer_number = '';
$start_date = '';
$end_date = '';
$machine_number = '';
$needle_type = '';
$machine_type = ''; // New variable for machine type

// Check if filter form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employer_number = $_POST['employer_number'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $machine_number = $_POST['machine_number'];
    $needle_type = $_POST['needle_type'];
    $machine_type = $_POST['machine_type']; // Get the selected machine type
}

// Retrieve machine types for the filter
$machine_types = [];
$machine_type_query = "SELECT DISTINCT machine_type FROM machine_list";
$machine_type_result = $conn->query($machine_type_query);

if ($machine_type_result->num_rows > 0) {
    while ($row = $machine_type_result->fetch_assoc()) {
        $machine_types[] = $row['machine_type'];
    }
}

// Prepare SQL query with filters, joining machine_list for machine_type
$query = "
    SELECT 
        i.employer_number,
        i.needle_type,
        i.issue_date,
        i.issue_quantity,
        i.machine_number,
        ml.machine_type,  -- Change this line to select from machine_list
        i.insurance_name,
        i.issued_by,
        i.issued_at,
        r.return_date,
        r.return_quantity,
        r.machine_number AS return_machine_number,
        r.return_condition,
        i.ongoing_style  -- Add ongoing_style here
    FROM 
        issued_needles i
    LEFT JOIN 
        return_needle r ON i.employer_number = r.employer_number
    LEFT JOIN 
        machine_list ml ON i.machine_number = ml.machine_number  -- Join to access machine_type
    WHERE 
        (i.employer_number LIKE ? OR ? = '') AND
        (i.issue_date BETWEEN ? AND ? OR (? = '' AND ? = '')) AND
        (i.machine_number LIKE ? OR ? = '') AND
        (i.needle_type LIKE ? OR ? = '') AND
        (ml.machine_type LIKE ? OR ? = '')  -- Use ml.machine_type
";

// Prepare the statement
$stmt = $conn->prepare($query);
$like_employer_number = '%' . $employer_number . '%';
$like_machine_number = '%' . $machine_number . '%';
$like_needle_type = '%' . $needle_type . '%';
$like_machine_type = '%' . $machine_type . '%';

$stmt->bind_param("ssssssssssss", $like_employer_number, $employer_number, $start_date, $end_date, $start_date, $end_date, $like_machine_number, $machine_number, $like_needle_type, $needle_type, $like_machine_type, $machine_type);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Issue and Return Summary</title>
    <link rel="icon" type="image/png" href="icon.png"/>
    <link rel="stylesheet" type="text/css" href="view_summary.css">
    
    <script>
        function printSummary() {
            // Hide other elements on the page temporarily
            const summaryContainer = document.querySelector('.summary-container');
            const otherContent = document.body.children;

            // Save original display values
            let originalDisplay = [];
            for (let i = 0; i < otherContent.length; i++) {
                if (otherContent[i] !== summaryContainer) {
                    originalDisplay[i] = otherContent[i].style.display;
                    otherContent[i].style.display = 'none';
                }
            }

            // Print the summary container
            window.print();

            // Restore original display values
            for (let i = 0; i < otherContent.length; i++) {
                if (otherContent[i] !== summaryContainer) {
                    otherContent[i].style.display = originalDisplay[i];
                }
            }
        }
    </script>
    
</head>
<body>
    <div class="summary-container">
        <h3>Summary</h3>

        <form method="POST" class="filter-form">
            <input type="text" name="employer_number" placeholder="Employer Number" value="<?php echo htmlspecialchars($employer_number); ?>">
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            <select name="machine_type" >
                <option value="">Select Machine Type</option>
                <?php foreach ($machine_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($machine_type == $type) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="needle_type" placeholder="Needle Type" value="<?php echo htmlspecialchars($needle_type); ?>">
            <button type="submit">Filter</button>
        </form>

        <button onclick="printSummary()" class="print-button">Print Summary</button>

        <table>
            <thead>
                <tr>
                    <th>Employer Number</th>
                    <th>Needle Type</th>
                    <th>Issue Date</th>
                    <th>Issued Quantity</th>
                    <th>Machine Number</th>
                    <th>Machine Type</th> <!-- Add machine type to the table -->
                    <th>Ongoing Style</th> <!-- Add ongoing style to the table -->
                    <th>Issued By</th>
                    <th>Issued At</th>
                    <th>Return Date</th>
                    <th>Returned Quantity</th>
                    <th>Return Machine Number</th>
                    <th>Return Condition</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['employer_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['needle_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['issue_quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['machine_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['machine_type']); ?></td> <!-- Display machine type -->
                        <td><?php echo htmlspecialchars($row['ongoing_style']); ?></td> <!-- Display ongoing style -->
                        <td><?php echo htmlspecialchars($row['issued_by']); ?></td>
                        <td><?php echo htmlspecialchars($row['issued_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_machine_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_condition']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <button onclick="window.location.href='export_excel.php'" class="export-button">Export to Excel</button>

<footer>   
    <div class="shortcut-container">
        <a href="view_needle_stock.php" class="shortcut-button">Return to View Needle Stock</a>
    </div>
</footer>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
