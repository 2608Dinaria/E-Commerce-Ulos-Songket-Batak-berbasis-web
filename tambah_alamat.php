<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data for background display
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get redirect parameter (default to akun.php)
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'akun';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $address_line = mysqli_real_escape_string($conn, $_POST['address_line']);
    $delivery_instructions = mysqli_real_escape_string($conn, $_POST['delivery_instructions']);
    
    // Insert new address
    $query = "INSERT INTO user_addresses (user_id, first_name, last_name, phone, province, city, district, postal_code, address_line, delivery_instructions) 
              VALUES ('$user_id', '$first_name', '$last_name', '$phone', '$province', '$city', '$district', '$postal_code', '$address_line', '$delivery_instructions')";
    
    if (mysqli_query($conn, $query)) {
        // Redirect based on where user came from
        if ($redirect == 'checkout') {
            echo "<script>window.location.href='checkout.php';</script>";
        } else {
            echo "<script>window.location.href='akun.php';</script>";
        }
        exit;
    } else {
        $error = "Gagal menambahkan alamat: " . mysqli_error($conn);
    }
}

// Combine styles from akun.php + modal styles
$extra_css = '
<style>
    /* Background styles from akun.php */
    body { background-color: #f9f9f9; }
    .account-container {
        display: flex;
        padding: 50px 80px;
        gap: 40px;
        background: #fff;
    }
    .account-sidebar { flex: 1; max-width: 300px; }
    .sidebar-menu {
        list-style: none;
        padding: 0;
        background: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
    }
    .sidebar-menu li { border-bottom: 1px solid #ccc; }
    .sidebar-menu li:last-child { border-bottom: none; }
    .sidebar-menu a {
        display: block;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        font-weight: bold;
    }
    .sidebar-menu a.active {
        background-color: #ff4d4d;
        color: #fff;
    }
    .sidebar-header {
        padding: 15px 20px;
        font-weight: bold;
        background: #e0e0e0;
        border-bottom: 1px solid #ccc;
    }
    .account-content { flex: 3; }
    .content-header {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 30px;
    }
    .info-card {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .info-row {
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .info-label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
    
    /* MODAL OVERLAY STYLES */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .modal-box {
        background: #fff;
        width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 8px;
        padding: 0;
        position: relative;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .modal-header-row {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid #eee;
        font-weight: bold;
        font-size: 1.1rem;
        position: relative;
    }
    
    .btn-close-modal {
        position: absolute;
        right: 20px;
        top: 20px;
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: #333;
    }
    
    .modal-body {
        padding: 20px 40px;
    }
    
    .form-group { margin-bottom: 15px; }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 0.95rem;
        color: #666;
    }
    .form-control::placeholder { color: #999; }
    
    .modal-footer {
        padding: 20px 40px;
        text-align: center;
        padding-bottom: 30px;
    }
    
    .btn-save-modal {
        width: 100%;
        padding: 10px;
        background: #fff;
        color: #000;
        border: 1px solid #333;
        font-weight: 600;
        font-size: 1rem;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-save-modal:hover { background: #f9f9f9; }
</style>
';

include 'includes/header.php';
?>

<!-- Background: Authentic Account Page Layout -->
<div class="account-container">
    <!-- Sidebar -->
    <div class="account-sidebar">
        <div class="sidebar-menu">
            <div class="sidebar-header">AKUN SAYA</div>
            <a href="akun.php" class="active">Informasi akun</a>
            <a href="pesanan_saya.php">Pesanan Saya</a>
            <a href="permohonan_penghapusan_akun.php">Permohonan Penghapusan Akun</a>
            <a href="logout.php">Keluar</a>
        </div>
    </div>

    <!-- Main Content (Inactive/Blurry effect conceptually) -->
    <div class="account-content">
        <div class="content-header">Hi (<?= htmlspecialchars($user['fullname']) ?>)</div>
        <div class="info-card">
            <div class="info-header">
                <div><i class="fas fa-user"></i> Informasi Pribadi</div>
                <a href="#" style="font-size: 0.9rem; color: #555; text-decoration: underline;">Ubah</a>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat Email</span>
                <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Lengkap</span>
                <span class="info-value"><?= htmlspecialchars($user['fullname']) ?></span>
            </div>
        </div>
        <!-- Address card snippet to look real -->
        <div class="info-card">
            <div class="info-header">
                    <div><i class="fas fa-map-marker-alt"></i> Alamat tersimpan</div>
            </div>
            <div style="text-align: center; padding: 20px;">
                <p style="color: #777;">Anda tidak memiliki alamat yang disimpan</p>
                <button class="btn-add-address" style="background:#000; color:#fff; padding:10px 20px; border:none; border-radius:5px;">tambah alamat baru</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Overlay -->
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header-row">
            Tambah Alamat
            <button class="btn-close-modal" onclick="window.location.href='<?= $redirect == 'checkout' ? 'checkout.php' : 'akun.php' ?>'">✕</button>
        </div>
        
        <div class="modal-body">
            <?php if (isset($error)): ?>
                <div style="background:#fee; color:red; padding:10px; margin-bottom:10px; border-radius:4px;"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="first_name" class="form-control" placeholder="Nama Depan" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="last_name" class="form-control" placeholder="Nama Belakang" required>
                </div>
                
                <div class="form-group">
                    <input type="tel" name="phone" class="form-control" placeholder="Nomor Telepon/Handphone" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="province" class="form-control" placeholder="Provinsi" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="city" class="form-control" placeholder="Kabupaten/Kota" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="district" class="form-control" placeholder="Kecamatan" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="postal_code" class="form-control" placeholder="Kode Pos" required maxlength="10">
                </div>
                
                <div class="form-group">
                    <input type="text" name="address_line" class="form-control" placeholder="Nama jln,Gedung" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="delivery_instructions" class="form-control" placeholder="Instruksi Pengiriman/Blok/Unit No. (Optional)">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-save-modal">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
