<?php
session_start();

// Check for login errors
$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
if ($login_error) {
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VonaTa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            background: #d32f2f;
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .login-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .login-header p {
            margin: 8px 0 0 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 40px 35px;
        }
        
        .alert {
            background: #ffebee;
            color: #c62828;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #d32f2f;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #d32f2f;
            box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
        }
        
        .btn-login {
            background: #d32f2f;
            color: white;
            padding: 14px;
            border: none;
            width: 100%;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .login-footer a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .login-footer a:hover {
            color: #d32f2f;
        }
        
        .login-footer i {
            margin-right: 5px;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../assets/img/logo.jpg" alt="VonaTa Logo">
            <h2>VonaTa Admin</h2>
            <p>Panel Administrasi</p>
        </div>
        <div class="login-body">
            <?php if ($login_error): ?>
                <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>
            <form action="auth_login.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="admin@tenun.com" autofocus>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                        <i class="fas fa-eye password-toggle" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            <div class="login-footer">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Kembali ke Website
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

