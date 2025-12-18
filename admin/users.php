<?php
include 'auth_check.php';
include '../config.php';

// Get all users (excluding admins)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query = "SELECT * FROM users WHERE (role='user' OR role IS NULL)";
if ($search) {
    $query .= " AND (fullname LIKE '%$search%' OR email LIKE '%$search%')";
}
$query .= " ORDER BY created_at DESC";
$users = mysqli_query($conn, $query);

$page_title = 'Kelola Pengguna';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Pengguna / Pelanggan</h2>
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>" style="width: 300px;">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            <?php if ($search): ?>
                <a href="users.php" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Tanggal Daftar</th>
                    <th>Total Pesanan</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users)): 
                    $user_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE user_id = {$user['id']}"))['count'];
                ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><strong><?= htmlspecialchars($user['fullname']) ?></strong></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                    <td><span class="badge badge-info"><?= $user_orders ?> pesanan</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
