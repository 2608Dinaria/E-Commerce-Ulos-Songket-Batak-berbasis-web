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

// Calculate deadlines (e.g. 24 hours from creation)
$created_at = strtotime($order['created_at']);
$deadline = $created_at + (24 * 3600);
$now = time();
$diff = $deadline - $now;

    // Default VA number (Simulated based on order ID)
    $payment_code = "876 0825 " . sprintf("%04d", $order_id) . " " . rand(1000, 9999);
    $payment_type_label = "No.rek/Virtual Account";
    $payment_instruction = "Bayar Pesanan ke Virtual Account di atas sebelum membuat pesanan kembali dengan Virtual Account agar nomor tetap sama.";
    $bank_name_display = "Bank Mandiri (Virtual Account)";
    $bank_logo_text = "mandiri";
    
    // Parse Payment Method
    $method = $order['payment_method'];
    
    if (strpos($method, 'Transfer Bank') !== false) {
        $parts = explode('-', $method);
        $bank_name = end($parts); // Get the bank name (last part)
        $bank_name = trim($bank_name);
        $bank_name_display = $bank_name . " (Virtual Account)";
        $bank_logo_text = strtolower($bank_name);
        $payment_instruction = "Bayar Pesanan ke Virtual Account $bank_name di atas.";
    } elseif (strpos($method, 'Bayar Tunai') !== false) {
        $parts = explode('-', $method);
        $retail_name = end($parts);
        $bank_name_display = trim($retail_name);
        $bank_logo_text = strtolower(str_replace(' ', '', $bank_name_display)); // e.g. agenbrilink
        $payment_type_label = "Kode Pembayaran";
        $payment_instruction = "Tunjukkan kode ini ke kasir $bank_name_display untuk melakukan pembayaran.";
    } elseif (strpos($method, 'GO-PAY') !== false) {
        $bank_name_display = "GO-PAY";
        $bank_logo_text = "gopay";
        $payment_type_label = "Nomor Ponsel / QR";
        // Just showing a dummy QR text or number for now
        $payment_code = "0812-" . rand(1000, 9999) . "-" . rand(1000, 9999); 
        $payment_instruction = "Scan QRIS atau transfer ke nomor ini via aplikasi GO-PAY.";
    } elseif (strpos($method, 'Kartu Kredit') !== false) {
        $bank_name_display = "Kartu Kredit"; // Clean name without ID
        $bank_logo_text = "VISA"; // Uppercase looks better
        $payment_type_label = "Status Pembayaran";
        $payment_code = "BERHASIL";
        $payment_instruction = "Pembayaran dengan Kartu Kredit telah berhasil diproses.";
    }

    include 'includes/header.php';
    ?>
    
    <div class="payment-container">
        <div class="back-link-container">
            <a href="pesanan_saya.php" class="back-link">Kembali</a>
        </div>
    
        <h2 class="page-title">Pembayaran</h2>
    
        <div class="payment-box">
            <!-- Top Section: Total & Timer -->
            <div class="payment-row">
                <div class="payment-label">Total Pembayaran</div>
                <div class="payment-value red-text">Rp<?= number_format($order['total_price'], 0, ',', '.') ?></div>
            </div>
    
            <div class="payment-row">
                <div class="payment-label"><?= (strpos($method, 'Kartu Kredit') !== false) ? 'Status Pembayaran' : 'Bayar dalam' ?></div>
                <div class="payment-value">
                    <?php if (strpos($method, 'Kartu Kredit') === false): ?>
                        <div class="countdown red-text" id="countdown">Loading...</div>
                        <div class="deadline-date">Jatuh tempo <?= date("d F Y, H:i", $deadline) ?></div>
                    <?php else: ?>
                        <div class="red-text" style="font-size: 1.1rem;">BERHASIL</div>
                        <div class="deadline-date"><?= date("d F Y, H:i", strtotime($order['created_at'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
    
            <div class="divider"></div>
    
            <!-- Method Section -->
            <div class="bank-section">
                <div class="bank-logo-row">
                    <div class="bank-logo-box">
                        <span style="color: #003d79; font-weight: bold; font-style: italic; font-size: 1.2rem; text-transform: capitalize;"><?= $bank_logo_text ?></span>
                    </div>
                    <span class="bank-name"><?= $bank_name_display ?></span>
                </div>
            </div>
    
            <div class="divider"></div>
            
            <!-- Code/Account Section -->
            <?php if (strpos($method, 'Kartu Kredit') === false): ?>
            <div class="va-section">
                <div class="payment-label"><?= $payment_type_label ?></div>
                <div class="va-row">
                    <div class="va-number red-text"><?= $payment_code ?></div>
                    <div class="copy-btn" onclick="copyToClipboard('<?= $payment_code ?>')">Salin</div>
                </div>
            </div>
            <?php else: ?>
             <div class="va-section">
                <div class="payment-label">Status Transaksi</div>
                <div class="va-row">
                    <div class="va-number red-text">LUNAS</div>
                </div>
            </div>
            <?php endif; ?>
    
            <!-- Instructions -->
            <div class="instructions">
                <p><?= $payment_instruction ?></p>
            </div>
    
            <!-- OK Button -->
            <div class="btn-container">
                <button onclick="window.location.href='pembayaran_berhasil.php?id=<?= $order_id ?>'" class="btn-ok">OK</button>
            </div>
        </div>
    </div>

    <script>
        // Countdown Timer
        var timeRemaining = <?= $diff > 0 ? $diff : 0 ?>;
        
        function updateTimer() {
            if (timeRemaining <= 0) {
                document.getElementById('countdown').textContent = "Expired";
                return;
            }
            
            var hours = Math.floor(timeRemaining / 3600);
            var minutes = Math.floor((timeRemaining % 3600) / 60);
            var seconds = timeRemaining % 60;
            
            var str = hours + " jam " + minutes + " menit " + seconds + " detik";
            document.getElementById('countdown').textContent = str;
            
            timeRemaining--;
        }
        
        setInterval(updateTimer, 1000);
        updateTimer(); // Initial call
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Virtual Account Berhasil Disalin!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
    
    <style>
        .payment-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            min-height: 60vh;
        }
        .back-link-container {
            text-align: right;
            margin-bottom: 20px;
        }
        .back-link {
            color: red;
            font-weight: 500;
            text-decoration: none;
        }
        .page-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: #000;
        }
        .payment-box {
            border: 1px solid #ddd;
            border-radius: 4px; /* Slightly more square as per image */
            padding: 30px;
            background: #fff;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .payment-label {
            font-size: 1rem;
            color: #333;
        }
        .payment-value {
            text-align: right;
        }
        .red-text {
            color: red;
            font-weight: bold;
        }
        .payment-row .red-text {
            font-size: 1.1rem;
        }
        .deadline-date {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .divider {
            height: 1px;
            background: #eee;
            margin: 20px 0;
        }
        .bank-logo-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .bank-logo-box {
            width: 80px;
            height: 40px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdfdfd;
            border-radius: 4px;
        }
        .bank-name {
            font-weight: 500;
            color: #333;
        }
        .va-section {
            margin-bottom: 20px;
        }
        .va-row {
            display: flex;
            justify-content: space-between; /* To push Salin to right if we want, or align left with VA */
            align-items: center;
            margin-top: 10px;
        }
        .va-number {
            font-size: 1.5rem; /* Larger font for VA */
            letter-spacing: 1px;
        }
        .copy-btn {
            color: red;
            font-weight: 500;
            cursor: pointer;
        }
        .instructions {
            font-size: 0.9rem;
            color: #555;
            margin-top: 30px;
            line-height: 1.6;
        }
        .btn-container {
            text-align: center;
            margin-top: 40px;
        }
        .btn-ok {
            padding: 10px 50px;
            background: #fff;
            border: 1px solid red;
            color: red;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-ok:hover {
            background: red;
            color: #fff;
        }
    </style>

<?php include 'includes/footer.php'; ?>
