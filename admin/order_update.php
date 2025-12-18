<?php
include 'auth_check.php';
include '../config.php';

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);

$valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header("Location: orders.php");
    exit;
}

// Get current order status
$current_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM orders WHERE id = $order_id"));

// If changing to 'cancelled' and order was not already cancelled, restore stock
if ($status === 'cancelled' && $current_order && $current_order['status'] !== 'cancelled') {
    // Get all order items
    $items = mysqli_query($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
    
    // Restore stock for each item
    while ($item = mysqli_fetch_assoc($items)) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        mysqli_query($conn, "UPDATE products SET stock = stock + $quantity WHERE id = $product_id");
    }
}

// Update order status
mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");

header("Location: orders.php");
exit;
?>
