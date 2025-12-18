<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        echo "<script>alert('Password tidak cocok!'); window.location.href='register.php';</script>";
        exit;
    }

    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location.href='register.php';</script>";
        exit;
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert User
    $insert_query = "INSERT INTO users (fullname, email, phone, password) VALUES ('$fullname', '$email', '$phone', '$hashed_password')";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('Registrasi berhasil! Silahkan login.'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $insert_query . "<br>" . mysqli_error($conn);
    }
}
?>
