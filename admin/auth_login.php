<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// Query to find admin user
$query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Set admin session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['fullname'];
        $_SESSION['admin_role'] = $user['role'];
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;
    }
}

// Login failed
$_SESSION['login_error'] = "Email atau password salah, atau Anda bukan admin.";
header("Location: login.php");
exit;
?>
