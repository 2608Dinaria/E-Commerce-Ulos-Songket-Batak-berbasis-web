<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$current_page = 'pesanan_saya';

// Extra CSS for this page
$extra_css = '
    <style>
        .account-container {
            display: flex;
            padding: 50px 80px;
            gap: 40px;
            background: #fff;
        }
        .account-sidebar {
            flex: 1;
            max-width: 300px;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        .sidebar-menu li {
            border-bottom: 1px solid #ccc;
        }
        .sidebar-menu li:last-child {
            border-bottom: none;
        }
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .sidebar-menu a.active {
            background-color: red; /* Red color from design */
            color: #fff;
        }
        .sidebar-menu a:hover:not(.active) {
            background-color: #d0d0d0;
        }
        .sidebar-header {
            padding: 15px 20px;
            font-weight: bold;
            background: #e0e0e0;
            border-bottom: 1px solid #ccc;
        }
        
        .account-content {
            flex: 3;
        }
        .content-header {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Tabs */
        .order-tabs {
            display: flex;
            border-bottom: 1px solid #dcdcdc;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        .order-tab {
            padding: 15px 25px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            white-space: nowrap;
            border-bottom: 4px solid transparent;
            margin-bottom: -1px;
        }
        .order-tab.active {
            color: #000;
            font-weight: bold;
            border-bottom: 4px solid #000;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px;
        }
        .empty-btn {
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
    </style>
';

include 'includes/header.php';
?>

    <div class="account-container">
        <!-- Sidebar -->
        <div class="account-sidebar">
            <div class="sidebar-menu">
                <div class="sidebar-header">AKUN SAYA</div>
                <a href="akun.php">Informasi akun</a>
                <a href="pesanan_saya.php" class="active">Pesanan Saya</a>
                <a href="permohonan_penghapusan_akun.php">Permohonan Penghapusan Akun</a>
                <a href="logout.php">Keluar</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="account-content">
            <div class="content-header">
                <i class="fas fa-box"></i> Pesanan
            </div>

            <?php
            $status_param = isset($_GET['status']) ? $_GET['status'] : 'all';
            
            // Map URL param to DB status
            // DB Statuses: pending, processing, shipped, completed, cancelled, return
            // 'diproses' tab => pending, processing
            // 'terkirim' => shipped, completed
            
            $db_status = [];
            if ($status_param == 'diproses') {
                $db_status = ["'pending'", "'processing'"];
            } elseif ($status_param == 'terkirim') {
                $db_status = ["'shipped'", "'completed'"];
            } elseif ($status_param == 'pengembalian') {
                // Placeholder
                $db_status = ["'returned'"]; 
            } elseif ($status_param == 'dibatalkan') {
                $db_status = ["'cancelled'"];
            }
            
            // Build Query
            $sql = "SELECT * FROM orders WHERE user_id = '$user_id'";
            if (!empty($db_status)) {
                $sql .= " AND status IN (" . implode(',', $db_status) . ")";
            }
            $sql .= " ORDER BY created_at DESC";
            
            $orders_result = mysqli_query($conn, $sql);
            ?>

            <div class="order-tabs">
                <a href="?status=all" class="order-tab <?= $status_param == 'all' ? 'active' : '' ?>">Semua</a>
                <a href="?status=diproses" class="order-tab <?= $status_param == 'diproses' ? 'active' : '' ?>">Diproses</a>
                <a href="?status=terkirim" class="order-tab <?= $status_param == 'terkirim' ? 'active' : '' ?>">Dikirim/Selesai</a>
                <a href="?status=pengembalian" class="order-tab <?= $status_param == 'pengembalian' ? 'active' : '' ?>">Penukaran/Pengembalian</a>
                <a href="?status=dibatalkan" class="order-tab <?= $status_param == 'dibatalkan' ? 'active' : '' ?>">Dibatalkan</a>
            </div>

            <?php if (mysqli_num_rows($orders_result) > 0): ?>
                <div class="orders-list">
                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                        <?php
                        // Fetch first item to display image/name with correct color variant
                        $oid = $order['id'];
                        $item_query = "SELECT oi.*, p.name, p.image, 
                                       COALESCE(pi.image, p.image) as display_image
                                       FROM order_items oi 
                                       JOIN products p ON oi.product_id = p.id 
                                       LEFT JOIN product_images pi ON oi.product_id = pi.product_id AND oi.color = pi.color
                                       WHERE oi.order_id = '$oid' LIMIT 1";
                        $item_result = mysqli_query($conn, $item_query);
                        $item = mysqli_fetch_assoc($item_result);
                        
                        // Count total items
                        $count_query = "SELECT COUNT(*) as total FROM order_items WHERE order_id = '$oid'";
                        $count_res = mysqli_fetch_assoc(mysqli_query($conn, $count_query));
                        $more_items = $count_res['total'] - 1;
                        
                        // Estimation
                         // Calculate deadlines (e.g. 5 days from creation)
                        $created_at = strtotime($order['created_at']);
                        $est_start = $created_at + (3 * 24 * 3600); // +3 days
                        $est_end = $created_at + (5 * 24 * 3600);   // +5 days
                        
                        // Format Indonesian Date
                        $months = [
                            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 
                            'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 
                            'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
                        ];
                        
                        $start_str = date("d", $est_start);
                        $end_str = date("d F Y", $est_end);
                        foreach ($months as $en => $id) $end_str = str_replace($en, $id, $end_str);
                        $est_text = "$start_str-$end_str";
                        ?>
                        
                        <div class="order-card">
                            <div class="order-body">
                                <img src="assets/img/<?= rawurlencode($item['display_image']) ?>" alt="<?= $item['name'] ?>" class="order-img">
                                <div class="order-details">
                                    <div class="product-name"><?= $item['name'] ?></div>
                                    <div class="product-variant"><?= htmlspecialchars($item['color']) ?></div>
                                    <div class="product-price">Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                                </div>
                                <div class="order-meta">
                                    <div class="qty">x<?= $item['quantity'] ?></div>
                                    <?php if ($more_items > 0): ?>
                                        <div class="more-items">+<?= $more_items ?> produk lainnya</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="order-total-row">
                                <span class="total-label">Total Pesanan:</span>
                                <span class="total-amount">Rp<?= number_format($order['total_price'], 0, ',', '.') ?></span>
                            </div>
                            
                            
                            <?php if ($order['status'] === 'shipped'): ?>
                            <!-- Show confirmation button and tracking for shipped orders -->
                            <div class="order-footer" style="padding: 15px; background: #fff3e0; border-top: 1px solid #e0e0e0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <div style="font-weight: 600; color: #ff9800; margin-bottom: 5px;">
                                            <i class="fas fa-shipping-fast"></i> Pesanan Sudah Dikirim
                                        </div>
                                        <div style="font-size: 0.85rem; color: #666;">Konfirmasi jika barang sudah diterima</div>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <a href="lacak_pesanan.php?id=<?= $oid ?>" style="background: #2196f3; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block;">
                                            <i class="fas fa-route"></i> Lacak Pesanan
                                        </a>
                                        <button onclick="confirmOrder(<?= $oid ?>)" style="background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                                            <i class="fas fa-check"></i> Pesanan Diterima
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php elseif ($order['status'] === 'returned'): ?>
                            <!-- Show buttons for returned orders -->
                            <?php
                            // Get return ID
                            $return_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM returns WHERE order_id = $oid"));
                            $return_id = $return_data ? $return_data['id'] : '#';
                            ?>
                            <div class="order-footer" style="padding: 15px; background: #e8f5e9; border-top: 1px solid #e0e0e0;">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;">
                                    <a href="return_status.php?id=<?= $return_id ?>" style="flex: 1; min-width: 200px; background: #ff9800; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                        <i class="fas fa-info-circle"></i> Status Pengembalian
                                    </a>
                                    <a href="submit_rating.php?order_id=<?= $oid ?>" style="flex: 1; min-width: 200px; background: #ffc107; color: #333; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                        <i class="fas fa-star"></i> Beri Rating
                                    </a>
                                    <a href="lacak_pesanan.php?id=<?= $oid ?>" style="flex: 1; min-width: 200px; background: #2196f3; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                        <i class="fas fa-route"></i> Lacak Pesanan
                                    </a>
                                </div>
                            </div>
                            <?php elseif ($order['status'] === 'completed'): ?>
                            <!-- Show return & rating buttons for completed orders -->
                            <?php
                            // Check if return exists
                            $return_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, status FROM returns WHERE order_id = $oid"));
                            ?>
                            <div class="order-footer" style="padding: 15px; background: #e8f5e9; border-top: 1px solid #e0e0e0;">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <?php if ($return_check): ?>
                                        <a href="return_status.php?id=<?= $return_check['id'] ?>" style="flex: 1; min-width: 200px; background: #ff9800; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                            <i class="fas fa-info-circle"></i> Status Pengembalian
                                        </a>
                                    <?php else: ?>
                                        <a href="submit_return.php?order_id=<?= $oid ?>" style="flex: 1; min-width: 200px; background: #f44336; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                            <i class="fas fa-undo"></i> Ajukan Pengembalian
                                        </a>
                                    <?php endif; ?>
                                    <a href="submit_rating.php?order_id=<?= $oid ?>" style="flex: 1; min-width: 200px; background: #ffc107; color: #333; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                        <i class="fas fa-star"></i> Beri Rating
                                    </a>
                                    <a href="lacak_pesanan.php?id=<?= $oid ?>" style="flex: 1; min-width: 200px; background: #2196f3; color: white; padding: 12px; text-align: center; border-radius: 5px; text-decoration: none; font-weight: 600;">
                                        <i class="fas fa-route"></i> Lacak Pesanan
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="order-footer" onclick="window.location.href='lacak_pesanan.php?id=<?= $oid ?>'" style="cursor: pointer;">
                                <div class="est-label">Estimasi Tiba</div>
                                <div class="est-date"><?= $est_text ?> <span style="float:right">></span></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <p style="margin-bottom: 20px;">Belum ada Pesanan</p>
                    <a href="index.php" class="empty-btn">Belanja Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        /* Card Styles */
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fff;
            overflow: hidden;
        }
        .order-body {
            display: flex;
            padding: 20px;
            gap: 20px;
        }
        .order-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        .order-details {
            flex: 1;
        }
        .product-name {
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .product-variant {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .product-price {
            color: red;
            font-weight: 500;
        }
        .order-meta {
            text-align: right;
            min-width: 80px;
        }
        .qty {
            font-weight: bold;
            color: #333;
        }
        .more-items {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }
        .order-total-row {
            padding: 0 20px 15px; /* bottom padding */
            text-align: right;
            font-size: 1.1rem;
        }
        .total-amount {
            color: red;
            font-weight: bold;
            margin-left: 10px;
        }
        .order-footer {
            background: #f5f5f5;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }
        .est-label {
            font-weight: bold;
            color: #333;
        }
        .est-date {
            font-weight: bold;
            color: #000;
        }
    </style>

<script>
function confirmOrder(orderId) {
    if (confirm('Konfirmasi bahwa Anda sudah menerima pesanan ini?')) {
        window.location.href = 'confirm_order.php?order_id=' + orderId;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
