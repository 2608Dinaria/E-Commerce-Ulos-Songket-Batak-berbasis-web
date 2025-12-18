<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VonaTa - Masuk / Daftar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($extra_css)) echo $extra_css; ?>
    <style>
        /* Auth Page Specific Styles */
        body {
            background-color: #f9f9f9;
        }
        .auth-header {
            text-align: center;
            padding: 20px 0;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .auth-header img {
            height: 50px;
        }
        .auth-header a {
            text-decoration: none;
            color: #333;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .auth-header h1 {
            font-size: 1.5rem;
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Simple Auth Header -->
    <header class="auth-header">
        <a href="index.php">
            <img src="assets/img/logo.jpg" alt="VonaTa Logo">
            <h1>VonaTa</h1>
        </a>
    </header>
