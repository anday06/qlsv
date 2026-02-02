<?php
/**
 * Header template
 * Phần đầu trang dùng chung cho tất cả các trang
 */
require_once __DIR__ . '/../config/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) . ' - ' : '' ?>Quản lý sinh viên</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
        }
        
        .sidebar .nav-link i {
            width: 25px;
        }
        
        .sidebar-brand {
            padding: 20px;
            color: white;
            text-decoration: none;
            display: block;
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand:hover {
            color: white;
        }
        
        /* Main Content */
        .main-content {
            padding: 20px;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 25px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        
        /* Tables */
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        /* Stats Cards */
        .stats-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
        }
        
        .stats-card.bg-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card.bg-success-gradient {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .stats-card.bg-warning-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-card.bg-info-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
        
        /* User dropdown */
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebarMenu">
                <div class="position-sticky">
                    <a href="<?= BASE_URL ?>/dashboard.php" class="sidebar-brand">
                        <i class="fas fa-graduation-cap me-2"></i>Quản lý SV
                    </a>
                    
                    <ul class="nav flex-column mt-3">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/students/') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/students/index.php">
                                <i class="fas fa-user-graduate"></i> Sinh viên
                            </a>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/admin/users.php">
                                <i class="fas fa-users-cog"></i> Quản lý Users
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/profile.php">
                                <i class="fas fa-user"></i> Thông tin cá nhân
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navbar -->
                <div class="top-navbar d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link d-md-none me-3" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#sidebarMenu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h5 class="mb-0"><?= isset($pageTitle) ? escape($pageTitle) : 'Dashboard' ?></h5>
                    </div>
                    
                    <div class="user-dropdown dropdown">
                        <a class="dropdown-toggle text-decoration-none text-dark" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                                </div>
                                <div class="d-none d-sm-block">
                                    <strong><?= escape($_SESSION['username']) ?></strong>
                                    <small class="d-block text-muted">
                                        <?= $_SESSION['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng' ?>
                                    </small>
                                </div>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php">
                                <i class="fas fa-user me-2"></i>Thông tin cá nhân
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Flash Messages -->
                <?php $flash = getFlashMessage(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : $flash['type']) ?> alert-dismissible fade show">
                        <?= escape($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
