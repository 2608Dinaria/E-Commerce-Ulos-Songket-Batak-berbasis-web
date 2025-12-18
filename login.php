<?php
include 'config.php';
$current_page = 'login';

// Extra CSS for this page
$extra_css = '
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            background-color: #f9f9f9;
            padding: 40px 20px;
        }
        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 1px solid #eee;
        }
        .auth-tabs {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .auth-tab {
            font-size: 1.2rem;
            font-weight: bold;
            color: #aaa;
            text-decoration: none;
            padding-bottom: 5px;
        }
        .auth-tab.active {
            color: #000;
            border-bottom: 2px solid #000;
            margin-bottom: -12px; /* Pull down to overlap border */
        }
        .login-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            background: #f9f9f9;
        }
        .password-group {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            cursor: pointer;
        }
        .forgot-password {
            display: block;
            text-align: right;
            color: #0056b3;
            text-decoration: none;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        .btn-login {
            background-color: #0044cc; /* Blue color from design */
            color: #fff;
            border: none;
            padding: 12px 40px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            max-width: 200px;
        }
        .btn-login:hover {
            background-color: #003399;
        }
        .terms-text {
            margin-top: 30px;
            font-size: 0.85rem;
            color: #777;
            line-height: 1.5;
        }
    </style>
';

include 'includes/auth_header.php';
?>

    <div class="login-container">
        <div class="login-box">
            <div class="auth-tabs">
                <a href="login.php" class="auth-tab active">Masuk</a>
                <a href="register.php" class="auth-tab">Daftar</a>
            </div>

            <h2 class="login-title">Silahkan Masuk!</h2>

            <form action="auth_login.php" method="POST">
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Alamat Email" required>
                </div>
                <div class="form-group password-group">
                    <input type="password" name="password" class="form-control" placeholder="Kata sandi*" required>
                    <i class="fas fa-eye-slash password-toggle"></i>
                </div>
                
                <a href="#" class="forgot-password">Lupa Kata Sandi?</a>

                <button type="submit" class="btn-login">Masuk</button>
            </form>

            <p class="terms-text">
                Dengan membuat akun Anda atau masuk,<br>
                Anda setuju dengan Syarat dan<br>
                Ketentuan & Kebijakan Privasi kami
            </p>
        </div>
    </div>

<?php include 'includes/auth_footer.php'; ?>
