<?php
// session_start(); // Already called in config.php
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch Order
$query = "SELECT * FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "Order not found.";
    exit;
}

// Order Items (for display)
$items_query = "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);
$items = [];
while ($row = mysqli_fetch_assoc($items_result)) {
    $items[] = $row;
}

// Logic for timeline active state
$status = $order['status']; 

// Auto-fix for ALL orders that are still 'pending' (User request: treat all as paid/processed)
if ($status == 'pending') {
    $status = 'processing';
    // Update DB to reflect this
    mysqli_query($conn, "UPDATE orders SET status='processing' WHERE id='$order_id'");
}

// Mapping: pending -> 1, processing -> 2, shipped -> 3, completed -> 4, returned -> 5
$step = 1;
if ($status == 'pending') $step = 1;
if ($status == 'processing') $step = 2;
if ($status == 'shipped') $step = 3;
if ($status == 'completed') $step = 4;
if ($status == 'returned') $step = 5;
if ($status == 'cancelled') $step = 0; // Special case

// Get return info if applicable
$return_date = '';
if ($step == 5) {
    $ret_q = mysqli_query($conn, "SELECT created_at FROM returns WHERE order_id = '$order_id'");
    if ($r = mysqli_fetch_assoc($ret_q)) {
        $return_date = date("d M Y H:i", strtotime($r['created_at']));
    }
}

include 'includes/header.php';
?>

<div class="tracking-container">
    <div class="tracking-header">
        <a href="pesanan_saya.php?status=diproses" class="back-link"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2>Lacak Pesanan</h2>
    </div>

    <div class="order-info-card">
        <div class="info-row">
            <span class="label">No. Pesanan</span>
            <span class="value">#VT<?= date('Ymd', strtotime($order['created_at'])) . $order['id'] ?></span>
        </div>
        <div class="info-row">
            <span class="label">Tanggal Pemesanan</span>
            <span class="value"><?= date("d F Y, H:i", strtotime($order['created_at'])) ?></span>
        </div>
         <div class="info-row">
            <span class="label">Kurir</span>
            <span class="value"><?= $order['shipping_method'] ?></span>
        </div>
    </div>

    <!-- Timeline -->
    <div class="timeline-box">
        <h3>Status Pengiriman</h3>
        
        <?php if ($status == 'cancelled'): ?>
            <div class="status-cancelled">
                <i class="fas fa-times-circle"></i> Pesanan Dibatalkan
            </div>
        <?php else: ?>
            <ul class="timeline">
                <li class="timeline-item <?= $step >= 1 ? 'active' : '' ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Dibuat</div>
                        <div class="timeline-date"><?= date("d M Y H:i", strtotime($order['created_at'])) ?></div>
                        <p>
                            <?php if ($step >= 2): ?>
                                Pembayaran Berhasil. Pesanan diteruskan ke penjual.
                            <?php else: ?>
                                Menunggu Pembayaran / Verifikasi.
                            <?php endif; ?>
                        </p>
                    </div>
                </li>
                <li class="timeline-item <?= $step >= 2 ? 'active' : '' ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Diproses</div>
                        <?php if ($step >= 2): ?>
                             <!-- Simulated date (creation + 1 hour) -->
                            <div class="timeline-date"><?= date("d M Y H:i", strtotime($order['created_at']) + 3600) ?></div>
                            <p>Penjual sedang menyiapkan pesananmu.</p>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="timeline-item <?= $step >= 3 ? 'active' : '' ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Dikirim</div>
                         <?php if ($step >= 3): ?>
                            <div class="timeline-date"><?= date("d M Y H:i", strtotime($order['created_at']) + 86400) ?></div>
                            <p>Paket sedang dalam perjalanan ke alamat tujuan.</p>
                        <?php endif; ?>
                    </div>
                </li>
                 <li class="timeline-item <?= $step >= 4 ? 'active' : '' ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-title">Pesanan Tiba</div>
                        <?php if ($step >= 4): ?>
                            <div class="timeline-date"><?= date("d M Y H:i", strtotime($order['created_at']) + 172800) ?></div>
                            <p>Paket telah diterima.</p>
                        <?php endif; ?>
                    </div>
                </li>
                <!-- Returned Step -->
                <?php if ($step == 5): ?>
                <li class="timeline-item active">
                    <div class="timeline-marker" style="background: #ff9800; border-color: #ff9800;"></div>
                    <div class="timeline-content">
                        <div class="timeline-title" style="color: #ff9800;">Pesanan Dikembalikan</div>
                        <div class="timeline-date"><?= $return_date ?></div>
                        <p>Pesanan telah berhasil dikembalikan.</p>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<style>
    .tracking-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 0 20px;
        min-height: 60vh;
    }
    .tracking-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }
    .tracking-header h2 {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0;
    }
    .back-link {
        color: #333;
        text-decoration: none;
        font-size: 1.1rem;
    }
    .order-info-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    .info-row:last-child {
        margin-bottom: 0;
    }
    .info-row .label {
        color: #666;
    }
    .info-row .value {
        font-weight: bold;
        color: #000;
    }
    
    .timeline-box {
        background: #fff;
        padding: 20px;
    }
    .timeline-box h3 {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .timeline {
        list-style: none;
        padding: 0;
        margin: 0;
        border-left: 2px solid #ddd;
        margin-left: 10px;
    }
    .timeline-item {
        margin-bottom: 30px;
        position: relative;
        padding-left: 20px;
    }
    .timeline-marker {
        position: absolute;
        left: -8px; /* Center on the border line */
        top: 0;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #ddd;
        border: 2px solid #fff;
    }
    .timeline-item.active .timeline-marker {
        background: red; /* Active color */
    }
    .timeline-item.active .timeline-title {
        color: red;
        font-weight: bold;
    }
    .timeline-title {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    .timeline-date {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 5px;
    }
    .timeline-content p {
        margin: 0;
        color: #555;
        font-size: 0.9rem;
    }
    .status-cancelled {
        color: red;
        font-weight: bold;
        text-align: center;
        padding: 20px;
        background: #fff5f5;
        border-radius: 8px;
    }
</style>

<?php include 'includes/footer.php'; ?>
