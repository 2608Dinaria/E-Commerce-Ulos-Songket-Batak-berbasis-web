<?php
include 'auth_check.php';
include '../config.php';

// Get filter
$filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'all';
$query = "SELECT r.*, 
          o.id as order_id, 
          o.total_price, 
          o.customer_name, 
          o.customer_email,
          oi.product_id,
          oi.color,
          p.name as product_name,
          COALESCE(pi.image, p.image) as product_image
          FROM returns r 
          JOIN orders o ON r.order_id = o.id
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          LEFT JOIN product_images pi ON p.id = pi.product_id AND oi.color = pi.color
          WHERE (oi.id = (SELECT MIN(id) FROM order_items WHERE order_id = o.id) OR oi.id IS NULL)";
if ($filter !== 'all') {
    $query .= " AND r.status='$filter'";
}
$query .= " ORDER BY r.created_at DESC";
$returns = mysqli_query($conn, $query);

$page_title = 'Kelola Pengembalian';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Pengembalian Barang</h2>
        <div style="display: flex; gap: 8px;">
            <a href="?status=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <a href="?status=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning' : 'btn-secondary' ?>">Pending</a>
            <a href="?status=approved" class="btn btn-sm <?= $filter === 'approved' ? 'btn-info' : 'btn-secondary' ?>">Disetujui</a>
            <a href="?status=rejected" class="btn btn-sm <?= $filter === 'rejected' ? 'btn-danger' : 'btn-secondary' ?>">Ditolak</a>
            <a href="?status=shipped_back" class="btn btn-sm" style="<?= $filter === 'shipped_back' ? 'background:#9c27b0;color:white;' : 'background:#666;color:white;' ?>">Barang Diterima</a>
            <a href="?status=completed" class="btn btn-sm <?= $filter === 'completed' ? 'btn-success' : 'btn-secondary' ?>">Selesai</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 300px;">Produk</th>
                    <th>User</th>
                    <th>Jumlah Refund</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($return = mysqli_fetch_assoc($returns)): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php if ($return['product_image']): ?>
                                <img src="../assets/img/<?= htmlspecialchars($return['product_image']) ?>" 
                                     alt="Product" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="color: #999;"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight: 600; color: #333;"><?= htmlspecialchars($return['product_name'] ?? 'N/A') ?></div>
                                <small style="color: #666;">Order #<?= $return['order_id'] ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?= htmlspecialchars($return['customer_name']) ?><br>
                        <small style="color: #666;"><?= htmlspecialchars($return['customer_email']) ?></small>
                    </td>
                    <td><strong>Rp <?= number_format($return['refund_amount'], 0, ',', '.') ?></strong></td>
                    <td>
                        <?php
                        $badge = [
                            'pending' => ['warning', 'Pending'],
                            'approved' => ['info', 'Disetujui'],
                            'rejected' => ['danger', 'Ditolak'],
                            'shipped_back' => ['primary', 'Barang Diterima'],
                            'completed' => ['success', 'Selesai']
                        ];
                        $status_info = $badge[$return['status']] ?? ['secondary', ucfirst($return['status'])];
                        ?>
                        <span class="badge badge-<?= $status_info[0] ?>"><?= $status_info[1] ?></span>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($return['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="viewReturn(<?= $return['id'] ?>)" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="returnModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; overflow-y: auto;">
    <div style="background: white; border-radius: 8px; max-width: 900px; width: 90%; max-height: 90vh; overflow-y: auto; margin: 20px;">
        <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modalTitle" style="margin: 0;">Detail Pengembalian</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="modalContent" style="padding: 20px;">
            Loading...
        </div>
    </div>
</div>

<script>
function viewReturn(id) {
    document.getElementById('returnModal').style.display = 'flex';
    document.getElementById('modalContent').innerHTML = '<p style="text-align:center;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    
    fetch('return_detail.php?id=' + id)
        .then(r => r.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
        });
}

function closeModal() {
    document.getElementById('returnModal').style.display = 'none';
}

document.getElementById('returnModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include 'includes/footer.php'; ?>
