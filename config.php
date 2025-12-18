<?php
$host = 'localhost';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = 'root';
$pass = '';
$db   = 'db_tenun';

// Set timezone to Indonesian Western Time (WIB)
date_default_timezone_set('Asia/Jakarta');

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
