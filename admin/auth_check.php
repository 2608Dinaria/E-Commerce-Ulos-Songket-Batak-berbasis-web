<?php
// Admin Authentication Check
// Include this file at the top of every admin page

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Set admin info in variables for easy access
$admin_id = $_SESSION['admin_id'];
$admin_email = $_SESSION['admin_email'];
$admin_name = $_SESSION['admin_name'];
?>
