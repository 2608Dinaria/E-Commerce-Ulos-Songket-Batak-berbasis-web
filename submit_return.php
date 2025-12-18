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

// Verify order belongs to user and is completed
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'completed'"));

if (!$order) {
    echo "<script>alert('Pesanan tidak ditemukan atau tidak dapat dikembalikan!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Check if return already exists
$existing_return = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM returns WHERE order_id = $order_id"));
if ($existing_return) {
    echo "<script>alert('Pengembalian untuk pesanan ini sudah diajukan!'); window.location='return_status.php?id={$existing_return['id']}';</script>";
    exit;
}

// Calculate refund amount from actual order items subtotal (without shipping & service fee)
// Get all order items and calculate subtotal
$items_query = mysqli_query($conn, "SELECT quantity, price FROM order_items WHERE order_id = $order_id");
$subtotal = 0;
while ($item = mysqli_fetch_assoc($items_query)) {
    $subtotal += $item['quantity'] * $item['price'];
}
$refund_amount = $subtotal; // Only return product subtotal, not shipping & service fee

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $account_holder = mysqli_real_escape_string($conn, $_POST['account_holder']);
    
    // Handle video upload
    $video_name = '';
    if (isset($_FILES['video_unboxing']) && $_FILES['video_unboxing']['error'] === 0) {
        $allowed_types = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'];
        $file_type = $_FILES['video_unboxing']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['video_unboxing']['name'], PATHINFO_EXTENSION);
            $video_name = 'unboxing_' . $order_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['video_unboxing']['tmp_name'], 'uploads/returns/' . $video_name);
        } else {
            echo "<script>alert('Format video tidak didukung! Gunakan MP4, MPEG, MOV, atau AVI.'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Video unboxing wajib diupload!'); window.history.back();</script>";
        exit;
    }
    
    // Insert return request
    $sql = "INSERT INTO returns (order_id, user_id, reason, video_unboxing, bank_name, account_number, account_holder, refund_amount, status) 
            VALUES ($order_id, $user_id, '$reason', '$video_name', '$bank_name', '$account_number', '$account_holder', $refund_amount, 'pending')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pengajuan pengembalian berhasil! Silakan tunggu persetujuan admin.'); window.location='pesanan_saya.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal mengajukan pengembalian!'); window.history.back();</script>";
        exit;
    }
}

$page_title = 'Ajukan Pengembalian';
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <h2 style="margin-bottom: 20px;">Ajukan Pengembalian Barang</h2>
    
    <div class="alert" style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <strong>⚠️ Syarat Pengembalian:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li>Video unboxing <strong>WAJIB</strong> diupload</li>
            <li>Barang dikembalikan ke: <strong>Gang Pendidikan No. 130, Binjai</strong></li>
            <li>Pengembalian dana: <strong>Rp <?= number_format($refund_amount, 0, ',', '.') ?></strong> (tidak termasuk ongkir & layanan)</li>
        </ul>
    </div>
    
    <div class="card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Alasan Pengembalian *</label>
                <textarea name="reason" class="form-control" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Jelaskan alasan pengembalian barang..."></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Video Unboxing * (Maksimal 50MB)</label>
                <input type="file" name="video_unboxing" accept="video/*" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666; display: block; margin-top: 5px;">Format: MP4, MPEG, MOV, AVI</small>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
            
            <h4 style="margin-bottom: 15px;">Informasi Rekening untuk Pengembalian Dana</h4>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Nama Bank *</label>
                <select name="bank_name" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Pilih Bank</option>
                    <option value="BCA">BCA</option>
                    <option value="Mandiri">Mandiri</option>
                    <option value="BRI">BRI</option>
                    <option value="BNI">BNI</option>
                    <option value="BSI">BSI</option>
                    <option value="CIMB Niaga">CIMB Niaga</option>
                    <option value="Danamon">Danamon</option>
                    <option value="SeaBank">SeaBank</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Nomor Rekening *</label>
                <input type="text" name="account_number" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Contoh: 1234567890">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Nama Pemilik Rekening *</label>
                <input type="text" name="account_holder" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Sesuai dengan nama di rekening">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; background: #d32f2f; color: white; border: none; border-radius: 5px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-paper-plane"></i> Ajukan Pengembalian
                </button>
                <a href="pesanan_saya.php" class="btn btn-secondary" style="flex: 1; padding: 12px; background: #666; color: white; border: none; border-radius: 5px; font-weight: 600; text-align: center; text-decoration: none; display: block;">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Create uploads directory if not exists
if (!file_exists('uploads/returns')) {
    mkdir('uploads/returns', 0777, true);
}

include 'includes/footer.php';
?>
