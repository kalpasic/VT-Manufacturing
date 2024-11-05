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
    <title>Washing Section</title>
    <link rel="stylesheet" type="text/css" href="washing.css">
</head>
<body>
    <div class="container">
        <!-- Left side with welcome message -->
        <div class="left-panel">
            <div class="company-name">VT MANUFACTURING [PVT] LIMITED - Washing Section</div>
            <h1 class="welcome-message">Welcome to the Washing Section, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>

        <!-- Right side with buttons -->
        <div class="right-panel">
            <h2>Select an Option</h2>
            <div class="tabs">
                <button onclick="window.location.href='washing_sending.php'">Washing Sending</button>
                <button onclick="window.location.href='washing_receiving.php'">Washing Receiving</button>
                <button onclick="window.location.href='washing_summary.php'">Summary</button> <!-- Added Summary Button -->
            </div>
        </div>
    </div>
</body>
</html>
