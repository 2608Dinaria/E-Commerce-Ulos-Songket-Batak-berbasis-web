<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin VonaTa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: #1a252f;
            border-bottom: 2px solid #d32f2f;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-logo img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            padding: 5px;
        }
        
        .sidebar-logo-text h2 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .sidebar-logo-text p {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .menu-section {
            margin-bottom: 10px;
        }
        
        .menu-label {
            padding: 15px 20px 8px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            font-weight: 600;
        }
        
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left-color: rgba(211, 47, 47, 0.5);
        }
        
        .menu-item.active {
            background: rgba(211, 47, 47, 0.15);
            color: white;
            border-left-color: #d32f2f;
        }
        
        .menu-item i {
            width: 20px;
            margin-right: 12px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #d32f2f;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #999;
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
            flex: 1;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #d32f2f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(211, 47, 47, 0.3);
        }
        
        .btn-secondary {
            background: #666;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #555;
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9rem;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table tr:hover {
            background: #fafafa;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #d32f2f;
            box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .badge-warning {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .badge-danger {
            background: #ffebee;
            color: #f44336;
        }
        
        .badge-info {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .badge-primary {
            background: #e8eaf6;
            color: #3f51b5;
        }
        
        .badge-secondary {
            background: #f5f5f5;
            color: #666;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #d32f2f;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #d32f2f;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.1;
            float: right;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="../assets/img/logo.jpg" alt="VonaTa Logo">
                <div class="sidebar-logo-text">
                    <h2>VonaTa Admin</h2>
                    <p>Panel Administrasi</p>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-label">Main Menu</div>
                <a href="dashboard.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="users.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Kelola Pengguna
                </a>
                <a href="orders.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Konfirmasi Pesanan
                </a>
                <a href="returns.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'returns.php' || basename($_SERVER['PHP_SELF']) == 'return_detail.php' ? 'active' : '' ?>">
                    <i class="fas fa-undo"></i> Kelola Pengembalian
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">Katalog</div>
                <a href="categories.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Kelola Kategori
                </a>
                <a href="products.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product_form.php' ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> Kelola Produk
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">Laporan</div>
                <a href="reports.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Laporan Penjualan
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-label">Akun</div>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <h1 class="page-title"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($admin_name ?? 'A', 0, 1)) ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?= $admin_name ?? 'Administrator' ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
