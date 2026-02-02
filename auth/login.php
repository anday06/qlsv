<?php
/**
 * Trang đăng nhập
 * Xử lý đăng nhập người dùng
 */
require_once '../config/config.php';

// Nếu đã đăng nhập thì chuyển về dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$old = [];

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $old['username'] = $username;
    
    // Validate
    if (empty($username)) {
        $errors['username'] = 'Vui lòng nhập tên đăng nhập.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    }
    
    // Kiểm tra đăng nhập
    if (empty($errors)) {
        global $pdo;
        
        try {
            // Tìm user theo username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // Kiểm tra user tồn tại và mật khẩu đúng
            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công - tạo session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Regenerate session ID để tránh session fixation
                session_regenerate_id(true);
                
                setFlashMessage('success', 'Đăng nhập thành công! Chào mừng ' . escape($user['username']));
                redirect('dashboard.php');
            } else {
                $errors['general'] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
}

// Lấy flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Quản lý sinh viên</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .login-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .input-group .form-control:focus {
            border-left: none;
        }
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h2>Quản lý Sinh viên</h2>
            <p class="mb-0 mt-2">Đăng nhập để tiếp tục</p>
        </div>
        <div class="login-body">
            <!-- Flash Message -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <?= escape($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Error Message -->
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?= escape($errors['general']) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               id="username" name="username" value="<?= escape($old['username'] ?? '') ?>" 
                               placeholder="Nhập tên đăng nhập" required autofocus>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" name="password" placeholder="Nhập mật khẩu" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                </button>
                
                <div class="text-center">
                    <p class="mb-0">Chưa có tài khoản? <a href="register.php" class="text-decoration-none">Đăng ký ngay</a></p>
                </div>
            </form>
            
            <!-- Demo Account Info -->
            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <strong>Tài khoản demo:</strong><br>
                    Admin: admin / password<br>
                    User: user1 / password
                </small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
