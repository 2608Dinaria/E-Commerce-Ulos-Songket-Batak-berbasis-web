<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: pesanan_saya.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$return_id = intval($_GET['id']);

// Get return details
$return = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, o.id as order_id, o.total_price, o.customer_name 
    FROM returns r 
    JOIN orders o ON r.order_id = o.id 
    WHERE r.id = $return_id AND r.user_id = $user_id"));

if (!$return) {
    echo "<script>alert('Data pengembalian tidak ditemukan!'); window.location='pesanan_saya.php';</script>";
    exit;
}

$page_title = 'Status Pengembalian';
include 'includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 40px auto; padding: 0 20px;">
    <a href="pesanan_saya.php" style="color: #d32f2f; text-decoration: none; margin-bottom: 20px; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Kembali ke Pesanan Saya
    </a>
    
    <h2 style="margin-bottom: 30px;">Status Pengembalian Barang</h2>
    
    <!-- Status Timeline -->
    <div class="card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Progress Pengembalian</h3>
        
        <div class="timeline">
            <div class="timeline-item <?= in_array($return['status'], ['pending', 'approved', 'rejected', 'shipped_back', 'completed']) ? 'active' : '' ?>">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <strong>Pengajuan Diajukan</strong>
                    <p><?= date('d M Y, H:i', strtotime($return['created_at'])) ?></p>
                </div>
            </div>
            
            <?php if ($return['status'] === 'rejected'): ?>
            <div class="timeline-item active" style="color: #f44336;">
                <div class="timeline-marker" style="background: #f44336;"></div>
                <div class="timeline-content">
                    <strong>Pengajuan Ditolak</strong>
                    <p><?= date('d M Y, H:i', strtotime($return['updated_at'])) ?></p>
                    <?php if ($return['admin_notes']): ?>
                        <div style="background: #ffebee; padding: 10px; border-radius: 5px; margin-top: 10px;">
                            <strong>Alasan:</strong> <?= nl2br(htmlspecialchars($return['admin_notes'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="timeline-item <?= in_array($return['status'], ['approved', 'shipped_back', 'completed']) ? 'active' : '' ?>">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <strong>Pengajuan Disetujui</strong>
                    <?php if ($return['status'] !== 'pending'): ?>
                        <p><?= date('d M Y, H:i', strtotime($return['updated_at'])) ?></p>
                        <?php if ($return['admin_notes']): ?>
                            <div style="background: #e8f5e9; padding: 10px; border-radius: 5px; margin-top: 10px;">
                                <strong>Catatan Admin:</strong> <?= nl2br(htmlspecialchars($return['admin_notes'])) ?>
                            </div>
                        <?php endif; ?>
                        <div style="background: #fff3e0; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <strong>📦 Kirim barang ke:</strong><br>
                            Gang Pendidikan No. 130, Binjai
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="timeline-item <?= in_array($return['status'], ['shipped_back', 'completed']) ? 'active' : '' ?>">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <strong>Barang Diterima Admin</strong>
                    <?php if (in_array($return['status'], ['shipped_back', 'completed'])): ?>
                        <p><?= date('d M Y, H:i', strtotime($return['updated_at'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="timeline-item <?= $return['status'] === 'completed' ? 'active' : '' ?>">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <strong>Dana Ditransfer</strong>
                    <?php if ($return['status'] === 'completed'): ?>
                        <p><?= date('d M Y, H:i', strtotime($return['updated_at'])) ?></p>
                        <?php if ($return['transfer_proof']): ?>
                            <div style="margin-top: 10px;">
                                <a href="uploads/returns/<?= $return['transfer_proof'] ?>" target="_blank" style="background: #4caf50; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block;">
                                    <i class="fas fa-receipt"></i> Lihat Bukti Transfer
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Detail Pengembalian -->
    <div class="card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Detail Pengembalian</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 12px 0; font-weight: 600; width: 200px;">Order ID</td>
                <td style="padding: 12px 0;">#<?= $return['order_id'] ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 12px 0; font-weight: 600;">Status</td>
                <td style="padding: 12px 0;">
                    <?php
                    $status_badge = [
                        'pending' => ['Menunggu Persetujuan', '#ff9800'],
                        'approved' => ['Disetujui - Kirim Barang', '#2196f3'],
                        'rejected' => ['Ditolak', '#f44336'],
                        'shipped_back' => ['Barang Diterima', '#9c27b0'],
                        'completed' => ['Selesai - Dana Ditransfer', '#4caf50']
                    ];
                    $badge = $status_badge[$return['status']] ?? ['Unknown', '#666'];
                    ?>
                    <span style="background: <?= $badge[1] ?>; color: white; padding: 5px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                        <?= $badge[0] ?>
                    </span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 12px 0; font-weight: 600;">Jumlah Pengembalian</td>
                <td style="padding: 12px 0;"><strong style="color: #4caf50; font-size: 1.1rem;">Rp <?= number_format($return['refund_amount'], 0, ',', '.') ?></strong></td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 12px 0; font-weight: 600;">Rekening Tujuan</td>
                <td style="padding: 12px 0;">
                    <?= $return['bank_name'] ?> - <?= $return['account_number'] ?><br>
                    <small style="color: #666;">a.n. <?= htmlspecialchars($return['account_holder']) ?></small>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 12px 0; font-weight: 600; vertical-align: top;">Alasan</td>
                <td style="padding: 12px 0;"><?= nl2br(htmlspecialchars($return['reason'])) ?></td>
            </tr>
            <tr>
                <td style="padding: 12px 0; font-weight: 600;">Video Unboxing</td>
                <td style="padding: 12px 0;">
                    <video width="400" controls style="border-radius: 5px; max-width: 100%;">
                        <source src="uploads/returns/<?= $return['video_unboxing'] ?>" type="video/mp4">
                        Browser Anda tidak mendukung video tag.
                    </video>
                </td>
            </tr>
        </table>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}
.timeline-item {
    position: relative;
    padding-bottom: 30px;
}
.timeline-marker {
    position: absolute;
    left: -24px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #ddd;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #ddd;
}
.timeline-item.active .timeline-marker {
    background: #4caf50;
    box-shadow: 0 0 0 2px #4caf50;
}
.timeline-content strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}
.timeline-item.active .timeline-content strong {
    color: #4caf50;
}
.timeline-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}
</style>

<?php include 'includes/footer.php'; ?>
