<?php
include 'auth_check.php';
include '../config.php';

// Get statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user' OR role IS NULL"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status='completed'"))['total'];
$total_returns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM returns"))['count'];
$pending_returns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM returns WHERE status='pending'"))['count'];

// Get recent orders with first product info
$recent_orders = mysqli_query($conn, "
    SELECT o.*, 
           oi.product_id,
           oi.color,
           p.name as product_name,
           COALESCE(pi.image, p.image) as product_image
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND oi.color = pi.color
    WHERE oi.id = (SELECT MIN(id) FROM order_items WHERE order_id = o.id)
       OR oi.id IS NULL
    ORDER BY o.created_at DESC 
    LIMIT 10
");

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-box stat-icon"></i>
        <div class="stat-value"><?= $total_products ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card" style="border-left-color: #4caf50;">
        <i class="fas fa-users stat-icon"></i>
        <div class="stat-value" style="color: #4caf50;"><?= $total_users ?></div>
        <div class="stat-label">Total Pengguna</div>
    </div>
    <div class="stat-card" style="border-left-color: #ff9800;">
        <i class="fas fa-shopping-cart stat-icon"></i>
        <div class="stat-value" style="color: #ff9800;"><?= $total_orders ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card" style="border-left-color: #2196f3;">
        <i class="fas fa-dollar-sign stat-icon"></i>
        <div class="stat-value" style="color: #2196f3;">Rp <?= number_format($total_sales, 0, ',', '.') ?></div>
        <div class="stat-label">Total Penjualan</div>
    </div>
    <div class="stat-card" style="border-left-color: #f44336;">
        <i class="fas fa-undo stat-icon"></i>
        <div class="stat-value" style="color: #f44336;"><?= $total_returns ?></div>
        <div class="stat-label">Pengembalian Barang</div>
        <?php if ($pending_returns > 0): ?>
            <small style="color: #ff9800; font-weight: 600; margin-top: 5px; display: block;">
                <?= $pending_returns ?> pending
            </small>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Pesanan Terbaru</h2>
        <a href="orders.php" class="btn btn-primary btn-sm">Lihat Semua</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 300px;">Produk</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($order = mysqli_fetch_assoc($recent_orders)): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php if ($order['product_image']): ?>
                                <img src="../assets/img/<?= htmlspecialchars($order['product_image']) ?>" 
                                     alt="Product" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="color: #999;"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight: 600; color: #333;"><?= htmlspecialchars($order['product_name'] ?? 'N/A') ?></div>
                                <small style="color: #666;"><?= htmlspecialchars($order['customer_email']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><strong>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></strong></td>
                    <td>
                        <?php
                        $badge = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'returned' => 'warning'
                        ];
                        $status_labels = [
                            'pending' => 'Pending',
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            'returned' => 'Dikembalikan'
                        ];
                        ?>
                        <span class="badge badge-<?= $badge[$order['status']] ?? 'secondary' ?>">
                            <?= $status_labels[$order['status']] ?? ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
