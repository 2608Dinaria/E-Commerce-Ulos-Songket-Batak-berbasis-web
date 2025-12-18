<?php
// session_start(); // Already called in config.php
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch Order
$query = "SELECT * FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch Order Items
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);
$items = [];
$total_qty = 0;
while ($row = mysqli_fetch_assoc($items_result)) {
    $items[] = $row;
    $total_qty += $row['quantity'];
}

// Estimate Delivery
$shipping_method = $order['shipping_method'];
$estimation = "3-5 hari kerja"; // Default Regular
if ($shipping_method == 'Hemat') $estimation = "5-7 hari kerja";
if ($shipping_method == 'Same Day') $estimation = "Hari ini";
if ($shipping_method == 'Instant') $estimation = "1-3 jam";

// Fetch Recommendations (Random 3 products)
$rec_query = "SELECT * FROM products ORDER BY RAND() LIMIT 3";
$rec_result = mysqli_query($conn, $rec_query);
$recommendations = [];
while ($row = mysqli_fetch_assoc($rec_result)) {
    $recommendations[] = $row;
}

include 'includes/header.php';
?>

<div class="success-container">
    <div class="success-header">
        <h2>Terima kasih, pesananmu sudah kami terima!</h2>
        <p>Kami sudah menerima pesanan anda dan sedang menyiapkannya dengan penuh perhatian.</p>
    </div>

    <div class="order-summary-box">
        <h3>Ringkasan Pesanan</h3>
        
        <div class="summary-columns">
            <div class="col-left">
                <h4>Detail</h4>
                <div class="info-row"><span class="label">Nomor Pesanan</span></div>
                <div class="info-row"><span class="label">Produk</span></div>
                <div class="info-row"><span class="label">Jumlah</span></div>
                <div class="info-row"><span class="label">Total Pembayaran</span></div>
                <div class="info-row"><span class="label">Estimasi Pengiriman</span></div>
            </div>
            <div class="col-right">
                <h4>Keterangan</h4>
                <div class="info-row"><span class="value">#VT<?= date('Ymd') . $order['id'] ?></span></div>
                <div class="info-row">
                    <span class="value">
                        <?php 
                        $names = array_map(function($item) { return $item['name']; }, $items);
                        echo implode(", ", $names);
                        ?>
                    </span>
                </div>
                <div class="info-row"><span class="value"><?= $total_qty ?></span></div>
                <div class="info-row"><span class="value">Rp<?= number_format($order['total_price'], 0, ',', '.') ?></span></div>
                <div class="info-row"><span class="value"><?= $estimation ?></span></div>
            </div>
        </div>

        <div class="btn-container">
            <button onclick="window.location.href='pesanan_saya.php?status=diproses'" class="btn-ok">OK</button>
        </div>
    </div>

    <div class="recommendations">
        <h3>Kamu Mungkin Juga Suka</h3>
        <div class="product-grid">
            <?php foreach ($recommendations as $product): ?>
            <div class="product-card">
                <a href="detail_produk.php?id=<?= $product['id'] ?>">
                    <img src="assets/img/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                    <div class="product-info">
                        <div class="price">Rp<?= number_format($product['price'], 0, ',', '.') ?></div>
                        <div class="name"><?= $product['name'] ?></div>
                        <div class="desc"><?= substr($product['description'], 0, 50) ?>...</div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    .success-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
        text-align: center;
    }
    .success-header h2 {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: #000;
    }
    .success-header p {
        color: #666;
        margin-bottom: 40px;
    }
    .order-summary-box {
        background: #fff;
        border: 1px solid #ddd; /* Light border */
        padding: 40px;
        max-width: 800px;
        margin: 0 auto 60px;
        text-align: left;
    }
    .order-summary-box h3 {
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 30px;
        color: #000;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px; /* Optional, adds spacing below title */
    }
    .summary-columns {
        display: flex;
        gap: 40px; /* Space between columns */
    }
    .col-left, .col-right {
        flex: 1;
    }
    .col-left h4, .col-right h4 {
        font-size: 1rem;
        font-weight: bold;
        margin-bottom: 20px;
        color: #000;
    }
    .info-row {
        margin-bottom: 15px;
        min-height: 24px; /* Ensure alignment even if empty */
    }
    .label {
        color: #555;
    }
    .value {
        color: #000;
        font-weight: 500;
    }
    .btn-container {
        text-align: center;
        margin-top: 40px;
        border-top: 1px solid #eee; /* Separator for button */
        padding-top: 30px;
    }
    .btn-ok {
        padding: 8px 60px;
        background: #fff;
        border: 1px solid red;
        color: red;
        font-weight: bold;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .btn-ok:hover {
        background: red;
        color: white;
    }

    /* Recommendations */
    .recommendations h3 {
        font-size: 1.2rem;
        margin-bottom: 30px;
        color: #333;
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        max-width: 800px; /* Match summary box width */
        margin: 0 auto;
    }
    .product-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        text-align: center;
        transition: transform 0.2s;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .product-card a {
        text-decoration: none;
        color: inherit;
    }
    .product-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .product-info {
        padding: 15px;
    }
    .price {
        color: red;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .name {
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 5px;
        color: #000;
    }
    .desc {
        color: #666;
        font-size: 0.85rem;
    }
    @media (max-width: 768px) {
        .summary-columns {
            flex-direction: column;
            gap: 0;
        }
        .product-grid {
            grid-template-columns: repeat(1, 1fr);
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
