<?php
include 'auth_check.php';
include '../config.php';

if (!isset($_GET['id'])) exit('Invalid order ID');

$order_id = intval($_GET['id']);
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id"));
if (!$order) exit('Order not found');

$items = mysqli_query($conn, "SELECT oi.*, p.name FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = $order_id");
?>

<table style="width:100%; margin-bottom:20px;">
    <tr><td style="padding:8px; width:150px; font-weight:600;">Nama</td><td style="padding:8px;"><?= htmlspecialchars($order['customer_name']) ?></td></tr>
    <tr style="background:#f9f9f9;"><td style="padding:8px; font-weight:600;">Email</td><td style="padding:8px;"><?= htmlspecialchars($order['customer_email']) ?></td></tr>
    <tr><td style="padding:8px; font-weight:600;">Telepon</td><td style="padding:8px;"><?= htmlspecialchars($order['customer_phone']) ?></td></tr>
    <tr style="background:#f9f9f9;"><td style="padding:8px; font-weight:600;">Alamat</td><td style="padding:8px;"><?= htmlspecialchars($order['customer_address']) ?></td></tr>
</table>

<h4 style="margin:20px 0 15px;">Item Pesanan</h4>
<table style="width:100%; border-collapse:collapse; border:1px solid #e0e0e0;">
    <thead>
        <tr style="background:#f5f5f5;">
            <th style="padding:12px; text-align:left; border-bottom:2px solid #ddd;">Produk</th>
            <th style="padding:12px; text-align:right; border-bottom:2px solid #ddd;">Harga</th>
            <th style="padding:12px; text-align:center; border-bottom:2px solid #ddd;">Qty</th>
            <th style="padding:12px; text-align:right; border-bottom:2px solid #ddd;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee;"><?= htmlspecialchars($item['name']) ?></td>
            <td style="padding:12px; text-align:right; border-bottom:1px solid #eee;">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
            <td style="padding:12px; text-align:center; border-bottom:1px solid #eee;"><?= $item['quantity'] ?></td>
            <td style="padding:12px; text-align:right; border-bottom:1px solid #eee;">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr style="background:#f5f5f5;">
            <th colspan="3" style="padding:15px; text-align:right; font-size:1.1rem;">Total</th>
            <th style="padding:15px; text-align:right; font-size:1.1rem; color:#d32f2f;">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></th>
        </tr>
    </tfoot>
</table>
