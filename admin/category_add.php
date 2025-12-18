<?php
include 'auth_check.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['category_name'])) {
    header("Location: categories.php");
    exit;
}

$category_name = mysqli_real_escape_string($conn, trim($_POST['category_name']));

// Insert into categories table
$result = mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$category_name')");

if ($result) {
    echo "<script>alert('Kategori berhasil ditambahkan!'); window.location='categories.php';</script>";
} else {
    echo "<script>alert('Kategori sudah ada atau terjadi kesalahan!'); window.location='categories.php';</script>";
}
?>

