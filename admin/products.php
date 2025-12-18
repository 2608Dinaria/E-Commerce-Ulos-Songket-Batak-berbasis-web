<?php
include 'auth_check.php';
include '../config.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    header("Location: products.php");
    exit;
}

// Get products
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$query = "SELECT * FROM products WHERE 1=1";
if ($search) $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
if ($cat_filter) $query .= " AND category='$cat_filter'";
$query .= " ORDER BY id ASC";
$products = mysqli_query($conn, $query);

// Get categories
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");

$page_title = 'Kelola Produk';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Produk</h2>
        <a href="product_form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Produk</a>
    </div>
    <div class="card-body">
        <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
            <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>" style="width:300px;">
            <select name="category" class="form-control" style="width:200px;">
                <option value="">Semua Kategori</option>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?= $cat['category'] ?>" <?= $cat_filter === $cat['category'] ? 'selected' : '' ?>><?= $cat['category'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="products.php" class="btn btn-secondary btn-sm">Reset</a>
        </form>
        
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><img src="../assets/img/<?= $product['image'] ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;"></td>
                    <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($product['category']) ?></span></td>
                    <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                    <td><?= $product['stock'] ?></td>
                    <td>
                        <a href="product_form.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?delete=<?= $product['id'] ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
