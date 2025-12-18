<?php
include 'config.php';

// Get Product ID
$product_id = isset($_GET['id']) ? $_GET['id'] : 0;
$query = "SELECT * FROM products WHERE id = '$product_id'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

// Fallback if product not found (to prevent broken page during dev)
if (!$product) {
    echo "<div style='padding:50px; text-align:center;'>Produk tidak ditemukan. <a href='index.php'>Kembali</a></div>";
    exit;
}

// Get ratings for this product
$ratings_query = mysqli_query($conn, "SELECT r.*, u.email FROM ratings r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = $product_id ORDER BY r.created_at DESC");
$ratings = [];
$total_rating = 0;
$rating_count = 0;
while ($row = mysqli_fetch_assoc($ratings_query)) {
    $ratings[] = $row;
    $total_rating += $row['rating'];
    $rating_count++;
}
$average_rating = $rating_count > 0 ? round($total_rating / $rating_count, 1) : 0;

// Get product images with color variants
$images_query = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC");
$product_images = [];
$available_colors = [];
$primary_image = $product['image']; // Fallback to old single image

while ($img = mysqli_fetch_assoc($images_query)) {
    $product_images[$img['color']] = $img['image'];
    $available_colors[] = $img['color'];
    if ($img['is_primary']) {
        $primary_image = $img['image'];
    }
}

// Fallback: if no images in product_images table, create default with product image
if (empty($product_images) && !empty($product['image'])) {
    $product_images['Default'] = $product['image'];
    $available_colors = ['Default'];
    $primary_image = $product['image'];
}

// Handle Add to Cart
// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    $color = $_POST['color'];

    if (isset($_SESSION['user_id'])) {
        // Logged in: Add to DB
        $user_id = $_SESSION['user_id'];
        
        $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id='$user_id' AND product_id='$product_id' AND color='$color'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id='$user_id' AND product_id='$product_id' AND color='$color'");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity, color) VALUES ('$user_id', '$product_id', '$quantity', '$color')");
        }
    } else {
        // Guest: Add to Session
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        
        // Create unique key for item (product_id + color)
        $item_key = $product_id . '_' . $color;
        
        if (isset($_SESSION['guest_cart'][$item_key])) {
            $_SESSION['guest_cart'][$item_key]['quantity'] += $quantity;
        } else {
            $_SESSION['guest_cart'][$item_key] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'color' => $color
            ];
        }
    }
    echo "<script>alert('Berhasil masuk keranjang!'); window.location.href='keranjang.php';</script>";
}

// Handle Buy Now
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buy_now'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $quantity = intval($_POST['quantity']);
    $color = $_POST['color'];

    // Check existing item
    $check = mysqli_query($conn, "SELECT id FROM cart WHERE user_id='$user_id' AND product_id='$product_id' AND color='$color'");
    
    $cart_id = 0;
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $cart_id = $row['id'];
        // Update quantity to match input + existing? Or just set?
        // For 'Buy Now', usually we just ensure it's in the cart. 
        // Let's increment based on input.
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE id='$cart_id'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity, color) VALUES ('$user_id', '$product_id', '$quantity', '$color')");
        $cart_id = mysqli_insert_id($conn);
    }
    
    // Redirect to Checkout directly with this item selected
    header("Location: checkout.php?selected_items[]=" . $cart_id);
    exit;
}

$extra_css = '
<style>
    /* Breadcrumb */
    .breadcrumb {
        padding: 20px 80px;
        font-size: 0.9rem;
        color: #666;
    }
    .breadcrumb a {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    
    /* Main Layout */
    .product-container {
        display: flex;
        padding: 0 80px 60px 80px;
        gap: 60px;
    }
    .product-img-col {
        flex: 1.2;
        position: relative;
    }
    .product-img-col img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 2px;
    }
    .product-info-col {
        flex: 1;
    }
    
    /* Typography & Elements */
    .p-title {
        font-size: 1.8rem;
        font-weight: 800; /* Extra bold */
        margin-bottom: 5px;
        color: #000;
    }
    .p-subtitle {
        font-size: 0.95rem;
        color: #444;
        margin-bottom: 15px;
    }
    .p-rating {
        color: #ff5722; /* Orange-red */
        font-size: 0.9rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .p-rating span {
        color: #666;
        font-weight: normal;
    }
    
    /* Price Box */
    .p-price-box {
        background-color: #f2f2f2; /* Light grey */
        padding: 12px 20px;
        margin-bottom: 25px;
        border-radius: 4px;
        width: 100%;
    }
    .p-price {
        color: #e60000; /* Strong red */
        font-size: 1.6rem;
        font-weight: 800;
    }
    
    /* Description */
    .p-desc-highlight {
        font-weight: 700;
        font-size: 1.05rem;
        line-height: 1.5;
        color: #000;
        margin-bottom: 30px;
    }
    
    /* Options */
    .option-row {
        margin-bottom: 20px;
    }
    .opt-label {
        font-size: 0.9rem;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }
    
    /* Color Pills */
    .color-group {
        display: flex;
        gap: 15px;
    }
    .color-radio { display: none; }
    .color-pill {
        padding: 8px 35px;
        border: 1px solid #ccc;
        border-radius: 50px; /* Pill shape */
        cursor: pointer;
        font-size: 0.9rem;
        background: #fff;
        transition: all 0.2s;
    }
    /* Selected State - Red */
    .color-radio:checked + .color-pill {
        background: red;
        border-color: red;
        color: white;
        font-weight: 600;
    }
    
    /* Quantity */
    .qty-box {
        display: flex;
        border: 1px solid #aaa;
        width: fit-content;
        border-radius: 3px;
        background: #fff;
    }
    .qty-btn {
        padding: 5px 15px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 1.2rem;
        color: #333;
    }
    .qty-val {
        width: 40px;
        text-align: center;
        border: none;
        font-weight: bold;
        font-size: 1rem;
    }
    
    /* Buttons */
    .btn-group {
        display: flex;
        gap: 20px;
        margin-top: 40px;
    }
    .btn-add-cart {
        background-color: #a0a0a0; /* Darker grey */
        color: #000;
        border: none;
        padding: 12px 25px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        border-radius: 3px;
        font-size: 0.95rem;
    }
    .btn-add-cart:hover { background-color: #999; }
    .btn-buy-now {
        background-color: #fff;
        color: red;
        border: 1px solid red;
        padding: 12px 30px;
        font-weight: 700;
        cursor: pointer;
        border-radius: 3px;
        font-size: 0.95rem;
        transition: 0.3s;
    }
    .btn-buy-now:hover {
        background-color: red;
        color: #fff;
    }
    
    /* Detail List */
    .detail-section {
        padding: 0 80px 60px 80px;
        color: #333;
    }
    .detail-title {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 15px;
    }
    .detail-list {
        list-style-type: disc;
        padding-left: 20px;
        line-height: 1.8;
    }
    
    /* Similar Products */
    .similar-section {
        padding: 60px 80px;
        background: #fff; /* White bg as per image */
    }
    .sim-title {
        text-align: center;
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 40px;
    }
</style>
';

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="index.php">Semua Produk</a> &gt; <a href="#">Acara Adat</a> &gt; <span><?= htmlspecialchars($product['name']) ?></span>
</div>

<form method="POST">
    <div class="product-container">
        <!-- Left: Image -->
        <div class="product-img-col">
            <img id="productImage" src="assets/img/<?= rawurlencode($primary_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <!-- Right: Info -->
        <div class="product-info-col">
            <h1 class="p-title"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="p-subtitle">Tenunan tangan yang lembut, klasik, dan sarat makna</div>
            
            
            <div class="p-rating">
                <?php if ($rating_count > 0): ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= floor($average_rating)): ?>
                            <i class="fas fa-star"></i>
                        <?php elseif ($i - 0.5 <= $average_rating): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php else: ?>
                            <i class="far fa-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <span>(<?= $rating_count ?> ulasan pelanggan)</span>
                <?php else: ?>
                    <i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                    <span>(Belum ada ulasan)</span>
                <?php endif; ?>
            </div>

            <div class="p-price-box">
                <span class="p-price">Rp<?= number_format($product['price'], 0, ',', '.') ?></span>
            </div>

            <div style="margin: 15px 0; font-size: 0.95rem;">
                <strong>Stok Tersedia:</strong> 
                <span style="color: <?= $product['stock'] > 5 ? '#4caf50' : ($product['stock'] > 0 ? '#ff9800' : '#f44336') ?>; font-weight: 600;">
                    <?= $product['stock'] ?> unit
                </span>
            </div>

            <div class="p-desc-highlight">
                Tenun lembut dengan sentuhan warna ungu dan benang emas, menghadirkan kesan anggun dan berkelas
            </div>

            <!-- Color Options -->
            <div class="option-row">
                <label class="opt-label">Color</label>
                <div class="color-group">
                    <?php foreach ($available_colors as $index => $color): ?>
                    <label>
                        <input type="radio" name="color" value="<?= htmlspecialchars($color) ?>" class="color-radio" 
                               data-image="<?= htmlspecialchars($product_images[$color]) ?>" 
                               <?= $index === 0 ? 'checked' : '' ?>
                               onchange="changeProductImage(this.dataset.image)">
                        <span class="color-pill"><?= htmlspecialchars($color) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quantity -->
            <div class="option-row">
                <label class="opt-label">Jumlah</label>
                <div class="qty-box">
                    <button type="button" class="qty-btn" onclick="updateQty(-1)" id="btnMinus">-</button>
                    <input type="text" name="quantity" id="qtyInput" class="qty-val" value="1" readonly>
                    <button type="button" class="qty-btn" onclick="updateQty(1)" id="btnPlus">+</button>
                </div>
                <small style="margin-left: 15px; color: #666;">Maksimal: <?= $product['stock'] ?> unit</small>
            </div>

            <!-- Buttons -->
            <div class="btn-group">
                <button type="submit" name="add_to_cart" class="btn-add-cart">
                    <i class="fas fa-shopping-cart"></i> Masukkan ke Keranjang
                </button>
                <button type="submit" name="buy_now" class="btn-buy-now">Beli sekarang</button>
            </div>
        </div>
    </div>
</form>

<!-- Details Text -->
<div class="detail-section">
    <div class="detail-title">Detail Produk:</div>
    <ul class="detail-list">
        <li>Bahan: Katun premium & serat alami</li>
        <li>Ukuran: ±200 × 60 cm (bisa sedikit berbeda tiap motif)</li>
        <li>Warna: Kombinasi lembut abu, merah muda, dan hitam klasik</li>
        <li>Perawatan: Tidak perlu dicuci. Cukup dilap perlahan menggunakan tisu basah lembut jika kotor, lalu keringkan di tempat teduh. Hindari pemutih, setrika panas, dan sinar matahari langsung.</li>
        <li>Garansi: Jika produk rusak saat diterima, kami ganti baru (dengan bukti foto dalam 24 jam).</li>
</ul>
</div>

<!-- Ratings & Reviews Section -->
<div class="detail-section" style="margin-top: 30px;">
    <div class="detail-title">Rating & Ulasan (<?= $rating_count ?>)</div>
    
    <?php if ($rating_count > 0): ?>
        <!-- Average Rating Summary -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 30px;">
            <div style="text-align: center;">
                <div style="font-size: 3rem; font-weight: bold; color: #d32f2f;"><?= $average_rating ?></div>
                <div style="color: #ffc107; font-size: 1.5rem; margin: 5px 0;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= floor($average_rating)): ?>
                            ★
                        <?php elseif ($i - 0.5 <= $average_rating): ?>
                            ★
                        <?php else: ?>
                            ☆
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <div style="color: #666; font-size: 0.9rem;"><?= $rating_count ?> ulasan</div>
            </div>
            
            <!-- Rating Distribution -->
            <div style="flex: 1;">
                <?php
                $rating_dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                foreach ($ratings as $r) {
                    $rating_dist[$r['rating']]++;
                }
                for ($star = 5; $star >= 1; $star--):
                    $count = $rating_dist[$star];
                    $percentage = $rating_count > 0 ? ($count / $rating_count) * 100 : 0;
                ?>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <span style="color: #ffc107; width: 60px;"><?= $star ?> ★</span>
                    <div style="flex: 1; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; background: #ffc107; width: <?= $percentage ?>%;"></div>
                    </div>
                    <span style="color: #666; font-size: 0.85rem; width: 40px; text-align: right;"><?= $count ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- Reviews List -->
        <div style="margin-top: 20px;">
            <?php foreach ($ratings as $review): ?>
            <div style="border-bottom: 1px solid #e0e0e0; padding: 15px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <div>
                        <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                            <?= htmlspecialchars($review['email'] ?? 'User') ?>
                        </div>
                        <div style="color: #ffc107; font-size: 1.1rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $review['rating'] ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div style="color: #999; font-size: 0.85rem;">
                        <?= date('d M Y', strtotime($review['created_at'])) ?>
                    </div>
                </div>
                <?php if ($review['review']): ?>
                    <div style="color: #555; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($review['review'])) ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <i class="fas fa-star" style="font-size: 3rem; opacity: 0.3; margin-bottom: 15px;"></i>
            <p>Belum ada ulasan untuk produk ini.</p>
            <p style="font-size: 0.9rem;">Jadilah yang pertama memberikan ulasan!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Similar Products -->
<div class="similar-section">
    <h2 class="sim-title">Produk Serupa</h2>
    <div class="product-grid">
        <?php
        $sim_query = "SELECT * FROM products WHERE id != '$product_id' ORDER BY RAND() LIMIT 2";
        $sim_res = mysqli_query($conn, $sim_query);
        while($sim = mysqli_fetch_assoc($sim_res)):
            $sim_image_path = 'assets/img/' . $sim['image'];
            $sim_display_image = file_exists($sim_image_path) ? $sim['image'] : 'placeholder.jpg';
            if ($sim['image'] == 'ragidup1.jpg' && !file_exists($sim_image_path) && file_exists('assets/img/ragidup.jpg')) {
                $sim_display_image = 'ragidup.jpg';
            }
        ?>
        <div class="product-card">
            <a href="detail_produk.php?id=<?= $sim['id'] ?>">
                <img src="assets/img/<?= rawurlencode($sim_display_image) ?>" alt="<?= htmlspecialchars($sim['name']) ?>">
                <h3 class="product-name"><?= htmlspecialchars($sim['name']) ?></h3>
                <div style="color:red; font-weight:bold; margin-top:5px;">Rp<?= number_format($sim['price'], 0, ',', '.') ?></div>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
const maxStock = <?= $product['stock'] ?>;

// Change product image when color is selected
function changeProductImage(imageName) {
    document.getElementById('productImage').src = 'assets/img/' + encodeURIComponent(imageName);
}

function updateQty(change) {
    let input = document.getElementById('qtyInput');
    let btnPlus = document.getElementById('btnPlus');
    let btnMinus = document.getElementById('btnMinus');
    
    let val = parseInt(input.value) + change;
    
    // Boundaries
    if (val < 1) val = 1;
    if (val > maxStock) val = maxStock;
    
    input.value = val;
    
    // Disable/Enable buttons based on value
    btnMinus.disabled = (val <= 1);
    btnPlus.disabled = (val >= maxStock);
    
    // Visual feedback for disabled buttons
    if (btnMinus.disabled) {
        btnMinus.style.opacity = '0.3';
        btnMinus.style.cursor = 'not-allowed';
    } else {
        btnMinus.style.opacity = '1';
        btnMinus.style.cursor = 'pointer';
    }
    
    if (btnPlus.disabled) {
        btnPlus.style.opacity = '0.3';
        btnPlus.style.cursor = 'not-allowed';
    } else {
        btnPlus.style.opacity = '1';
        btnPlus.style.cursor = 'pointer';
    }
}

// Initialize on page load
window.addEventListener('DOMContentLoaded', function() {
    updateQty(0); // Initialize button states
});
</script>

<?php include 'includes/footer.php'; ?>
