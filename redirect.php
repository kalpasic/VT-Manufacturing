<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Check if the user is an Admin
if ($_SESSION['department'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login page if access denied
    exit();
}

// Handle the redirection based on selected page
if (isset($_POST['page'])) {
    $page = $_POST['page'];
    header("Location:C:\xampp\htdocs\login " . $page); // Redirect to the specified page
    exit();
} else {
    header("Location: index.php"); // If no page is selected, redirect to login page
    exit();
}
?>
