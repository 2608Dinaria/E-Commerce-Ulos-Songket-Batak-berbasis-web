<?php
include 'config.php';
$current_page = 'kategori';

// Extra CSS for this page
$extra_css = '
    <style>
        /* Specific styles for category page */
        .hero-adat {
            display: flex;
            padding: 50px;
            background: #f9f9f9;
            align-items: center;
            gap: 50px;
        }
        .hero-adat-text {
            flex: 1;
        }
        .hero-adat-text h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }
        .hero-adat-text p {
            color: #555;
            line-height: 1.6;
        }
        .hero-adat-image {
            flex: 1;
        }
        .hero-adat-image img {
            width: 100%;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 40px 0;
            align-items: center;
        }
        .pagination a {
            color: #333;
            font-weight: bold;
            padding: 5px 10px;
        }
        .pagination a.active {
            color: var(--primary-color);
        }
        .pagination i {
            font-size: 0.8rem;
        }
    </style>
';

include 'includes/header.php';
?>

    <!-- Hero Section Fashion -->
    <section class="hero-adat">
        <div class="hero-adat-text">
            <h2>Koleksi Pilihan Fashion/Modern Wear</h2>
            <p>Pilih ulos atau songket terbaik untuk melengkapi Fashion anda</p>
        </div>
        <div class="hero-adat-image">
            <!-- Using ragidup.jpg as placeholder for the shelf display image -->
            <img src="assets/img/modern wear.png" alt="Fashion Modern Wear">
        </div>
    </section>

    <!-- Product Grid -->
    <section class="latest-collection" style="background: #fff;">
        <div style="display: flex; gap: 30px; max-width: 1200px; margin: 0 auto; padding: 0 20px; align-items: flex-start;">
            
            <!-- Sidebar Filter -->
            <div class="filter-sidebar" style="flex: 0 0 250px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                <h3 style="margin-bottom: 20px; font-size: 1.2rem;">Filter Produk</h3>
                
                <form action="kategori_fashion.php" method="GET">
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
                        <a href="kategori_fashion.php" style="display: block; text-align: center; margin-top: 10px; color: red; text-decoration: underline; font-size: 0.9rem;">Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Product Grid -->
            <div class="product-grid" style="flex: 1; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php
            // Build Query
            // Admin screenshot shows "Fashion/modern wear", so we must include that.
            // Using LIKE to be safer with variations or exact match for known values.
            $conditions = ["(category = 'Fashion' OR category = 'Modern Wear' OR category = 'Fashion/modern wear')"];
            
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
            $sql .= " LIMIT 12"; 
            
            $result = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $image_path = 'assets/img/' . $row['image'];
                    $display_image = file_exists($image_path) ? $row['image'] : 'placeholder.jpg';
                    if ($row['image'] == 'ragidup1.jpg' && !file_exists($image_path) && file_exists('assets/img/ragidup.jpg')) {
                        $display_image = 'ragidup.jpg';
                    }
                    ?>
                    <div class="product-card">
                        <a href="detail_produk.php?id=<?= $row['id'] ?>">
                            <img src="assets/img/<?= rawurlencode($display_image) ?>" alt="<?= $row['name'] ?>">
                            <h3 class="product-name"><?= $row['name'] ?></h3>
                        </a>
                        <span class="product-price">Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
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
        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#"><i class="fas fa-chevron-right"></i></a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
