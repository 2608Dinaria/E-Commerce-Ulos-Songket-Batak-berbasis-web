<?php
// session_start(); // Already called in config.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? '';
$shipping_method = $_POST['shipping_method'] ?? '';

// Basic Validation
if (empty($payment_method)) {
    echo "<script>alert('Mohon pilih metode pembayaran!'); window.history.back();</script>";
    exit;
}
if (empty($shipping_method)) {
    echo "<script>alert('Mohon pilih metode pengiriman!'); window.history.back();</script>";
    exit;
}

// Fetch user address
// Fetch user address
if (isset($_POST['address_id']) && !empty($_POST['address_id'])) {
    $addr_id = intval($_POST['address_id']);
    $addr_query = "SELECT * FROM user_addresses WHERE id = '$addr_id' AND user_id = '$user_id'";
} else {
    // Fallback if something goes wrong (e.g. direct access)
    $addr_query = "SELECT * FROM user_addresses WHERE user_id = '$user_id' ORDER BY id ASC LIMIT 1";
}
$addr_result = mysqli_query($conn, $addr_query);
$address = mysqli_fetch_assoc($addr_result);

if (!$address) {
    echo "<script>alert('Mohon lengkapi alamat pengiriman!'); window.location.href='tambah_alamat.php';</script>";
    exit;
}

$full_address = $address['address_line'] . ", " . $address['city'] . ", " . $address['province'] . " " . $address['postal_code'];
$customer_name = $address['first_name'] . " " . $address['last_name'];
$customer_phone = $address['phone'];
$customer_email = ""; // User table has email, let's fetch if needed, but orders table has customer_email column. We can get it from users table.

// Get Email
$user_query = mysqli_query($conn, "SELECT email FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$customer_email = $user_data['email'];

// Calculate Totals (Securely on server)
// Validate Selected Items
if (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
    echo "<script>alert('Tidak ada item yang dipilih!'); window.location.href='index.php';</script>";
    exit;
}

$selected_ids_array = array_map('intval', $_POST['selected_items']);
$selected_ids = implode(',', $selected_ids_array);

$cart_query = "SELECT c.*, p.name, p.price, p.image 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = '$user_id' AND c.id IN ($selected_ids)";
$cart_result = mysqli_query($conn, $cart_query);

$subtotal = 0;
$items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $subtotal += $row['price'] * $row['quantity'];
    $items[] = $row;
}

if (empty($items)) {
    echo "<script>alert('Keranjang belanja kosong atau item tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

// Shipping Cost Logic
$shipping_cost = 0;
if ($shipping_method == 'Regular') {
    $shipping_cost = 10000; 
} elseif ($shipping_method == 'Hemat') {
    $shipping_cost = 5000;
} elseif ($shipping_method == 'Same Day') {
    $shipping_cost = 15000;
} elseif ($shipping_method == 'Instant') {
    $shipping_cost = 30000;
}

$service_fee = 2000;
$total_price = $subtotal + $shipping_cost + $service_fee;

// Set initial status to processing for ALL methods as per user request (Simulation mode)
$initial_status = 'processing';

// Insert into orders
$stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_price, payment_method, shipping_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "issssssss", $user_id, $customer_name, $customer_email, $customer_phone, $full_address, $total_price, $payment_method, $shipping_method, $initial_status);

if (mysqli_stmt_execute($stmt)) {
    $order_id = mysqli_insert_id($conn);
    
    // Insert order items and reduce stock
    $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, color, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        // Get color from cart item, default to 'Default' if not set
        $color = isset($item['color']) ? $item['color'] : 'Default';
        
        // Insert order item with color
        mysqli_stmt_bind_param($stmt_item, "iisid", $order_id, $item['product_id'], $color, $item['quantity'], $item['price']);
        mysqli_stmt_execute($stmt_item);
        
        // Reduce product stock
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Check current stock first
        $stock_check = mysqli_query($conn, "SELECT stock FROM products WHERE id = $product_id");
        $stock_data = mysqli_fetch_assoc($stock_check);
        
        if ($stock_data && $stock_data['stock'] >= $quantity) {
            // Reduce stock
            mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
        } else {
            // Stock insufficient - rollback order (optional: you can handle this differently)
            mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id");
            mysqli_query($conn, "DELETE FROM orders WHERE id = $order_id");
            echo "<script>alert('Stok produk {$item['name']} tidak mencukupi!'); window.location.href='keranjang.php';</script>";
            exit;
        }
    }
    
    // Clear ONLY Selected Items from Cart
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id' AND id IN ($selected_ids)");
    
    // Redirect to Payment Page
    header("Location: pembayaran.php?id=" . $order_id);
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
