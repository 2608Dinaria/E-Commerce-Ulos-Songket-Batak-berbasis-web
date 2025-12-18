<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get selected items from GET parameter
$selected_items = isset($_GET['selected_items']) ? $_GET['selected_items'] : [];

if (empty($selected_items)) {
    echo "<script>alert('Tidak ada produk yang dipilih!'); window.location='keranjang.php';</script>";
    exit;
}

// Convert array to comma-separated string for query
$selected_ids = implode(',', array_map('intval', $selected_items));

// Fetch ONLY selected items with stock information
$query = "SELECT c.*, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = '$user_id' AND c.id IN ($selected_ids)";
$result = mysqli_query($conn, $query);
$cart_items = [];
$subtotal = 0;
$stock_errors = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Validate stock availability
    if ($row['quantity'] > $row['stock']) {
        $stock_errors[] = "Stok {$row['name']} tidak mencukupi. Tersedia: {$row['stock']}, diminta: {$row['quantity']}";
    }
    
    $row['total_price'] = $row['price'] * $row['quantity'];
    $subtotal += $row['total_price'];
    $cart_items[] = $row;
}

// If there are stock errors, show them and redirect back
if (!empty($stock_errors)) {
    $error_msg = implode("\\n", $stock_errors);
    echo "<script>alert('$error_msg'); window.location.href='keranjang.php';</script>";
    exit;
}

// Check if user_addresses table exists, existing logic...
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_addresses'");
if (mysqli_num_rows($table_check) == 0) {
    $schema = file_get_contents('db_schema.sql');
    // Split and execute queries manually to verify formatting handling
    $queries = explode(';', $schema);
    foreach($queries as $query) {
        if(trim($query) != '') {
            mysqli_query($conn, $query);
        }
    }
}

// Check if user_cards table exists, if not create it
$cards_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_cards'");
if (mysqli_num_rows($cards_table_check) == 0) {
    $create_cards_table = "CREATE TABLE IF NOT EXISTS user_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        card_number VARCHAR(20) NOT NULL,
        card_holder VARCHAR(100) NOT NULL,
        expiry_date VARCHAR(7) NOT NULL,
        cvv VARCHAR(4) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $create_cards_table);
}

// Fetch ALL user addresses
$addr_query = "SELECT * FROM user_addresses WHERE user_id = '$user_id' ORDER BY id ASC";
$addr_result = mysqli_query($conn, $addr_query);
$all_addresses = [];
while ($row = mysqli_fetch_assoc($addr_result)) {
    $all_addresses[] = $row;
}

// Determine which address to use
$address = null;

// 1. Check if specific address requested via GET
if (isset($_GET['addr_id'])) {
    $req_id = intval($_GET['addr_id']);
    foreach ($all_addresses as $addr) {
        if ($addr['id'] == $req_id) {
            $address = $addr;
            break;
        }
    }
}

// 2. Fallback to the first address if not found or not specified
if (!$address && !empty($all_addresses)) {
    $address = $all_addresses[0];
}

// If still no address, redirect to add address page
if (!$address) {
    echo "<script>
        alert('Halo! Sepertinya kamu belum memiliki alamat tersimpan. Silakan tambahkan alamat pengiriman terlebih dahulu sebelum melanjutkan checkout.');
        window.location.href='tambah_alamat.php?redirect=checkout';
    </script>";
    exit;
}

// Calculate totals
$shipping_fee = 0; // Free shipping for now
$service_fee = 2000; // Fixed service fee
$total = $subtotal + $shipping_fee + $service_fee;

// Fetch user's saved cards
$cards_query = "SELECT * FROM user_cards WHERE user_id = '$user_id' ORDER BY created_at DESC";
$cards_result = mysqli_query($conn, $cards_query);
$saved_cards = [];
while ($card = mysqli_fetch_assoc($cards_result)) {
    $saved_cards[] = $card;
}

$extra_css = '
<style>
    body { background-color: #f9f9f9; }
    
    .checkout-container {
        padding: 40px 80px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .back-link {
        text-align: right;
        margin-bottom: 20px;
    }
    .back-link a {
        color: red;
        text-decoration: underline;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .checkout-title {
        text-align: center;
        font-size: 1.6rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .checkout-subtitle {
        text-align: center;
        color: #333;
        margin-bottom: 40px;
        font-size: 0.95rem;
    }
    
    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    
    /* Left Column */
    .section-border {
        border: 1px solid #ccc;
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    /* Address Card */
    .address-card {
        background: #f5f5f5;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 4px;
        position: relative;
    }
    
    .address-header {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        gap: 10px;
    }
    
    .address-icon {
        font-size: 1.2rem;
        color: #000;
    }
    
    .address-name {
        font-weight: normal;
        font-size: 0.95rem;
    }
    
    .edit-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        color: red;
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .address-details {
        margin-left: 28px; /* Align with text start */
        font-size: 0.9rem;
        color: #333;
        line-height: 1.5;
    }
    
    /* Product Item */
    .product-item {
        display: flex;
        gap: 15px;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }
    .product-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .product-item img {
        width: 120px;
        height: 120px;
        object-fit: cover;
    }
    
    .product-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .product-info h4 {
        font-size: 1rem;
        font-weight: bold;
        margin-bottom: 30px;
    }
    
    .product-info .color {
        color: #000;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .product-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .product-price {
        color: red;
        font-size: 1rem;
    }
    
    .product-qty {
        color: #000;
        font-weight: normal;
    }
    
    /* Right Column */
    .dropdown-field {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
        background: #f5f5f5;
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #666;
    }
    
    .summary-box {
        background: #f5f5f5;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 3px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }
    
    .summary-row:last-child {
        border-bottom: none;
        font-weight: bold;
        padding-top: 15px;
        margin-top: 5px;
    }
    
    .summary-label {
        color: #333;
    }
    
    .summary-value {
        color: red;
        font-weight: bold;
    }
    
    .btn-order-container {
        text-align: right;
        margin-top: 20px;
    }
    
    .btn-order {
        padding: 10px 40px;
        background: #fff;
        border: 1px solid red;
        color: red;
        font-weight: bold;
        font-size: 1rem;
        cursor: pointer;
        border-radius: 2px;
    }
    
    .btn-order:hover {
        background: red;
        color: #fff;
    }

    /* Custom Dropdown Styling */
    .custom-dropdown {
        position: relative;
        width: 100%;
        margin-bottom: 20px;
    }
    
    .dropdown-selected {
        background: #f5f5f5;
        border: 1px solid #ccc;
        padding: 12px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #666;
    }
    
    .dropdown-options {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1000;
        margin-top: 5px;
    }
    
    .dropdown-option {
        padding: 12px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #eee;
    }
    
    .dropdown-option:last-child {
        border-bottom: none;
    }
    
    .dropdown-option:hover {
        background-color: #f9f9f9;
    }
    
    .option-price {
        color: red;
        font-weight: 500;
    }
    
    .option-icon {
        color: #333;
        font-size: 1.1rem;
    }
    
    .dropdown-option:hover .option-icon {
        color: black;
    }
    
    /* Bank Grid Styling */
    .bank-grid {
        display: none;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding: 10px 15px 15px 15px;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }

    /* Mitra Grid Styling (2 columns) */
    .mitra-grid {
        display: none;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 10px 15px 15px 15px;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }
    
    /* Card Form Styling */
    .card-form {
        display: none; /* Hidden by default */
        padding: 15px;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 0.9rem;
        background: #f5f5f5;
        color: #333;
    }
    
    .form-row {
        display: flex;
        gap: 10px;
    }
    
    .form-row .form-group {
        flex: 1;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #000;
        margin-bottom: 15px;
    }
    
    .btn-add-card {
        width: 100%;
        padding: 10px;
        background: red;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: bold;
        cursor: pointer;
        font-size: 1rem;
    }
    
    .btn-add-card:hover {
        background: #cc0000;
    }

    .saved-cards-list {
        padding: 10px 15px;
        background: #fff; /* Changed to white to contrast with the card item gray */
        display: none;
        border-bottom: 1px solid #eee;
    }
    
    .saved-card-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 15px;
        background-color: #f5f5f5; /* Light gray background */
        border: 1px solid #ddd;
        border-radius: 6px; /* Rounded corners */
        margin-bottom: 10px;
        cursor: pointer;
        align-items: center;
        color: #333;
        font-weight: 500;
    }
    
    .saved-card-item:last-child {
        margin-bottom: 0;
    }
    
    .saved-card-item span:first-child {
        font-size: 1rem;
    }
    
    .saved-card-item span:last-child {
        font-size: 1rem;
        color: #555;
    }
    
    .add-new-card-link {
        color: red;
        text-decoration: underline;
        font-size: 0.9rem;
        cursor: pointer;
        display: block;
        margin-top: 15px;
        text-align: center;
    }

    .bank-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        text-align: center;
        cursor: pointer;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
        color: #333;
    }
    
    .bank-item:hover {
        border-color: #999;
    }
    
    .header-option {
         /* inherit dropdown-option styles */
    }
    
    .option-active .option-icon {
        color: red !important;
        font-weight: 900; /* make sure circle is solid or change icon class in JS */
    }
</style>
';

include 'includes/header.php';
?>

<div class="checkout-container">
    <div class="back-link">
        <a href="keranjang.php">Kembali</a>
    </div>
    
    <h1 class="checkout-title">Detail Pesanan</h1>
    <p class="checkout-subtitle">Yuk, cek lagi pesananmu sebelum dikirim.Sesuaikan jumlah produk dan pilih opsi pengiriman yang kamu inginkan.</p>
    
    <div class="checkout-grid">
        <!-- Left Column -->
        <div class="section-border">
            <!-- Shipping Address -->
            <div style="margin-bottom: 30px;">
                <div class="section-title">Alamat Pengiriman</div>
                <?php if ($address): ?>
                <div class="address-card">
                        <?php 
                        // Re-build selected items query string for the edit link
                        $query_params = [];
                        if (!empty($selected_items)) {
                            $query_params['selected_items'] = $selected_items;
                        }
                        $base_qs = http_build_query($query_params);
                        ?>
                        
                        <a href="edit_alamat.php?id=<?= $address['id'] ?>&redirect=checkout&<?= $base_qs ?>" class="edit-icon" title="Ubah Alamat"><i class="fas fa-pencil-alt"></i></a>
                        
                        <div class="address-header">
                            <i class="fas fa-map-marker-alt address-icon"></i>
                            <span class="address-name"><?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?> (<?= htmlspecialchars($address['phone']) ?>)</span>
                        </div>
                        <div class="address-details">
                            <?= htmlspecialchars($address['address_line']) ?> <?= htmlspecialchars($address['delivery_instructions'] ? ' ' . $address['delivery_instructions'] : '') ?><br>
                            <?= htmlspecialchars($address['district']) ?>, <?= htmlspecialchars($address['city']) ?><br>
                            <?= htmlspecialchars($address['province']) ?>, <?= htmlspecialchars($address['postal_code']) ?>
                        </div>

                        <?php if (count($all_addresses) > 1): ?>
                            <div style="margin-top: 15px;">
                                <button type="button" onclick="openAddressModal()" style="background: none; border: 1px solid #ccc; padding: 5px 15px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; color: #555;">Ganti Alamat</button>
                            </div>
                        <?php endif; ?>
                </div>
                <?php else: ?>
                    <!-- Should not be reached due to redirect above, but safe to keep -->
                <?php endif; ?>
            </div>
            
            <!-- Products -->
            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
            
            <div>
                <?php foreach ($cart_items as $item): ?>
                <div class="product-item">
                    <img src="assets/img/<?= rawurlencode($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div class="product-info">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <div class="color"><?= htmlspecialchars($item['color']) ?></div>
                        <div class="product-price-row">
                            <div class="product-price">Rp<?= number_format($item['price'], 0, ',', '.') ?></div>
                            <div class="product-qty">x<?= $item['quantity'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column -->
        <div>
            <!-- Shipping Method -->
            <div class="section-title">Metode Pengiriman</div>
            
            <div class="custom-dropdown" id="shipping-dropdown">
                <div class="dropdown-selected" onclick="toggleShippingDropdown('shipping-options')">
                    <span id="shipping-text">Pilih metode pengiriman...</span>
                    <!-- <i class="fas fa-chevron-down"></i> -->
                </div>
                <div class="dropdown-options" id="shipping-options">
                    <div class="dropdown-option" onclick="selectShipping('Regular', 10000)">
                        <span>Regular</span>
                        <span class="option-price">Rp10.000</span>
                    </div>
                    <div class="dropdown-option" onclick="selectShipping('Hemat', 5000)">
                        <span>Hemat</span>
                        <span class="option-price">Rp5.000</span>
                    </div>
                    <div class="dropdown-option" onclick="selectShipping('Same Day', 15000)">
                        <span>Same Day</span>
                        <span class="option-price">Rp15.000</span>
                    </div>
                    <div class="dropdown-option" onclick="selectShipping('Instant', 30000)">
                        <span>Instant</span>
                        <span class="option-price">Rp30.000</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="section-title">Metode Pembayaran</div>
            
            <div class="custom-dropdown" id="payment-dropdown">
                <div class="dropdown-selected" onclick="togglePaymentDropdown('payment-options')">
                    <span id="payment-text">Pilih metode pembayaran...</span>
                    <!-- <i class="fas fa-chevron-down"></i> -->
                </div>
                <div class="dropdown-options" id="payment-options">
                    <!-- Transfer Bank Option with Expandable Grid -->
                    <div class="payment-group">
                        <div class="dropdown-option" onclick="toggleBankGrid()" id="option-transfer">
                            <span>Transfer Bank</span>
                            <i class="far fa-circle option-icon" id="icon-transfer"></i>
                        </div>
                        <div class="bank-grid" id="bank-grid" style="display: none;">
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - SeaBank')">
                                <!-- <img src="assets/img/banks/seabank.png" alt="SeaBank"> -->
                                <span>SeaBank</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - Mandiri')">
                                <span>Mandiri</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - BRI')">
                                <span>BRI</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - BNI')">
                                <span>BNI</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - BCA')">
                                <span>BCA</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - Bank Mega')">
                                <span>Bank Mega</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - CIMB Niaga')">
                                <span>CIMB Niaga</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - BSI')">
                                <span>BSI</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Transfer Bank - Danamon')">
                                <span>Damonan</span>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-option" onclick="selectPayment('GO-PAY')">
                        <span>GO-PAY</span>
                        <i class="far fa-circle option-icon"></i>
                    </div>
                    
                    <!-- Bayar Tunai Option with Expandable Grid -->
                    <div class="payment-group">
                        <div class="dropdown-option" onclick="toggleMitraGrid()" id="option-mitra">
                            <span>Bayar Tunai di Mitra/Agen</span>
                            <i class="far fa-circle option-icon" id="icon-mitra"></i>
                        </div>
                        <div class="mitra-grid" id="mitra-grid" style="display: none;">
                            <div class="bank-item" onclick="selectPayment('Bayar Tunai - Agen BRILink')">
                                <span>Agen BRILink</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Bayar Tunai - Indomaret')">
                                <span>Indomaret</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Bayar Tunai - Alfamidi')">
                                <span>Alfamidi</span>
                            </div>
                            <div class="bank-item" onclick="selectPayment('Bayar Tunai - Alfamart')">
                                <span>Alfamart</span>
                            </div>
                        </div>
                    </div>

                    <!-- Credit/Debit Card Option with Expandable Form -->
                    <div class="payment-group">
                        <div class="dropdown-option" onclick="toggleCardSection()" id="option-cc">
                            <span>Kartu Kredit/Debit</span>
                            <i class="far fa-plus-square option-icon" id="icon-cc"></i>
                        </div>
                        
                        <!-- Saved Cards List (Hidden by default) -->
                        <div id="saved-cards-container" class="saved-cards-list">
                            <?php if (!empty($saved_cards)): ?>
                                <?php foreach ($saved_cards as $card): ?>
                                    <div class="saved-card-item" onclick="selectSavedCard('<?= $card['id'] ?>', '*<?= substr($card['card_number'], -4) ?>')">
                                        <span>Kartu Debit</span>
                                        <!-- Show asterisk and last 4 digits as per image reference -->
                                        <span>*<?= substr($card['card_number'], -4) ?></span> 
                                    </div>
                                <?php endforeach; ?>
                                <div class="add-new-card-link" onclick="showAddCardForm()">+ Tambah Kartu Baru</div>
                            <?php else: ?>
                                <!-- No cards, JS will auto-show form or we can just leave empty and toggle form directly -->
                            <?php endif; ?>
                        </div>

                        <!-- Add Card Form -->
                        <div class="card-form" id="card-form" style="display: none;">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Nomor Kartu" id="cc-number">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Masa Berlaku" id="cc-expiry">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="CVC/CVV" id="cc-cvv">
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Nama di Kartu" id="cc-name">
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="save-card-check">
                                <label for="save-card-check">Simpan kartu untuk transaksi berikutnya</label>
                            </div>
                            <button class="btn-add-card" onclick="saveNewCard()">Tambah Kartu</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Duplicate hidden input removed -->
            
            <!-- Order Summary -->
            <form action="checkout_process.php" method="POST" id="checkout-form">
                <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                <input type="hidden" name="payment_method" id="selected_payment_method">
                <input type="hidden" name="shipping_method" id="selected_shipping_method">
                
                <div class="section-title">Jumlah Pesanan</div>
                <div class="summary-box">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal (<?= count($cart_items) ?> produk)</span>
                        <span class="summary-value" data-value="<?= $subtotal ?>">Rp<?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Pengiriman</span>
                        <span class="summary-value" id="shipping-cost">Rp0</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Biaya Layanan</span>
                        <span class="summary-value" data-value="<?= $service_fee ?>">Rp<?= number_format($service_fee, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Total</span>
                        <span class="summary-value" id="grand-total">Rp<?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                </div>
                
                <?php foreach ($selected_items as $item_id): ?>
                    <input type="hidden" name="selected_items[]" value="<?= htmlspecialchars($item_id) ?>">
                <?php endforeach; ?>

                <div class="btn-order-container">
                    <button type="submit" class="btn-order">Pesan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleShippingDropdown(id) {
        var options = document.getElementById(id);
        if (options.style.display === "block") {
            options.style.display = "none";
        } else {
            options.style.display = "block";
        }
    }

    function selectShipping(name, price) {
        // Update selected text with name and price
        var formattedPrice = 'Rp' + price.toLocaleString('id-ID');
        var htmlContent = '<span>' + name + '</span><span class="option-price">' + formattedPrice + '</span>';
        
        var displayBox = document.getElementById('shipping-text');
        displayBox.innerHTML = htmlContent;
        displayBox.style.width = '100%';
        displayBox.style.display = 'flex';
        displayBox.style.justifyContent = 'space-between';
        
        // Update hidden input
        document.getElementById('selected_shipping_method').value = name;
        
        // Hide dropdown
        document.getElementById('shipping-options').style.display = "none";
        
        // Update shipping cost display
        document.getElementById('shipping-cost').textContent = formattedPrice;
        
        // Calculate new total
        var subtotal = <?= $subtotal ?>;
        var serviceFee = <?= $service_fee ?>;
        var total = subtotal + serviceFee + price;
        
        // Update total display
        document.getElementById('grand-total').textContent = 'Rp' + total.toLocaleString('id-ID');
    }

    function togglePaymentDropdown(id) {
        var options = document.getElementById(id);
        if (options.style.display === "block") {
            options.style.display = "none";
        } else {
            options.style.display = "block";
        }
    }

    function toggleBankGrid() {
        var grid = document.getElementById('bank-grid');
        var icon = document.getElementById('icon-transfer');
        var option = document.getElementById('option-transfer');
        
        if (grid.style.display === 'grid') {
            grid.style.display = 'none';
            icon.className = 'far fa-circle option-icon';
            option.classList.remove('option-active');
        } else {
            grid.style.display = 'grid';
            icon.className = 'fas fa-dot-circle option-icon'; // Change to solid dot
            icon.style.color = 'red';
            option.classList.add('option-active');
        }
    }

    function toggleMitraGrid() {
        var grid = document.getElementById('mitra-grid');
        var icon = document.getElementById('icon-mitra');
        var option = document.getElementById('option-mitra');
        
        if (grid.style.display === 'grid') {
            grid.style.display = 'none';
            icon.className = 'far fa-circle option-icon';
            option.classList.remove('option-active');
        } else {
            grid.style.display = 'grid';
            icon.className = 'fas fa-dot-circle option-icon'; // Change to solid dot
            icon.style.color = 'red';
            option.classList.add('option-active');
        }
    }

    function selectPayment(name) {
        // Update selected text
        document.getElementById('payment-text').textContent = name;
        
        // Update hidden input
        document.getElementById('selected_payment_method').value = name;
        
        // Hide dropdown
        document.getElementById('payment-options').style.display = "none";
    }

    function toggleCardSection() {
        var cardForm = document.getElementById('card-form');
        var savedList = document.getElementById('saved-cards-container');
        var icon = document.getElementById('icon-cc');
        
        // Collapse other sections
        document.getElementById('bank-grid').style.display = 'none';
        document.getElementById('mitra-grid').style.display = 'none';
        
        // Determine current state based on what's visible
        var isExpanded = (cardForm.style.display === 'block' || (savedList && savedList.style.display === 'block'));
        
        if (isExpanded) {
            // Collapse
            cardForm.style.display = 'none';
            if(savedList) savedList.style.display = 'none';
            icon.className = 'far fa-plus-square option-icon';
        } else {
            // Expand
            // Check if savedList exists/has content via CSS inspection or simple logic
            // We can check if the element exists and has children (apart from the add link)
            if (savedList && savedList.getElementsByClassName('saved-card-item').length > 0) {
                 savedList.style.display = 'block';
            } else {
                 cardForm.style.display = 'block';
            }
            icon.className = 'far fa-minus-square option-icon'; 
        }
    }

    function showAddCardForm() {
        var savedList = document.getElementById('saved-cards-container');
        if(savedList) savedList.style.display = 'none';
        document.getElementById('card-form').style.display = 'block';
    }

    function saveNewCard() {
        var num = document.getElementById('cc-number').value;
        var holder = document.getElementById('cc-name').value;
        var expiry = document.getElementById('cc-expiry').value;
        var cvv = document.getElementById('cc-cvv').value;
        // Checkbox logic - for this demo, adding card implies saving it to user list
        // as per requirement "saved so next time... do not have to add again"
        
        if (!num || !holder || !expiry || !cvv) {
            alert('Harap isi semua data kartu');
            return;
        }

        var formData = new FormData();
        formData.append('card_number', num);
        formData.append('card_holder', holder);
        formData.append('expiry_date', expiry);
        formData.append('cvv', cvv);
        
        fetch('save_card.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Kartu berhasil ditambahkan!');
                // Reload to refresh the saved cards list
                window.location.reload(); 
            } else {
                alert('Gagal menyimpan kartu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan kartu.');
        });
    }

    function selectSavedCard(id, maskedNum) {
        // Update selected text
        document.getElementById('payment-text').textContent = 'Kartu Kredit - ' + maskedNum;
        
        // Update hidden input
        // Using a format backend can parse if needed, or just the string for now
        document.getElementById('selected_payment_method').value = 'Kartu Kredit (ID: ' + id + ')';
        
        // Hide dropdown
        document.getElementById('payment-options').style.display = "none";
    }

</script>
<!-- Address Selection Modal -->
<div id="address-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #fff; width: 500px; max-height: 80vh; overflow-y: auto; border-radius: 8px; padding: 20px; position: relative;">
        <h3 style="text-align: center; margin-bottom: 20px;">Pilih Alamat Pengiriman</h3>
        <button onclick="closeAddressModal()" style="position: absolute; right: 20px; top: 20px; background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        
        <div class="address-list">
            <?php 
            // Re-build selected items query string
            $query_params = [];
            if (!empty($selected_items)) {
                $query_params['selected_items'] = $selected_items;
            }
            $base_qs = http_build_query($query_params);
            
            foreach ($all_addresses as $addr): 
                $is_selected = ($address && $address['id'] == $addr['id']);
                $bg_color = $is_selected ? '#f0f9ff' : '#fff';
                $border_color = $is_selected ? '#007bff' : '#eee';
            ?>
                <div style="border: 1px solid <?= $border_color ?>; background: <?= $bg_color ?>; padding: 15px; border-radius: 8px; margin-bottom: 10px; cursor: pointer;" 
                     onclick="window.location.href='checkout.php?<?= $base_qs ?>&addr_id=<?= $addr['id'] ?>'">
                    <div style="font-weight: bold; margin-bottom: 5px;">
                        <?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?>
                        <span style="font-weight: normal; color: #555;">(<?= htmlspecialchars($addr['phone']) ?>)</span>
                        <?php if($is_selected): ?>
                            <span style="float: right; color: #007bff; font-size: 0.8rem;">Terpilih</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.9rem; color: #333;">
                        <?= htmlspecialchars($addr['address_line']) ?><br>
                        <?= htmlspecialchars($addr['district']) ?>, <?= htmlspecialchars($addr['city']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="tambah_alamat.php?redirect=checkout" style="display: inline-block; padding: 10px 20px; background: #333; color: #fff; text-decoration: none; border-radius: 5px;">+ Tambah Alamat Baru</a>
            </div>
        </div>
    </div>
</div>

<script>
    function openAddressModal() {
        document.getElementById('address-modal').style.display = 'flex';
    }
    
    function closeAddressModal() {
        document.getElementById('address-modal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('address-modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
        // ... (preserve existing onclick logic for dropdowns if needed, or merge)
        if (!event.target.matches('.dropdown-selected') && !event.target.matches('.dropdown-selected *')) {
            var dropdowns = document.getElementsByClassName("dropdown-options");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.style.display === "block") {
                    openDropdown.style.display = "none";
                }
            }
        }
    };
    
    // ... (rest of existing script)
    // Be careful not to overwrite the window.onclick entirely if it was defined above in the generic code. 
    // The replace block below replaces lines 969-979 which was the window.onclick. 
    // So I merged the logic above.
</script>

<?php include 'includes/footer.php'; ?>
