<?php
include 'auth_check.php';
include '../config.php';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$sales_query = "SELECT * FROM orders WHERE status='completed' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date' ORDER BY created_at DESC";
$sales = mysqli_query($conn, $sales_query);

$total_sales = 0;
$total_orders = 0;
$temp_sales = mysqli_query($conn, $sales_query);
while ($order = mysqli_fetch_assoc($temp_sales)) {
    $total_sales += $order['total_price'];
    $total_orders++;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: #d32f2f; border-bottom: 3px solid #d32f2f; padding-bottom: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #d32f2f; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .total { font-weight: bold; background: #f5f5f5; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print" style="padding:10px 20px; background:#d32f2f; color:white; border:none; border-radius:5px; cursor:pointer; margin-bottom:20px;">
        <i class="fas fa-print"></i> Cetak Laporan
    </button>
    
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <h2>VonaTa - Tenun Nusantara</h2>
        <p>Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></p>
    </div>
    
    <div class="summary">
        <table style="border:none;">
            <tr>
                <td style="border:none;"><strong>Total Pesanan:</strong></td>
                <td style="border:none;"><?= $total_orders ?> pesanan</td>
                <td style="border:none;"><strong>Total Penjualan:</strong></td>
                <td style="border:none;">Rp <?= number_format($total_sales, 0, ',', '.') ?></td>
            </tr>
        </table>
    </div>
    
    <h3>Detail Penjualan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Pesanan</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($sale = mysqli_fetch_assoc($sales)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>#<?= $sale['id'] ?></td>
                <td><?= date('d M Y, H:i', strtotime($sale['created_at'])) ?></td>
                <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                <td>Rp <?= number_format($sale['total_price'], 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="4" style="text-align:right; padding-right:20px;"><strong>TOTAL:</strong></td>
                <td><strong>Rp <?= number_format($total_sales, 0, ',', '.') ?></strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="margin-top:50px; text-align:right;">
        <p>Dicetak pada: <?= date('d M Y, H:i') ?></p>
        <p>Oleh: <?= $admin_name ?></p>
    </div>
</body>
</html>
