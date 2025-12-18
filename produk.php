<?php
include 'config.php';
$current_page = 'produk';
include 'includes/header.php';
?>

    <!-- Latest Collection -->
    <section class="latest-collection" style="background: #e0e0e0; padding-top: 50px;">
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
                        <a href="detail_produk.php?id=<?= $row['id'] ?>" class="btn-buy">Beli sekarang</a>
                    </div>
                    <?php
                }
            } else {
                // Fallback static content
                ?>
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

            </div>
        </div>
    </section>

    <!-- Main Product Grid -->
    <section class="latest-collection" style="background: #fff;">
        <div style="display: flex; gap: 30px; max-width: 1200px; margin: 0 auto; padding: 0 20px; align-items: flex-start;">
            
            <!-- Sidebar Filter -->
            <div class="filter-sidebar" style="flex: 0 0 250px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Filter Produk</h3>
                
                <form action="produk.php" method="GET">
                    <!-- Price Range -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="font-size: 1rem; margin-bottom: 10px;">Harga</h4>
                        <div style="margin-bottom: 10px;">
                            <input type="number" name="min_price" placeholder="Min" value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; margin-bottom: 5px;">
                            <input type="number" name="max_price" placeholder="Max" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd;">
                        </div>
                    </div>

                    <button type="submit" style="width: 100%; padding: 10px; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Terapkan Filter</button>
                    <?php if(isset($_GET['min_price']) || isset($_GET['max_price'])): ?>
                        <a href="produk.php" style="display: block; text-align: center; margin-top: 10px; color: red; text-decoration: underline; font-size: 0.9rem;">Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Product Grid -->
            <div class="product-grid" style="flex: 1; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php
            // Build Filter Query
            $conditions = [];
            if (isset($_GET['category']) && $_GET['category'] != '') {
                $cat_filter = mysqli_real_escape_string($conn, $_GET['category']);
                $conditions[] = "category = '$cat_filter'";
            }
            if (isset($_GET['min_price']) && $_GET['min_price'] != '') {
                $min_p = intval($_GET['min_price']);
                $conditions[] = "price >= $min_p";
            }
            if (isset($_GET['max_price']) && $_GET['max_price'] != '') {
                $max_p = intval($_GET['max_price']);
                $conditions[] = "price <= $max_p";
            }

            $sql = "SELECT * FROM products";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
            $sql .= " ORDER BY id DESC"; // Remove LIMIT for now to see results, or keep pagination if needed

            // Original logic had LIMIT 6 for pagination demo, let's keep it simple for now or preserve pagination query separately
            // Assuming no conflicting pagination parameter for now.
            // But verify if original code had pagination logic below. Use limits?
            // Original code: $query = "SELECT * FROM products LIMIT 6";
            
            // Let's stick effectively to "Show All matches" or maybe LIMIT 12 for better view
            $sql .= " LIMIT 12"; 
            
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="product-card">
                        <a href="detail_produk.php?id=<?= $row['id'] ?>">
                            <img src="assets/img/<?= rawurlencode($row['image']) ?>" alt="<?= $row['name'] ?>">
                            <h3 class="product-name"><?= $row['name'] ?></h3>
                            <span class="product-price">Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
                        </a>
                        <!-- Add Buy Button here to be consistent with top section if desired, but user didn't ask explicitly. 
                             Usually lists have it. Original list code (lines 110-116) didn't have Buy Now button in the loop, only link. -->
                    </div>
                    <?php
                }
            } else {
                echo '<p style="grid-column: 1/-1; text-align: center; padding: 20px;">Produk tidak ditemukan.</p>';
            }
            ?>
        </div>
    </div>

        <!-- Pagination -->
        <div class="pagination" style="display: flex; justify-content: center; gap: 15px; margin: 40px 0; align-items: center;">
            <a href="#" style="color: var(--primary-color); font-weight: bold; padding: 5px 10px; text-decoration: underline;">1</a>
            <a href="#" style="color: #333; font-weight: bold; padding: 5px 10px;">2</a>
            <a href="#" style="color: #333; font-weight: bold; padding: 5px 10px;">3</a>
            <a href="#" style="color: #333;"><i class="fas fa-chevron-right"></i></a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
