<?php
/**
 * Trang đăng ký tài khoản
 * Cho phép người dùng tạo tài khoản mới
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
    // Lấy dữ liệu từ form
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Lưu dữ liệu cũ để hiển thị lại nếu có lỗi
    $old = [
        'username' => $username,
        'email' => $email,
        'role' => $role
    ];
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Tên đăng nhập không được để trống.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors['username'] = 'Tên đăng nhập phải từ 3-50 ký tự.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới.';
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!isValidEmail($email)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Mật khẩu không được để trống.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }
    
    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
    }
    
    // Validate role
    if (!in_array($role, ['admin', 'user'])) {
        $errors['role'] = 'Vai trò không hợp lệ.';
    }
    
    // Kiểm tra username và email đã tồn tại chưa
    if (empty($errors)) {
        global $pdo;
        
        // Kiểm tra username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors['username'] = 'Tên đăng nhập đã được sử dụng.';
        }
        
        // Kiểm tra email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email đã được sử dụng.';
        }
    }
    
    // Nếu không có lỗi thì tạo tài khoản
    if (empty($errors)) {
        try {
            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm user vào database
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $email, $role]);
            
            setFlashMessage('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
            redirect('auth/login.php');
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Quản lý sinh viên</title>
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
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .register-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-register:hover {
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
    <div class="register-card">
        <div class="register-header">
            <h2><i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản</h2>
            <p class="mb-0 mt-2">Tạo tài khoản mới để sử dụng hệ thống</p>
        </div>
        <div class="register-body">
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?= escape($errors['general']) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm" novalidate>
                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               id="username" name="username" value="<?= escape($old['username'] ?? '') ?>" 
                               required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Chỉ chứa chữ cái, số và dấu gạch dưới</small>
                </div>
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" name="email" value="<?= escape($old['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" name="password" required minlength="6">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Ít nhất 6 ký tự</small>
                </div>
                
                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                               id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Role -->
                <div class="mb-4">
                    <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" 
                                id="role" name="role" required>
                            <option value="user" <?= ($old['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User (Người dùng thường)</option>
                            <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (Quản trị viên)</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['role']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-register w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>Đăng ký
                </button>
                
                <div class="text-center">
                    <p class="mb-0">Đã có tài khoản? <a href="login.php" class="text-decoration-none">Đăng nhập ngay</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Client-side Validation -->
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('confirm_password').setCustomValidity('Mật khẩu xác nhận không khớp');
                document.getElementById('confirm_password').reportValidity();
            } else {
                document.getElementById('confirm_password').setCustomValidity('');
            }
        });
        
        // Real-time password match check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            if (this.value !== password) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
