<?php
include 'auth_check.php';
include '../config.php';

if (!isset($_GET['id'])) exit;

$id = intval($_GET['id']);
$return = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, o.id as order_id, o.total_price, o.customer_name, o.customer_email, o.customer_phone 
    FROM returns r 
    JOIN orders o ON r.order_id = o.id 
    WHERE r.id = $id"));

if (!$return) {
    echo "<p style='text-align:center; color:#f44336;'>Data tidak ditemukan!</p>";
    exit;
}
?>

<div style="margin-bottom: 20px;">
    <h4>Informasi Return #<?= $return['id'] ?></h4>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="border-bottom: 1px solid #f0f0f0;">
            <td style="padding: 10px 0; font-weight: 600; width: 200px;">Order ID</td>
            <td style="padding: 10px 0;">#<?= $return['order_id'] ?></td>
        </tr>
        <tr style="border-bottom: 1px solid #f0f0f0;">
            <td style="padding: 10px 0; font-weight: 600;">User</td>
            <td style="padding: 10px 0;">
                <?= htmlspecialchars($return['customer_name']) ?><br>
                <small><?= htmlspecialchars($return['customer_email']) ?> | <?= htmlspecialchars($return['customer_phone']) ?></small>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #f0f0f0;">
            <td style="padding: 10px 0; font-weight: 600;">Status</td>
            <td style="padding: 10px 0;"><strong><?= ucfirst($return['status']) ?></strong></td>
        </tr>
        <tr style="border-bottom: 1px solid #f0f0f0;">
            <td style="padding: 10px 0; font-weight: 600;">Jumlah Refund</td>
            <td style="padding: 10px 0;"><strong style="color: #4caf50; font-size: 1.2rem;">Rp <?= number_format($return['refund_amount'], 0, ',', '.') ?></strong></td>
        </tr>
        <tr style="border-bottom: 1px solid #f0f0f0;">
            <td style="padding: 10px 0; font-weight: 600;">Rekening</td>
            <td style="padding: 10px 0;">
                <?= $return['bank_name'] ?> - <?= $return['account_number'] ?><br>
                <small>a.n. <?= htmlspecialchars($return['account_holder']) ?></small>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #f0f0f0;">
            <td style="padding: 10px 0; font-weight: 600; vertical-align: top;">Alasan</td>
            <td style="padding: 10px 0;"><?= nl2br(htmlspecialchars($return['reason'])) ?></td>
        </tr>
        <tr>
            <td style="padding: 10px 0; font-weight: 600; vertical-align: top;">Video Unboxing</td>
            <td style="padding: 10px 0;">
                <video width="100%" controls style="max-width: 500px; border-radius: 5px;">
                    <source src="../uploads/returns/<?= $return['video_unboxing'] ?>" type="video/mp4">
                    Browser tidak mendukung video.
                </video>
            </td>
        </tr>
    </table>
</div>

<!-- Actions -->
<div style="border-top: 2px solid #f0f0f0; padding-top: 20px;">
    <?php if ($return['status'] === 'pending'): ?>
        <h4 style="margin-bottom: 15px;">Tindakan</h4>
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Catatan Admin (Opsional)</label>
            <textarea id="adminNotes_<?= $id ?>" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px;" placeholder="Tambahkan catatan..."></textarea>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-success" style="flex: 1;" onclick="
                    var notes = document.getElementById('adminNotes_<?= $id ?>').value;
                    if(confirm('Konfirmasi menyetujui pengembalian ini?')) {
                        window.location.href = 'return_update.php?id=<?= $id ?>&status=approved&notes=' + encodeURIComponent(notes);
                    }
                ">
                    <i class="fas fa-check"></i> Setujui Pengembalian
                </button>
                <button type="button" class="btn btn-danger" style="flex: 1;" onclick="
                    var notes = document.getElementById('adminNotes_<?= $id ?>').value;
                    if(!notes) {
                        if(!confirm('Tolak tanpa catatan?')) return;
                    }
                    if(confirm('Konfirmasi menolak pengembalian ini?')) {
                        window.location.href = 'return_update.php?id=<?= $id ?>&status=rejected&notes=' + encodeURIComponent(notes);
                    }
                ">
                    <i class="fas fa-times"></i> Tolak Pengembalian
                </button>
            </div>
        </div>
    <?php elseif ($return['status'] === 'approved'): ?>
        <div style="background: #fff3e0; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
            <strong>⏳ Menunggu user mengirim barang kembali ke:</strong><br>
            Gang Pendidikan No. 130, Binjai
        </div>
        
        <!-- Form Konfirmasi & Upload -->
        <div id="confirmSection">
            <button class="btn btn-primary" style="width: 100%;" onclick="document.getElementById('confirmSection').style.display='none'; document.getElementById('uploadSection').style.display='block';">
                <i class="fas fa-box-open"></i> Konfirmasi Barang Sudah Diterima
            </button>
        </div>
        
        <div id="uploadSection" style="display: none;">
            <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <strong style="color: #4caf50;">✓ Barang sudah diterima</strong><br>
                Upload bukti transfer untuk menyelesaikan pengembalian
            </div>
            <h4 style="margin-bottom: 15px;">Upload Bukti Transfer</h4>
            <input type="file" id="transferProofNew_<?= $id ?>" accept="image/*" required style="margin-bottom: 10px; width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <button type="button" class="btn btn-success" style="width: 100%;" onclick="
                var fileInput = document.getElementById('transferProofNew_<?= $id ?>');
                if (!fileInput.files[0]) {
                    alert('Pilih file bukti transfer!');
                    return;
                }
                if (!confirm('Konfirmasi bahwa barang sudah diterima dan akan upload bukti transfer?')) {
                    return;
                }
                var formData = new FormData();
                formData.append('transfer_proof', fileInput.files[0]);
                formData.append('id', <?= $id ?>);
                formData.append('confirm_received', '1');
                fetch('return_update.php?upload=1', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert('Barang dikonfirmasi diterima dan bukti transfer berhasil diupload!');
                        window.location.href = 'returns.php';
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(function(err) {
                    alert('Error: ' + err.message);
                });
            ">
                <i class="fas fa-upload"></i> Upload & Selesaikan Pengembalian
            </button>
        </div>
    <?php elseif ($return['status'] === 'shipped_back'): ?>
        <h4 style="margin-bottom: 15px;">Upload Bukti Transfer</h4>
        <input type="file" id="transferProofShipped_<?= $id ?>" accept="image/*" required style="margin-bottom: 10px; width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <button type="button" class="btn btn-success" style="width: 100%;" onclick="
            var fileInput = document.getElementById('transferProofShipped_<?= $id ?>');
            if (!fileInput.files[0]) {
                alert('Pilih file bukti transfer!');
                return;
            }
            if (!confirm('Upload bukti transfer dan selesaikan pengembalian?')) {
                return;
            }
            var formData = new FormData();
            formData.append('transfer_proof', fileInput.files[0]);
            formData.append('id', <?= $id ?>);
            fetch('return_update.php?upload=1', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    alert('Bukti transfer berhasil diupload!');
                    window.location.href = 'returns.php';
                } else {
                    alert('Gagal upload: ' + data.message);
                }
            })
            .catch(function(err) {
                alert('Error: ' + err.message);
            });
        ">
            <i class="fas fa-upload"></i> Upload & Selesaikan Pengembalian
        </button>
    <?php elseif ($return['status'] === 'completed'): ?>
        <div style="background: #e8f5e9; padding: 15px; border-radius: 5px;">
            <strong style="color: #4caf50;">✓ Pengembalian Selesai</strong>
            <?php if ($return['transfer_proof']): ?>
                <br>Bukti Transfer: <a href="../uploads/returns/<?= $return['transfer_proof'] ?>" target="_blank">Lihat</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function updateReturn(id, status) {
    const notesEl = document.getElementById('adminNotes');
    const notes = notesEl ? notesEl.value : '';
    
    if (status === 'rejected' && !notes) {
        if (!confirm('Tolak tanpa catatan?')) return;
    }
    
    const confirmMsg = status === 'approved' ? 'menyetujui' : status === 'rejected' ? 'menolak' : 'mengupdate';
    if (!confirm(`Konfirmasi ${confirmMsg} pengembalian ini?`)) return;
    
    // Navigate to update page and reload parent after
    const url = `return_update.php?id=${id}&status=${status}&notes=${encodeURIComponent(notes)}`;
    window.location.href = url;
}

function uploadTransfer(id) {
    const fileInput = document.getElementById('transferProof');
    if (!fileInput.files[0]) {
        alert('Pilih file bukti transfer!');
        return;
    }
    
    const formData = new FormData();
    formData.append('transfer_proof', fileInput.files[0]);
    formData.append('id', id);
    
    fetch('return_update.php?upload=1', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Bukti transfer berhasil diupload!');
            if (window.opener) {
                window.opener.location.reload();
                window.close();
            } else {
                window.location.href = 'returns.php';
            }
        } else {
            alert('Gagal upload: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

// Combined function: confirm received + upload transfer
function uploadTransferAndConfirm(id) {
    const fileInput = document.getElementById('transferProof');
    if (!fileInput.files[0]) {
        alert('Pilih file bukti transfer!');
        return;
    }
    
    if (!confirm('Konfirmasi bahwa barang sudah diterima dan akan upload bukti transfer?')) {
        return;
    }
    
    // First update status to shipped_back, then upload
    const formData = new FormData();
    formData.append('transfer_proof', fileInput.files[0]);
    formData.append('id', id);
    formData.append('confirm_received', '1'); // Flag to update status first
    
    fetch('return_update.php?upload=1', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Barang dikonfirmasi diterima dan bukti transfer berhasil diupload!');
            if (window.opener) {
                window.opener.location.reload();
                window.close();
            } else {
                window.location.href = 'returns.php';
            }
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}
</script>
