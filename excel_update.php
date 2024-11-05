<?php
require 'vendor/washing_summary.php'; // Load PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
$total_sent = 0;
$total_received = 0;

// Fetch receiving data
if ($result_receiving->num_rows > 0) {
    while ($row = $result_receiving->fetch_assoc()) {
        $cont_number = htmlspecialchars($row["cont_number"]);
        $style = htmlspecialchars($row["style"]);
        $color = htmlspecialchars($row["color"]);
        if (!isset($merged_results[$cont_number][$style][$color])) {
            $merged_results[$cont_number][$style][$color] = [
                'date' => htmlspecialchars($row["date"]),
                'actual_quantity' => (int)htmlspecialchars($row["actual_quantity"]),
                'invoice_quantity' => (int)htmlspecialchars($row["invoice_quantity"]),
                'remarks_receiving' => htmlspecialchars($row["remarks"]),
                'actual_sending' => 0,
                'remarks_sending' => '',
            ];
        }
        $total_received += (int)htmlspecialchars($row["actual_quantity"]);
    }
}

// Fetch sending data and merge with receiving data
if ($result_sending->num_rows > 0) {
    while ($row = $result_sending->fetch_assoc()) {
        $cont_number = htmlspecialchars($row["cont_number"]);
        $style = htmlspecialchars($row["style"]);
        $color = htmlspecialchars($row["color"]);
        if (!isset($merged_results[$cont_number][$style][$color])) {
            $merged_results[$cont_number][$style][$color] = [
                'date' => htmlspecialchars($row["date"]),
                'actual_quantity' => 0,
                'invoice_quantity' => 0,
                'remarks_receiving' => '',
                'actual_sending' => (int)htmlspecialchars($row["actual"]),
                'remarks_sending' => htmlspecialchars($row["remarks"]),
            ];
        }
        $total_sent += (int)htmlspecialchars($row["actual"]);
    }
}

// Excel export functionality
if (isset($_POST['export_excel'])) {
    $existingFilePath = 'path/to/your/existingfile.xlsx';
    $spreadsheet = IOFactory::load($existingFilePath);

    $sheet = $spreadsheet->getActiveSheet();

    $rowIndex = 2; // Assuming the data starts on row 2
    foreach ($merged_results as $cont_number => $styles) {
        foreach ($styles as $style => $colors) {
            foreach ($colors as $color => $data) {
                $quantity_difference = $data['actual_sending'] - $data['actual_quantity'];
                $sheet->setCellValue("A{$rowIndex}", $data['date']);
                $sheet->setCellValue("B{$rowIndex}", $cont_number);
                $sheet->setCellValue("C{$rowIndex}", $style);
                $sheet->setCellValue("D{$rowIndex}", $color);
                $sheet->setCellValue("E{$rowIndex}", $data['actual_sending']);
                $sheet->setCellValue("F{$rowIndex}", $data['remarks_sending']);
                $sheet->setCellValue("G{$rowIndex}", $data['invoice_quantity']);
                $sheet->setCellValue("H{$rowIndex}", $data['actual_quantity']);
                $sheet->setCellValue("I{$rowIndex}", $data['remarks_receiving']);
                $sheet->setCellValue("J{$rowIndex}", $quantity_difference);
                $rowIndex++;
            }
        }
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($existingFilePath);
    exit('Excel file updated successfully.');
}

$conn->close();
?>
