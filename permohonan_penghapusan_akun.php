<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Account Deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $user_id = $_SESSION['user_id'];
    
    // Delete user (Cascading delete should handle addresses if set, otherwise we might need manual cleanup)
    // Assuming simple delete for now as per request
    $query = "DELETE FROM users WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        session_destroy();
        echo "<script>alert('Akun berhasil dihapus.'); window.location.href='login.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus akun: " . mysqli_error($conn) . "');</script>";
    }
}

$current_page = 'hapus_akun';

// Extra CSS
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
            background-color: red;
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
        
        .warning-box {
            background-color: #fff;
            padding: 20px;
        }
        .warning-title {
            color: #555;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .warning-title i {
            color: #ff4d4d;
            font-size: 1.2rem;
        }
        .warning-list {
            list-style-type: decimal;
            padding-left: 20px;
            color: #333;
            line-height: 1.6;
        }
        .warning-list li {
            margin-bottom: 10px;
        }
        
        .btn-continue {
            background-color: #fff;
            color: #333;
            border: 1px solid #333;
            padding: 10px 30px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            display: inline-block;
        }
        .btn-continue:hover {
            background-color: #000;
            color: #fff;
        }

        /* Modal */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 30px;
            border: 1px solid #888;
            width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .modal-header {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .modal-body {
            margin-bottom: 30px;
            color: #555;
            line-height: 1.5;
        }
        .modal-footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .btn-cancel {
            flex: 1;
            padding: 10px;
            background: #fff;
            border: 1px solid #333;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background-color: #000;
            color: #fff;
        }
        .btn-delete {
            flex: 1;
            padding: 10px;
            background: #fff;
            border: 1px solid red;
            color: red;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .btn-delete:hover {
            background-color: red;
            color: #fff;
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
                <a href="pesanan_saya.php">Pesanan Saya</a>
                <a href="permohonan_penghapusan_akun.php" class="active">Permohonan Penghapusan Akun</a>
                <a href="logout.php">Keluar</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="account-content">
            <div class="content-header">
                <i class="fas fa-user"></i> Permohonan Penghapusan Akun
            </div>

            <div class="warning-box">
                <div class="warning-title">
                    <i class="fas fa-exclamation-circle"></i> Informasi dan Pemberitahuan Penting:
                </div>
                <ol class="warning-list">
                    <li>Semua data Anda akan dihapus dan cashback yang ada di akun Anda akan hangus.</li>
                    <li>Akun Anda seharusnya tidak memiliki pembelian / pesanan yang sedang berjalan atau tertunda. Jika ada pembelian/order yang tertunda, permintaan penghapusan akun tidak akan diproses.</li>
                    <li>VONATA berhak menyimpan data transaksional untuk keperluan audit keuangan.</li>
                    <li>Menghapus akun Anda berarti Anda tidak dapat mengakses Akun Anda dan sebagainya. Anda tidak akan dapat mengakses riwayat pesanan Anda atau mencetak bukti atau pembelian / faktur.</li>
                    <li>Setelah Anda mengirimkan permintaan penghapusan akun, Anda tidak akan diizinkan untuk masuk/login ke dalam akun VONATA Anda. Mohon dapat dipastikan untuk keluar dari sesi web yang ada di ponsel atau laptop Anda.</li>
                </ol>

                <button class="btn-continue" onclick="openModal()">lanjutkan Hapus Akun</button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Hapus Akun?</div>
            <div class="modal-body">
                Setelah Anda mengonfirmasi untuk menghapus akun Anda, data akun Anda akan dihapus secara permanen dan tidak dapat diubah
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal()">Simpan Akun Saya</button>
                <form method="POST" style="flex: 1; display: flex;">
                    <button type="submit" name="confirm_delete" class="btn-delete">Hapus Akun Saya</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById("deleteModal");

        function openModal() {
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>
