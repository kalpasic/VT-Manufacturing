<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Check if the user is from Production or Admin
if ($_SESSION['department'] !== 'Production' && $_SESSION['department'] !== 'Admin') {
    header("Location: index.php"); // Redirect to login page if access denied
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Page</title>
    <link rel="stylesheet" type="text/css" href="styleproduction.css">
</head>
<body>
    <div class="company-name">VT MANUFACTURING [PVT] LIMITED - Production</div>
    <h1>Welcome to the Production Page, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>This is a restricted area for Production department members and Admin.</p>
    
    <!-- Main Tabs for Cutting, Sewing, and Washing -->
    <div class="tabs">
        <button onclick="showTab('cutting')">Cutting</button>
        <button onclick="showTab('sewing')">Sewing</button>
        <button onclick="window.location.href='washing.php'">Washing</button> <!-- Change to redirect to washing.php -->
    </div>

    <!-- Main Tab Content -->
    <div id="cutting" class="tab-content">
        <h2>Cutting Section</h2>
        <p>Details and functionalities related to Cutting will be displayed here.</p>
    </div>
    
    <div id="sewing" class="tab-content" style="display:none;">
        <h2>Sewing Section</h2>
        <p>Details and functionalities related to Sewing will be displayed here.</p>
    </div>

    <div class="logout-container">
        <a href="login.php">Logout</a>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all main tab contents
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.style.display = 'none');

            // Show the selected main tab content
            document.getElementById(tabName).style.display = 'block';
        }
    </script>
</body>
</html>
