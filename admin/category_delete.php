<?php
include 'auth_check.php';
include '../config.php';

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit;
}

$id = intval($_GET['id']);

// Get category name first
$cat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM categories WHERE id=$id"));
if (!$cat) {
    header("Location: categories.php");
    exit;
}

// Check if category has products
$count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category='{$cat['name']}'"))['count'];
if ($count > 0) {
    echo "<script>alert('Tidak dapat menghapus kategori yang masih memiliki produk!'); window.location='categories.php';</script>";
    exit;
}

// Delete from categories table
mysqli_query($conn, "DELETE FROM categories WHERE id=$id");

header("Location: categories.php");
exit;
?>

