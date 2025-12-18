<?php
include 'auth_check.php';
include '../config.php';

// Get filter
$filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'all';
$query = "SELECT o.*, 
          oi.product_id,
          oi.color,
          p.name as product_name,
          COALESCE(pi.image, p.image) as product_image
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          LEFT JOIN product_images pi ON p.id = pi.product_id AND oi.color = pi.color
          WHERE (oi.id = (SELECT MIN(id) FROM order_items WHERE order_id = o.id) OR oi.id IS NULL)";
if ($filter !== 'all') {
    $query .= " AND o.status='$filter'";
}
$query .= " ORDER BY o.created_at DESC";
$orders = mysqli_query($conn, $query);

$page_title = 'Konfirmasi Pesanan';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Pesanan</h2>
        <div style="display: flex; gap: 8px;">
            <a href="?status=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <a href="?status=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning' : 'btn-secondary' ?>">Pending</a>
            <a href="?status=processing" class="btn btn-sm" style="<?= $filter === 'processing' ? 'background:#2196f3;color:white;' : 'background:#666;color:white;' ?>">Processing</a>
            <a href="?status=shipped" class="btn btn-sm <?= $filter === 'shipped' ? 'btn-success' : 'btn-secondary' ?>">Dikirim</a>
            <a href="?status=completed" class="btn btn-sm <?= $filter === 'completed' ? 'btn-success' : 'btn-secondary' ?>" style="<?= $filter === 'completed' ? 'background:#4caf50;' : '' ?>">Completed</a>
            <a href="?status=cancelled" class="btn btn-sm <?= $filter === 'cancelled' ? 'btn-danger' : 'btn-secondary' ?>">Cancelled</a>
        </div>
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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($order = mysqli_fetch_assoc($orders)): 
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
                            'cancelled' => 'danger'
                        ];
                        $status_labels = [
                            'pending' => 'Pending',
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan'
                        ];
                        ?>
                        <span class="badge badge-<?= $badge[$order['status']] ?? 'secondary' ?>">
                            <?= $status_labels[$order['status']] ?? ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="viewOrder(<?= $order['id'] ?>)" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled' && $order['status'] !== 'shipped'): ?>
                        <button class="btn btn-success btn-sm" onclick="updateStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')" title="Update Status">
                            <i class="fas fa-check"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="orderModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; border-radius:8px; max-width:800px; width:90%; max-height:90vh; overflow-y:auto;">
        <div style="padding:20px; border-bottom:2px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center;">
            <h3 id="modalTitle" style="margin:0;">Detail Pesanan</h3>
            <button onclick="closeModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div id="modalContent" style="padding:25px;"></div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function viewOrder(id) {
    document.getElementById('orderModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Detail Pesanan #' + id;
    document.getElementById('modalContent').innerHTML = '<p style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    
    fetch('order_detail.php?id=' + id)
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
        });
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function updateStatus(id, current) {
    let next = '';
    let label = '';
    
    // Workflow: pending → processing → shipped (only admin can do this)
    if (current === 'pending') {
        next = 'processing';
        label = 'Processing (Sedang Diproses)';
    } else if (current === 'processing') {
        next = 'shipped';
        label = 'Shipped (Sudah Dikirim)';
    } else {
        alert('Status tidak dapat diubah lagi. User yang akan mengkonfirmasi penerimaan barang.');
        return;
    }
    
    if (confirm(`Update status pesanan #${id} menjadi "${label}"?`)) {
        window.location.href = `order_update.php?id=${id}&status=${next}`;
    }
}

document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
