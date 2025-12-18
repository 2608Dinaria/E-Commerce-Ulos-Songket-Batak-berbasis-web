<?php
include 'auth_check.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['category_id']) || !isset($_POST['new_name'])) {
    header("Location: categories.php");
    exit;
}

$category_id = intval($_POST['category_id']);
$old_name = mysqli_real_escape_string($conn, $_POST['old_name']);
$new_name = mysqli_real_escape_string($conn, trim($_POST['new_name']));

// Update category in categories table
mysqli_query($conn, "UPDATE categories SET name='$new_name' WHERE id=$category_id");

// Update all products using this category
mysqli_query($conn, "UPDATE products SET category='$new_name' WHERE category='$old_name'");

header("Location: categories.php");
exit;
?>

