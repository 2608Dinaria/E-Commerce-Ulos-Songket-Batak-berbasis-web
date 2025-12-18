<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$current_page = 'akun';

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
        .info-header i {
            margin-right: 10px;
        }
        .info-row {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
        }
        .btn-add-address {
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
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
                <a href="akun.php" class="active">Informasi akun</a>
                <a href="pesanan_saya.php">Pesanan Saya</a>
                <a href="permohonan_penghapusan_akun.php">Permohonan Penghapusan Akun</a>
                <a href="logout.php">Keluar</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="account-content">
            <div class="content-header">Hi (<?= htmlspecialchars($user['fullname']) ?>)</div>

            <!-- Personal Info -->
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
                <div class="info-row">
                    <span class="info-label">No Hp/Telp</span>
                    <span class="info-value"><?= htmlspecialchars($user['phone']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kata Sandi</span>
                    <span class="info-value">******</span>
                </div>
            </div>

            <!-- Saved Address -->
            <?php
            $address_query = "SELECT * FROM user_addresses WHERE user_id = '$user_id'";
            $address_result = mysqli_query($conn, $address_query);
            $address_count = mysqli_num_rows($address_result);
            ?>
            <div class="info-card">
                <div class="info-header">
                    <div><i class="fas fa-map-marker-alt"></i> Alamat tersimpan (<?= $address_count ?>)</div>
                </div>
                
                <?php if ($address_count > 0): ?>
                    <?php while ($addr = mysqli_fetch_assoc($address_result)): ?>
                        <div style="border: 1px solid #eee; padding: 20px; border-radius: 8px; position: relative; margin-bottom: 15px;">
                            <a href="edit_alamat.php?id=<?= $addr['id'] ?>" style="position: absolute; top: 20px; right: 20px; color: #ff4d4d;"><i class="fas fa-pencil-alt"></i></a>
                            
                            <div style="font-weight: bold; font-size: 1.1rem; margin-bottom: 10px;">
                                <?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?> 
                                <span style="font-weight: normal; color: #555;">(<?= htmlspecialchars($addr['phone']) ?>)</span>
                            </div>
                            
                            <div style="color: #333; line-height: 1.6;">
                                <?= htmlspecialchars($addr['address_line']) ?><br>
                                <?= htmlspecialchars($addr['district']) ?>, <?= htmlspecialchars($addr['city']) ?><br>
                                <?= htmlspecialchars($addr['province']) ?>, <?= htmlspecialchars($addr['postal_code']) ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <div style="margin-top: 20px;">
                        <a href="tambah_alamat.php" class="btn-add-address" style="background: #fff; color: #000; border: 1px solid #000;">+ Tambah Alamat Baru</a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px;">
                        <p style="color: #777; margin-bottom: 20px;">Anda tidak memiliki alamat yang disimpan</p>
                        <a href="tambah_alamat.php" class="btn-add-address">tambah alamat baru</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
