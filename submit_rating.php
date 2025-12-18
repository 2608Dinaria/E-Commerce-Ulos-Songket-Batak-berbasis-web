<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: pesanan_saya.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

// Verify order
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id AND status IN ('completed', 'returned')"));
if (!$order) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location='pesanan_saya.php';</script>";
    exit;
}

// Get order items
$items = mysqli_query($conn, "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");

// Check existing ratings
$rated_products = [];
$ratings_result = mysqli_query($conn, "SELECT product_id FROM ratings WHERE order_id = $order_id AND user_id = $user_id");
while ($r = mysqli_fetch_assoc($ratings_result)) {
    $rated_products[] = $r['product_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);
    
    if ($rating < 1 || $rating > 5) {
        echo "<script>alert('Rating harus antara 1-5!'); window.history.back();</script>";
        exit;
    }
    
    // Insert rating
    $sql = "INSERT INTO ratings (order_id, user_id, product_id, rating, review) 
            VALUES ($order_id, $user_id, $product_id, $rating, '$review')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Terima kasih atas review Anda!'); window.location='submit_rating.php?order_id=$order_id';</script>";
        exit;
    }
}

$page_title = 'Beri Rating & Review';
include 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <a href="pesanan_saya.php" style="color: #d32f2f; text-decoration: none; margin-bottom: 20px; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    
    <h2 style="margin-bottom: 30px;">Beri Rating & Review</h2>
    
    <div class="card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <p style="margin-bottom: 20px; color: #666;">Bagikan pengalaman Anda dengan produk yang sudah diterima!</p>
        
        <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <div class="product-rating-item" style="border-bottom: 1px solid #f0f0f0; padding: 20px 0; margin-bottom: 20px;">
            <div style="display: flex; gap: 20px; align-items: start;">
                <img src="assets/img/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 10px;"><?= htmlspecialchars($item['name']) ?></h4>
                    <p style="color: #666; margin: 0;">Rp <?= number_format($item['price'], 0, ',', '.') ?> x <?= $item['quantity'] ?></p>
                    
                    <?php if (in_array($item['product_id'], $rated_products)): ?>
                        <div style="margin-top: 10px; background: #e8f5e9; padding: 10px; border-radius: 5px;">
                            <i class="fas fa-check-circle" style="color: #4caf50;"></i> Sudah diberi rating
                        </div>
                    <?php else: ?>
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Rating</label>
                                <div class="star-rating">
                                    <input type="radio" name="rating" value="5" id="star5-<?= $item['product_id'] ?>" required>
                                    <label for="star5-<?= $item['product_id'] ?>">★</label>
                                    <input type="radio" name="rating" value="4" id="star4-<?= $item['product_id'] ?>">
                                    <label for="star4-<?= $item['product_id'] ?>">★</label>
                                    <input type="radio" name="rating" value="3" id="star3-<?= $item['product_id'] ?>">
                                    <label for="star3-<?= $item['product_id'] ?>">★</label>
                                    <input type="radio" name="rating" value="2" id="star2-<?= $item['product_id'] ?>">
                                    <label for="star2-<?= $item['product_id'] ?>">★</label>
                                    <input type="radio" name="rating" value="1" id="star1-<?= $item['product_id'] ?>">
                                    <label for="star1-<?= $item['product_id'] ?>">★</label>
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Review (Opsional)</label>
                                <textarea name="review" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Bagaimana pengalaman Anda dengan produk ini?"></textarea>
                            </div>
                            
                            <button type="submit" style="background: #d32f2f; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-star"></i> Kirim Rating
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php if (count($rated_products) === mysqli_num_rows(mysqli_query($conn, "SELECT COUNT(*) as count FROM order_items WHERE order_id = $order_id"))): ?>
        <div style="text-align: center; padding: 20px; background: #e8f5e9; border-radius: 5px;">
            <i class="fas fa-check-circle" style="color: #4caf50; font-size: 2rem;"></i>
            <p style="margin: 10px 0 0; font-weight: 600;">Semua produk sudah diberi rating. Terima kasih!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.star-rating {
    display: inline-flex;
    flex-direction: row-reverse;
    font-size: 2rem;
}
.star-rating input {
    display: none;
}
.star-rating label {
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}
.star-rating input:checked ~ label,
.star-rating input:checked ~ label ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}
</style>

<?php include 'includes/footer.php'; ?>
