<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: produk.php");
    exit;
}

$product_id = intval($_GET['id']);

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    // Redirect to login
    // Ideally we could store the intended destination to redirect back after login
    // But for now, simple redirect as per user request "langsung ke halaman login.php"
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Add to Cart Logic
// Check if product already exists in cart
$check_query = "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
$check_result = mysqli_query($conn, $check_query);

$cart_id = 0;

if (mysqli_num_rows($check_result) > 0) {
    // Update Quantity
    $row = mysqli_fetch_assoc($check_result);
    $cart_id = $row['id'];
    // Optional: Increment quantity? Or just leave it? 
    // Usually "Buy Now" implies buying 1 unit. If it's already there, maybe just use it.
    // Let's just use the existing item.
} else {
    // Insert new item
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $cart_id = mysqli_insert_id($conn);
}

// 3. Redirect to Checkout
// checkout.php expects 'selected_items' array in GET if coming from cart selection
// We mimic that format: checkout.php?selected_items[]=123
header("Location: checkout.php?selected_items[]=" . $cart_id);
exit;
?>
