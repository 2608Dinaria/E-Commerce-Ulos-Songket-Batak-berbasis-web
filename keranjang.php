<?php
include 'config.php';

// Initialize Items
$cart_items = [];
$grand_total = 0;

if (isset($_SESSION['user_id'])) {
    // Logged In: Fetch from DB
    $user_id = $_SESSION['user_id'];
    
    // Handle Delete
    if (isset($_GET['delete'])) {
        $cart_id = $_GET['delete'];
        mysqli_query($conn, "DELETE FROM cart WHERE id='$cart_id' AND user_id='$user_id'");
        header("Location: keranjang.php");
        exit;
    }

    // Handle Quantity Update
    if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
        $cart_id = $_GET['id'];
        $qty = intval($_GET['qty']);
        if ($qty > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity='$qty' WHERE id='$cart_id' AND user_id='$user_id'");
        }
        header("Location: keranjang.php");
        exit;
    }

    // Fetch Items
    $query = "SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $row['total_price'] = $row['price'] * $row['quantity'];
        $grand_total += $row['total_price'];
        $cart_items[] = $row; // row has 'id' which is cart_id
    }
} else {
    // Guest: Fetch from Session
    if (!isset($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }

    // Handle Delete (Session)
    if (isset($_GET['delete'])) {
        $item_key = $_GET['delete'];
        unset($_SESSION['guest_cart'][$item_key]);
        header("Location: keranjang.php");
        exit;
    }

    // Handle Update (Session)
    if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['key']) && isset($_GET['qty'])) {
        $item_key = $_GET['key'];
        $qty = intval($_GET['qty']);
        if ($qty > 0 && isset($_SESSION['guest_cart'][$item_key])) {
            $_SESSION['guest_cart'][$item_key]['quantity'] = $qty;
        }
        header("Location: keranjang.php");
        exit;
    }

    foreach ($_SESSION['guest_cart'] as $key => $item) {
        $pid = $item['product_id'];
        $qty = $item['quantity'];
        
        // Fetch product details for display
        $res = mysqli_query($conn, "SELECT name, price, image FROM products WHERE id='$pid'");
        if ($p = mysqli_fetch_assoc($res)) {
            $total_price = $p['price'] * $qty;
            $grand_total += $total_price;
            
            $cart_items[] = [
                'id' => $key, // Use session key as ID for guest
                'product_id' => $pid,
                'quantity' => $qty,
                'color' => $item['color'],
                'name' => $p['name'],
                'price' => $p['price'],
                'image' => $p['image'],
                'total_price' => $total_price,
                'is_guest' => true // Flag for view logic
            ];
        }
    }
}

$extra_css = '
<style>
    /* ... (CSS remains same) ... */
    body { background-color: #f9f9f9; }
    .cart-container {
        padding: 40px 80px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .cart-title {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .continue-shopping {
        color: red;
        text-decoration: underline;
        font-size: 1rem;
    }
    
    /* Item Wrapper */
    .cart-item-wrapper {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        align-items: center;
    }
    
    /* Item Card */
    .cart-item-card {
        background: #fff;
        border: 1px solid #ddd;
        padding: 20px;
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    /* Checkbox Area */
    .checkbox-col {
        width: 50px;
        display: flex;
        justify-content: center;
    }
    .custom-check {
        width: 20px;
        height: 20px;
        border: 1px solid #ccc;
        cursor: pointer;
    }
    .custom-check.checked {
        background-color: red;
        border-color: red;
    }
    
    /* Product Info */
    .product-col {
        flex: 2;
        display: flex;
        gap: 20px;
        border-right: 1px solid #eee;
        padding-right: 20px;
    }
    .product-img {
        width: 180px;
        height: 120px;
        object-fit: cover;
    }
    .product-details h3 {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .product-details p {
        color: #555;
        margin-bottom: 15px;
    }
    .price-box {
        background: #f2f2f2;
        padding: 5px 15px;
        color: red;
        font-weight: bold;
        display: inline-block;
        border-radius: 3px;
        width: 100%;
        max-width: 250px;
    }
    
    /* Quantity & Total */
    .qty-total-col {
        flex: 1.5;
        padding-left: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 20px;
    }
    .qty-row, .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .qty-label, .total-label {
        font-weight: bold;
        font-size: 1rem;
    }
    .qty-controls {
        display: flex;
        gap: 15px;
        font-weight: bold;
    }
    .qty-btn {
        cursor: pointer;
        font-size: 1.2rem;
        background: none;
        border: none;
        text-decoration: none;
        color: #000;
        display: inline-block;
        padding: 0 5px;
    }
    
    /* Delete Icon */
    .delete-col {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .delete-btn {
        font-size: 1.8rem;
        color: #333;
        cursor: pointer;
        text-decoration: none;
    }
    
    /* Footer Summary */
    .cart-footer {
        margin-top: 40px;
        border-top: 1px solid #ddd;
        padding-top: 30px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .note-section {
        flex: 1;
        max-width: 400px;
    }
    .note-label {
        display: block;
        margin-bottom: 10px;
        font-size: 1rem;
        color: #333;
    }
    .note-input {
        width: 100%;
        height: 100px;
        background: #eee;
        border: 1px solid #ccc;
        padding: 10px;
        resize: none;
    }
    
    .checkout-section {
        text-align: right;
    }
    .grand-total-row {
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    .grand-total-price {
        background: #f2f2f2;
        color: red;
        font-weight: bold;
        padding: 5px 20px;
        border-radius: 3px;
        margin-left: 10px;
        font-size: 1.3rem;
    }
    .btn-checkout {
        display: inline-block;
        padding: 12px 50px;
        background: #fff;
        border: 1px solid red;
        color: red;
        font-weight: bold;
        text-decoration: none;
        font-size: 1rem;
    }
    .btn-checkout:hover {
        background: red;
        color: #fff;
    }
</style>
';

include 'includes/header.php';
?>

<div class="cart-container">
    <div class="cart-header">
        <div class="cart-title">Keranjang Belanja</div>
        <a href="index.php" class="continue-shopping">Lanjut belanja</a>
    </div>

    <div style="margin-bottom: 20px; display:flex; align-items:center; gap:10px;">
        <div class="custom-check" id="selectAllCheck"></div> <span>Produk</span>
    </div>

<form id="cartForm" action="checkout.php" method="GET">
    <?php if (empty($cart_items)): ?>
        <p>Keranjang kosong.</p>
    <?php else: ?>
        <?php foreach ($cart_items as $index => $item): ?>
        <div class="cart-item-wrapper">
            <div class="cart-item-card">
                <div class="checkbox-col">
                    <div class="custom-check" data-id="<?= $item['id'] ?>" data-price="<?= $item['total_price'] ?>"></div>
                    <!-- Hidden checkbox for form submission -->
                    <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" id="check_<?= $item['id'] ?>" style="display:none;">
                </div>
                
                <div class="product-col">
                    <img src="assets/img/<?= rawurlencode($item['image']) ?>" class="product-img">
                    <div class="product-details">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p><?= htmlspecialchars($item['color']) ?></p>
                        <div class="price-box">Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                    </div>
                </div>
                
                <div class="qty-total-col">
                    <div class="qty-row">
                        <span class="qty-label">Jumlah</span>
                        <div class="qty-controls">
                            <?php if (isset($item['is_guest'])): ?>
                                <a href="keranjang.php?action=update&key=<?= $item['id'] ?>&qty=<?= $item['quantity'] - 1 ?>" class="qty-btn">-</a>
                                <span><?= $item['quantity'] ?></span>
                                <a href="keranjang.php?action=update&key=<?= $item['id'] ?>&qty=<?= $item['quantity'] + 1 ?>" class="qty-btn">+</a>
                            <?php else: ?>
                                <a href="keranjang.php?action=update&id=<?= $item['id'] ?>&qty=<?= $item['quantity'] - 1 ?>" class="qty-btn">-</a>
                                <span><?= $item['quantity'] ?></span>
                                <a href="keranjang.php?action=update&id=<?= $item['id'] ?>&qty=<?= $item['quantity'] + 1 ?>" class="qty-btn">+</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Total</span>
                        <div class="price-box">Rp<?= number_format($item['total_price'], 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="delete-col">
                <?php if (isset($item['is_guest'])): ?>
                    <a href="keranjang.php?delete=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                <?php else: ?>
                    <a href="keranjang.php?delete=<?= $item['id'] ?>" class="delete-btn" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="cart-footer" id="cartFooter" style="display: none;">
            <div class="note-section">
                <label class="note-label">Catatan untuk pesanan</label>
                <textarea class="note-input" placeholder="Tambahkan catatan untuk pesanan mu disini..."></textarea>
            </div>
            
            <div class="checkout-section">
                <div class="grand-total-row">
                    Total (<span id="selectedCount">0</span> produk) : 
                    <span class="grand-total-price" id="selectedTotal">Rp0</span>
                </div>
                <button type="submit" class="btn-checkout">Checkout</button>
            </div>
        </div>
    <?php endif; ?>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheck = document.getElementById('selectAllCheck');
        const itemCheckboxes = document.querySelectorAll('.custom-check[data-price]');
        const footer = document.getElementById('cartFooter');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectedTotalSpan = document.getElementById('selectedTotal');
        
        // Select all functionality
        selectAllCheck.addEventListener('click', function() {
            const isChecked = this.classList.contains('checked');
            
            if (isChecked) {
                // Uncheck all
                this.classList.remove('checked');
                itemCheckboxes.forEach(box => {
                    box.classList.remove('checked');
                    // Uncheck hidden input
                    const id = box.getAttribute('data-id');
                    document.getElementById('check_' + id).checked = false;
                });
            } else {
                // Check all
                this.classList.add('checked');
                itemCheckboxes.forEach(box => {
                    box.classList.add('checked');
                    // Check hidden input
                    const id = box.getAttribute('data-id');
                    document.getElementById('check_' + id).checked = true;
                });
            }
            updateTotals();
        });
        
        // Individual checkbox functionality
        itemCheckboxes.forEach(box => {
            box.addEventListener('click', function() {
                this.classList.toggle('checked');
                
                // Toggle hidden input
                const id = this.getAttribute('data-id');
                const hiddenCheck = document.getElementById('check_' + id);
                hiddenCheck.checked = !hiddenCheck.checked;
                
                // Update select all checkbox state
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.classList.contains('checked'));
                if (allChecked) {
                    selectAllCheck.classList.add('checked');
                } else {
                    selectAllCheck.classList.remove('checked');
                }
                
                updateTotals();
            });
        });
        
        function updateTotals() {
            const checkedBoxes = document.querySelectorAll('.custom-check[data-price].checked');
            let total = 0;
            let count = 0;
            
            checkedBoxes.forEach(box => {
                total += parseFloat(box.getAttribute('data-price'));
                count++;
            });
            
            if (count > 0) {
                footer.style.display = 'flex';
                selectedCountSpan.textContent = count;
                selectedTotalSpan.textContent = 'Rp' + total.toLocaleString('id-ID');
            } else {
                footer.style.display = 'none';
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
