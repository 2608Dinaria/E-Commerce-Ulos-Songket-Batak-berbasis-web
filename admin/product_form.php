<?php
include 'auth_check.php';
include '../config.php';

$edit_mode = false;
$product = null;
$product_images = [];

if (isset($_GET['id'])) {
    $edit_mode = true;
    $id = intval($_GET['id']);
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $id"));
    
    // Get existing images
    $images_query = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $id ORDER BY is_primary DESC");
    while ($img = mysqli_fetch_assoc($images_query)) {
        $product_images[] = $img;
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

$page_title = ($edit_mode ? 'Edit' : 'Tambah') . ' Produk';
include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $edit_mode ? 'Edit' : 'Tambah' ?> Produk</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="product_save.php" enctype="multipart/form-data">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?= $product['id']?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Nama Produk *</label>
                <input type="text" name="name" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($product['name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Kategori *</label>
                <select name="category" class="form-control" required>
                    <option value="">Pilih Kategori</option>
                    <?php 
                    mysqli_data_seek($categories, 0); // Reset pointer
                    while ($cat = mysqli_fetch_assoc($categories)): 
                    ?>
                        <option value="<?= $cat['name'] ?>" <?= $edit_mode && $product['category'] === $cat['name'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Deskripsi *</label>
                <textarea name="description" class="form-control" rows="5" required><?= $edit_mode ? htmlspecialchars($product['description']) : '' ?></textarea>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label class="form-label">Harga (Rp) *</label>
                    <input type="number" name="price" class="form-control" required value="<?= $edit_mode ? $product['price'] : '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Stok *</label>
                    <input type="number" name="stock" class="form-control" required value="<?= $edit_mode ? $product['stock'] : '' ?>">
                </div>
            </div>
            
            <!-- Color Variant Images -->
            <div class="form-group">
                <label class="form-label" style="font-size: 1.1rem; font-weight: 600;">Gambar Produk dengan Variasi Warna *</label>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">Upload beberapa gambar dengan warna yang berbeda. Setidaknya 1 warna wajib diupload.</p>
                
                <div id="colorVariants">
                    <?php if (!empty($product_images)): ?>
                        <?php foreach ($product_images as $index => $img): ?>
                            <div class="color-variant-item" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 15px; background: #f9f9f9;">
                                <div style="display: grid; grid-template-columns: 150px 1fr auto; gap: 15px; align-items: start;">
                                    <div>
                                        <label style="font-weight: 600; display: block; margin-bottom: 8px;">Warna</label>
                                        <input type="text" name="colors[]" class="form-control" value="<?= htmlspecialchars($img['color']) ?>" placeholder="Contoh: Emas" required>
                                    </div>
                                    <div>
                                        <label style="font-weight: 600; display: block; margin-bottom: 8px;">Gambar (Opsional untuk update)</label>
                                        <input type="file" name="image_<?= $index ?>" class="form-control" accept="image/*">
                                        <small style="color: #666; display: block; margin-top: 5px;">Current: <?= $img['image'] ?></small>
                                        <img src="../assets/img/<?= $img['image'] ?>" style="max-width: 100px; margin-top: 10px; border-radius: 4px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <label style="display: flex; align-items: center; gap: 5px;">
                                            <input type="radio" name="primary_color" value="<?= $index ?>" <?= $img['is_primary'] ? 'checked' : '' ?>>
                                            <span style="font-size: 0.9rem;">Primary</span>
                                        </label>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.parentElement.remove()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="color-variant-item" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 15px; background: #f9f9f9;">
                            <div style="display: grid; grid-template-columns: 150px 1fr auto; gap: 15px; align-items: start;">
                                <div>
                                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Warna</label>
                                    <input type="text" name="colors[]" class="form-control" placeholder="Contoh: Emas" required>
                                </div>
                                <div>
                                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Gambar *</label>
                                    <input type="file" name="image_0" class="form-control" accept="image/*" required>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 5px;">
                                        <input type="radio" name="primary_color" value="0" checked>
                                        <span style="font-size: 0.9rem;">Primary</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="button" onclick="addColorVariant()" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                    <i class="fas fa-plus"></i> Tambah Warna
                </button>
            </div>
            
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit_mode ? 'Update' : 'Simpan' ?></button>
                <a href="products.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
let variantIndex = <?= !empty($product_images) ? count($product_images) : 1 ?>;

function addColorVariant() {
    const container = document.getElementById('colorVariants');
    const newItem = document.createElement('div');
    newItem.className = 'color-variant-item';
    newItem.style.cssText = 'border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 15px; background: #f9f9f9;';
    
    newItem.innerHTML = `
        <div style="display: grid; grid-template-columns: 150px 1fr auto; gap: 15px; align-items: start;">
            <div>
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Warna</label>
                <input type="text" name="colors[]" class="form-control" placeholder="Contoh: Biru" required>
            </div>
            <div>
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Gambar *</label>
                <input type="file" name="image_${variantIndex}" class="form-control" accept="image/*" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="radio" name="primary_color" value="${variantIndex}">
                    <span style="font-size: 0.9rem;">Primary</span>
                </label>
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.parentElement.remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newItem);
    variantIndex++;
}
</script>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
