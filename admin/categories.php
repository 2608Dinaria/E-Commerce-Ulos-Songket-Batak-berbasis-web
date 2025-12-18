<?php
include 'auth_check.php';
include '../config.php';

// Get all categories from categories table
$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $cat_name = $row['name'];
    // Count products using this category
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE category='$cat_name'"))['count'];
    $categories[] = ['id' => $row['id'], 'name' => $cat_name, 'count' => $count];
}

$page_title = 'Kelola Kategori';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Kategori Produk</h2>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').style.display='flex'">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Produk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= $cat['count'] ?> produk</span></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editCat('<?= htmlspecialchars($cat['name']) ?>', <?= $cat['id'] ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($cat['count'] == 0): ?>
                        <a href="category_delete.php?id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($categories)): ?>
            <p style="text-align:center; padding:40px; color:#999;">Belum ada kategori. Tambahkan kategori pertama Anda!</p>
        <?php endif; ?>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; border-radius:8px; max-width:500px; width:90%; padding:30px;">
        <h3 style="margin:0 0 20px;">Tambah Kategori Baru</h3>
        <form method="POST" action="category_add.php">
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="category_name" class="form-control" required placeholder="Contoh: Acara Adat">
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; border-radius:8px; max-width:500px; width:90%; padding:30px;">
        <h3 style="margin:0 0 20px;">Edit Kategori</h3>
        <form method="POST" action="category_edit.php">
            <input type="hidden" name="category_id" id="category_id">
            <input type="hidden" name="old_name" id="old_name">
            <div class="form-group">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="new_name" id="new_name" class="form-control" required>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Batal</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function editCat(name, id) {
    document.getElementById('category_id').value = id;
    document.getElementById('old_name').value = name;
    document.getElementById('new_name').value = name;
    document.getElementById('editModal').style.display = 'flex';
}
</script>
