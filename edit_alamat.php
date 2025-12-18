<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch existing address data
$query = "SELECT * FROM user_addresses WHERE id = '$address_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$address = mysqli_fetch_assoc($result);

if (!$address) {
    echo "<script>alert('Alamat tidak ditemukan!'); window.location.href='akun.php';</script>";
    exit;
}

// Get redirect parameter (Check POST first, then GET, default to akun)
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : (isset($_GET['redirect']) ? $_GET['redirect'] : 'akun');

// Capture selected_items if present
$selected_items = [];
if (isset($_REQUEST['selected_items'])) {
    $selected_items = $_REQUEST['selected_items'];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (validation code from previous context) ...
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $address_line = mysqli_real_escape_string($conn, $_POST['address_line']);
    $delivery_instructions = mysqli_real_escape_string($conn, $_POST['delivery_instructions']);

    $update_query = "UPDATE user_addresses SET 
                     first_name='$first_name', 
                     last_name='$last_name', 
                     phone='$phone', 
                     province='$province', 
                     city='$city', 
                     district='$district', 
                     postal_code='$postal_code', 
                     address_line='$address_line', 
                     delivery_instructions='$delivery_instructions' 
                     WHERE id='$address_id' AND user_id='$user_id'";

    if (mysqli_query($conn, $update_query)) {
        // Construct Redirect URL with selected_items if any
        $redirect_url = ($redirect == 'checkout') ? 'checkout.php' : 'akun.php';
        
        if ($redirect == 'checkout' && !empty($selected_items)) {
            $qs = http_build_query(['selected_items' => $selected_items]);
            $redirect_url .= '?' . $qs;
        }

        echo "<script>alert('Alamat berhasil diperbarui!'); window.location.href='$redirect_url';</script>";
    } else {
        echo "Error: " . $update_query . "<br>" . mysqli_error($conn);
    }
}

$current_page = 'edit_alamat';

// Extra CSS for this page
$extra_css = '
    <style>
        .address-container {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
            background-color: #f9f9f9;
        }
        .address-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 600px;
            position: relative;
        }
        .address-header {
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 30px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            right: 0;
            top: -10px;
            font-size: 1.5rem;
            color: #aaa;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            background: #fff;
        }
        .btn-save {
            background-color: #fff;
            color: #333;
            border: 1px solid #333;
            padding: 12px 40px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-save:hover {
            background-color: #333;
            color: #fff;
        }
    </style>
';

include 'includes/header.php';
?>

    <div class="address-container">
        <div class="address-box">
            <div class="address-header">
                Edit Alamat
                <a href="<?= $redirect == 'checkout' ? 'checkout.php' : 'akun.php' ?>" class="close-btn">&times;</a>
            </div>

            <form action="" method="POST">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <?php
                // Preserve selected_items
                if (!empty($selected_items)) {
                    foreach ($selected_items as $item_id) {
                        echo '<input type="hidden" name="selected_items[]" value="' . htmlspecialchars($item_id) . '">';
                    }
                }
                ?>
                <div class="form-group">
                    <input type="text" name="first_name" class="form-control" placeholder="Nama Depan" value="<?= htmlspecialchars($address['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="last_name" class="form-control" placeholder="Nama Belakang" value="<?= htmlspecialchars($address['last_name']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" class="form-control" placeholder="Nomor Telepon/Handphone" value="<?= htmlspecialchars($address['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="province" class="form-control" placeholder="Provinsi" value="<?= htmlspecialchars($address['province']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="city" class="form-control" placeholder="Kabupaten/Kota" value="<?= htmlspecialchars($address['city']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="district" class="form-control" placeholder="Kecamatan" value="<?= htmlspecialchars($address['district']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="postal_code" class="form-control" placeholder="Kode Pos" value="<?= htmlspecialchars($address['postal_code']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="address_line" class="form-control" placeholder="Nama jln,Gedung" value="<?= htmlspecialchars($address['address_line']) ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="delivery_instructions" class="form-control" placeholder="Instruksi Pengiriman/Blok/Unit No. (Optional)" value="<?= htmlspecialchars($address['delivery_instructions']) ?>">
                </div>

                <button type="submit" class="btn-save">Simpan</button>
            </form>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
