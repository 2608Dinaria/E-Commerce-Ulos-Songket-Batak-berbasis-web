<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: pesanan_saya.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

// Verify order belongs to user and status is 'shipped'
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'shipped'"));

if (!$order) {
    echo "<script>alert('Pesanan tidak ditemukan atau tidak dapat dikonfirmasi!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Update status to completed
mysqli_query($conn, "UPDATE orders SET status = 'completed' WHERE id = $order_id");

echo "<script>alert('Terima kasih! Pesanan telah dikonfirmasi sebagai selesai.'); window.location='pesanan_saya.php';</script>";
exit;
?>
