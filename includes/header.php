<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VonaTa - Setiap Helai Benang Punya Cerita</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Active Link Style */
        .nav-links a.active {
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
        }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>

<?php
// Get cart count for logged-in user
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = '$user_id'";
    $cart_result = mysqli_query($conn, $cart_query);
    if ($cart_result) {
        $cart_data = mysqli_fetch_assoc($cart_result);
        $cart_count = $cart_data['total'] ? $cart_data['total'] : 0;
    }
}
?>

    <!-- Header -->
    <header>
        <div class="top-bar">
            <div class="logo">
                <img src="assets/img/logo.jpg" alt="VonaTa Logo">
                <div class="logo-text">
                    <h1>VonaTa</h1>
                    <p>Setiap Helai Benang Punya Cerita</p>
                </div>
            </div>
            
            <nav class="nav-links">
                <a href="index.php" class="<?= ($current_page == 'home') ? 'active' : '' ?>">Home</a>
                <div class="dropdown">
                    <a href="#" onclick="toggleDropdown(event)" class="<?= ($current_page == 'kategori') ? 'active' : '' ?>">Kategori <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-content" id="kategoriDropdown">
                        <a href="kategori_adat.php">Acara adat</a>
                        <a href="kategori_seremonial.php">Acara Seremonial</a>
                        <a href="kategori_fashion.php">Fashion / Modern Wear</a>
                    </div>
                </div>
                <a href="produk.php" class="<?= ($current_page == 'produk') ? 'active' : '' ?>">Produk</a>
                <a href="about.php" class="<?= ($current_page == 'about') ? 'active' : '' ?>">About</a>
            </nav>

            <div class="search-bar">
                <input type="text" placeholder="Cari produk...">
                <button><i class="fas fa-search"></i></button>
            </div>

            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="akun.php" class="icon-btn" title="Akun Saya"><i class="fas fa-user"></i></a>
                <?php else: ?>
                    <a href="login.php" class="icon-btn" title="Masuk / Daftar"><i class="far fa-user"></i></a>
                <?php endif; ?>
                <a href="keranjang.php" class="icon-btn">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>
