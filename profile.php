<?php
/**
 * Trang thông tin cá nhân
 * Cho phép xem và cập nhật thông tin người dùng
 */
$pageTitle = 'Thông tin cá nhân';
require_once 'includes/header.php';

global $pdo;

$errors = [];
$success = false;

// Lấy thông tin user hiện tại
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Profile Error: " . $e->getMessage());
    setFlashMessage('error', 'Không thể tải thông tin người dùng.');
    redirect('dashboard.php');
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_info') {
        // Cập nhật thông tin cơ bản
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'Email không hợp lệ.';
        } else {
            // Kiểm tra email đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Email đã được sử dụng.';
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$email, $_SESSION['user_id']]);
                
                $_SESSION['email'] = $email;
                $user['email'] = $email;
                
                setFlashMessage('success', 'Cập nhật thông tin thành công!');
                redirect('profile.php');
            } catch (PDOException $e) {
                error_log("Update Profile Error: " . $e->getMessage());
                $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
            }
        }
    } elseif ($action === 'change_password') {
        // Đổi mật khẩu
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password)) {
            $errors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
        }
        
        if (empty($new_password)) {
            $errors['new_password'] = 'Vui lòng nhập mật khẩu mới.';
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        }
        
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }
        
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                setFlashMessage('success', 'Đổi mật khẩu thành công!');
                redirect('profile.php');
            } catch (PDOException $e) {
                error_log("Change Password Error: " . $e->getMessage());
                $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
            }
        }
    }
}
?>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user me-2"></i>Thông tin tài khoản
            </div>
            <div class="card-body">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger"><?= escape($errors['general']) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_info">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" value="<?= escape($user['username']) ?>" disabled>
                        <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" name="email" value="<?= escape($user['email']) ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Vai trò</label>
                        <input type="text" class="form-control" 
                               value="<?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng' ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ngày tạo tài khoản</label>
                        <input type="text" class="form-control" 
                               value="<?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>" disabled>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Cập nhật thông tin
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-key me-2"></i>Đổi mật khẩu
            </div>
            <div class="card-body">
                <form method="POST" action="" id="changePasswordForm">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            Mật khẩu hiện tại <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                               id="current_password" name="current_password" required>
                        <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['current_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            Mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                               id="new_password" name="new_password" required minlength="6">
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['new_password']) ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Ít nhất 6 ký tự</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            Xác nhận mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                               id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Validate password match
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            document.getElementById('confirm_password').setCustomValidity('Mật khẩu xác nhận không khớp');
            document.getElementById('confirm_password').reportValidity();
        }
    });
    
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPass = document.getElementById('new_password').value;
        if (this.value !== newPass) {
            this.setCustomValidity('Mật khẩu xác nhận không khớp');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
