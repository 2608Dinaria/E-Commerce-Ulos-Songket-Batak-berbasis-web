<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            
            // Merge Guest Cart if exists
            if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $item) {
                    $pid = $item['product_id'];
                    $qty = $item['quantity'];
                    $color = isset($item['color']) ? $item['color'] : ''; // Default empty if not set
                    
                    // Check if item exists in DB cart
                    $check = mysqli_query($conn, "SELECT id FROM cart WHERE user_id='{$user['id']}' AND product_id='$pid' AND color='$color'");
                    if (mysqli_num_rows($check) > 0) {
                        mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty WHERE user_id='{$user['id']}' AND product_id='$pid' AND color='$color'");
                    } else {
                        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity, color) VALUES ('{$user['id']}', '$pid', '$qty', '$color')");
                    }
                }
                // Clear guest cart
                unset($_SESSION['guest_cart']);
            }

            echo "<script>alert('Login berhasil!'); window.location.href='akun.php';</script>";
        } else {
            echo "<script>alert('Password salah!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location.href='login.php';</script>";
    }
}
?>
