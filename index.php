<?php
include 'config.php';
$current_page = 'home';
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-image">
            <img src="assets/img/2.jpg" alt="Kain Tenun">
        </div>
        <div class="hero-text">
            <h2>Selamat Datang di VonaTa!</h2>
            <p>Kami percaya bahwa <strong>setiap helai benang</strong> menyimpan makna dan cerita. <strong>VonaTa</strong> menghadirkan tenun tangan asli dari pengrajin lokal Sumatera Utara, perpaduan antara tradisi dan desain modern. Dibuat dengan penuh ketelatenan, setiap kain VonaTa bukan hanya karya seni, tapi juga <strong>warisan budaya</strong> yang hidup di setiap detailnya. Temukan koleksi tenun eksklusif kami dan rasakan kehangatan tradisi dalam setiap sentuhan kainnya.</p>
            <a href="produk.php" class="btn-primary">Belanja sekarang</a>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <div class="category-card">
            <img src="assets/img/tumtuman 2.jpg" alt="Tumtuman">
            <h3>Tumtuman</h3>
        </div>
        <div class="category-card">
            <img src="assets/img/ragidup.jpg" alt="Ragidup">
            <h3>Ragidup</h3>
        </div>
        <div class="category-card">
            <img src="assets/img/puca klasik.jpg" alt="Puca Klasik">
            <h3>Puca Klasik</h3>
        </div>
    </section>

    <!-- Latest Collection -->
    <section class="latest-collection">
        <h2 class="section-title">Koleksi Terbaru!</h2>
        <p class="section-subtitle">perpaduan warna, motif, dan makna dalam setiap helai kainnya.</p>

        <div class="product-grid">
            <?php
            $query = "SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT 3";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $image_path = 'assets/img/' . $row['image'];
                    $display_image = file_exists($image_path) ? $row['image'] : 'placeholder.jpg'; // Ensure you have a placeholder.jpg or similar
                    
                    // Specific fix for ragidup1.jpg if it's missing but ragidup.jpg exists
                    if ($row['image'] == 'ragidup1.jpg' && !file_exists($image_path) && file_exists('assets/img/ragidup.jpg')) {
                        $display_image = 'ragidup.jpg';
                    }
                    ?>
                    <div class="product-card">
                        <div class="badge-new">Produk<br>Baru</div>
                        <a href="detail_produk.php?id=<?= $row['id'] ?>">
                            <img src="assets/img/<?= rawurlencode($display_image) ?>" alt="<?= $row['name'] ?>">
                            <span class="product-price">Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
                            <h3 class="product-name"><?= $row['name'] ?></h3>
                            <p class="product-desc"><?= substr($row['description'], 0, 100) ?>...</p>
                        </a>
                        <a href="beli_langsung.php?id=<?= $row['id'] ?>" class="btn-buy">Beli sekarang</a>
                    </div>
                    <?php
                }
            } else {
                // Fallback to static content if no products in DB
                ?>
                <!-- Product 1 (Static) -->
                <div class="product-card">
                    <div class="badge-new">Produk<br>Baru</div>
                    <a href="detail_produk.php?id=1">
                        <img src="assets/img/Ragi hotang.png" alt="Ulos Ragi Hotang">
                        <span class="product-price">Rp850.000</span>
                        <h3 class="product-name">Ulos Ragi Hotang</h3>
                        <p class="product-desc">Tenun dengan dominasi merah dan sentuhan emas yang melambangkan kasih dan kehangatan. Pas untuk acara adat, hadiah, atau koleksi pribadi yang bermakna.</p>
                    </a>
                    <a href="beli_langsung.php?id=1" class="btn-buy">Beli sekarang</a>
                </div>

                <!-- Product 2 (Static) -->
                <div class="product-card">
                    <div class="badge-new">Produk<br>Baru</div>
                    <a href="detail_produk.php?id=2">
                        <img src="assets/img/ragidup1.jpg" alt="Ulos Ragidup">
                        <span class="product-price">Rp1.200.000</span>
                        <h3 class="product-name">Ulos Ragidup</h3>
                        <p class="product-desc">Motif klasik yang kuat dengan warna kontras khas Batak. Simbol kehidupan dan keberkahan. Pas untuk acara adat, hadiah, atau koleksi pribadi yang bermakna.</p>
                    </a>
                    <a href="beli_langsung.php?id=2" class="btn-buy">Beli sekarang</a>
                </div>

                <!-- Product 3 (Static) -->
                <div class="product-card">
                    <div class="badge-new">Produk<br>Baru</div>
                    <a href="detail_produk.php?id=3">
                        <img src="assets/img/puca bintk.jpg" alt="Songket Puca Bintik">
                        <span class="product-price">Rp1.200.000</span>
                        <h3 class="product-name">Songket Puca Bintik</h3>
                        <p class="product-desc">Songket lembut dengan motif tradisional dan warna modern yang elegan. Bisa dipakai untuk acara resmi atau tampilan stylish bernuansa budaya.</p>
                    </a>
                    <a href="beli_langsung.php?id=3" class="btn-buy">Beli sekarang</a>
                </div>
                <?php
            }
            ?>
        </div>
    </section>

    <!-- Best Sellers -->
    <section class="best-sellers">
        <div class="best-seller-grid">
            <div class="best-seller-info">
                <h2 class="section-title" style="text-align: left;">Koleksi Terlaris</h2>
                <p style="margin-bottom: 20px;">Yang paling banyak dicari dan jadi favorit pelanggan kami!</p>
                <p style="color: #555; line-height: 1.6;">Di VonaTa, kami percaya bahwa setiap helai benang memiliki makna dan cerita. Kain-kain ini adalah pilihan pelanggan kami, tenunan tangan dengan motif yang paling diminati dan sarat filosofi budaya.</p>
            </div>
            <div class="best-seller-products">
                <?php
                // Query for best sellers (top 2 by sales quantity)
                $bs_query = "SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold
                             FROM products p
                             LEFT JOIN order_items oi ON p.id = oi.product_id
                             LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                             GROUP BY p.id
                             ORDER BY total_sold DESC, p.id DESC
                             LIMIT 2";
                $bs_result = mysqli_query($conn, $bs_query);
                
                while ($bs_row = mysqli_fetch_assoc($bs_result)) {
                    $bs_image_path = 'assets/img/' . $bs_row['image'];
                    $bs_display_image = file_exists($bs_image_path) ? $bs_row['image'] : 'placeholder.jpg';
                    
                    if ($bs_row['image'] == 'ragidup1.jpg' && !file_exists($bs_image_path) && file_exists('assets/img/ragidup.jpg')) {
                        $bs_display_image = 'ragidup.jpg';
                    }
                    ?>
                    <!-- Product Card -->
                    <div class="product-card">
                        <a href="detail_produk.php?id=<?= $bs_row['id'] ?>" style="text-decoration:none; color:inherit;">
                            <img src="assets/img/<?= rawurlencode($bs_display_image) ?>" alt="<?= htmlspecialchars($bs_row['name']) ?>">
                            <span class="product-price">Rp<?= number_format($bs_row['price'], 0, ',', '.') ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($bs_row['name']) ?></h3>
                            <p class="product-desc" style="text-align: center;"><?= htmlspecialchars(substr($bs_row['description'], 0, 50)) ?>...</p>
                        </a>
                    </div>
                <?php } ?>
                <!-- Arrow -->
                <div style="display: flex; align-items: center; justify-content: center; flex-direction: column; cursor: pointer;" onclick="window.location.href='produk.php'">
                    <i class="fas fa-chevron-right" style="font-size: 2rem;"></i>
                    <span>Lihat semua<br>Koleksi</span>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
