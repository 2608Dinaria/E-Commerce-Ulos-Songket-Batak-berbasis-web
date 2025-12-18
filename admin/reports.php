<?php
include 'auth_check.php';
include '../config.php';

// Get date filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get sales data
$sales_query = "SELECT * FROM orders WHERE status='completed' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date' ORDER BY created_at DESC";
$sales = mysqli_query($conn, $sales_query);

// Calculate totals
$total_sales = 0;
$total_orders = 0;
$sales_by_category = [];

$temp_sales = mysqli_query($conn, $sales_query);
while ($order = mysqli_fetch_assoc($temp_sales)) {
    $total_sales += $order['total_price'];
    $total_orders++;
    
    // Get order items to calculate category sales
    $items = mysqli_query($conn, "SELECT oi.*, p.category FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = {$order['id']}");
    while ($item = mysqli_fetch_assoc($items)) {
        $cat = $item['category'];
        if (!isset($sales_by_category[$cat])) {
            $sales_by_category[$cat] = 0;
        }
        $sales_by_category[$cat] += ($item['price'] * $item['quantity']);
    }
}

$page_title = 'Laporan Penjualan';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filter Laporan</h2>
    </div>
    <div class="card-body">
        <form method="GET" style="display:flex; gap:15px; align-items:end;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="print_report.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" target="_blank" class="btn btn-success"><i class="fas fa-print"></i> Cetak Laporan</a>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-shopping-cart stat-icon"></i>
        <div class="stat-value"><?= $total_orders ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card" style="border-left-color: #4caf50;">
        <i class="fas fa-dollar-sign stat-icon"></i>
        <div class="stat-value" style="color: #4caf50;">Rp <?= number_format($total_sales, 0, ',', '.') ?></div>
        <div class="stat-label">Total Penjualan</div>
    </div>
</div>

<!-- Category Breakdown -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Penjualan per Kategori</h2>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Total Penjualan</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_by_category as $cat => $amount): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cat) ?></strong></td>
                    <td>Rp <?= number_format($amount, 0, ',', '.') ?></td>
                    <td>
                        <div style="background:#f0f0f0; border-radius:10px; height:20px; width:100%; max-width:200px; overflow:hidden;">
                            <div style="background:#4caf50; height:100%; width:<?= $total_sales > 0 ? ($amount/$total_sales)*100 : 0 ?>%;"></div>
                        </div>
                        <?= $total_sales > 0 ? round(($amount/$total_sales)*100, 1) : 0 ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Sales List -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Detail Penjualan</h2>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($sale = mysqli_fetch_assoc($sales)): ?>
                <tr>
                    <td>#<?= $sale['id'] ?></td>
                    <td><?= date('d M Y, H:i', strtotime($sale['created_at'])) ?></td>
                    <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                    <td><strong>Rp <?= number_format($sale['total_price'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
