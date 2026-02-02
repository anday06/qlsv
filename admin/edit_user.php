<?php
/**
 * Trang sửa thông tin người dùng
 * Chỉ admin mới có quyền truy cập
 */
$pageTitle = 'Sửa thông tin người dùng';
require_once '../includes/header.php';

// Kiểm tra quyền admin
requireAdmin();

global $pdo;

// Lấy ID user từ URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage('error', 'ID người dùng không hợp lệ.');
    redirect('admin/users.php');
}

// Lấy thông tin user
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        setFlashMessage('error', 'Không tìm thấy người dùng.');
        redirect('admin/users.php');
    }
} catch (PDOException $e) {
    error_log("Get User Error: " . $e->getMessage());
    setFlashMessage('error', 'Có lỗi xảy ra.');
    redirect('admin/users.php');
}

$errors = [];
$old = $user;

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $new_password = $_POST['new_password'] ?? '';
    
    $old['email'] = $email;
    $old['role'] = $role;
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!isValidEmail($email)) {
        $errors['email'] = 'Email không hợp lệ.';
    } else {
        // Kiểm tra email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email đã được sử dụng.';
        }
    }
    
    // Validate role
    if (!in_array($role, ['admin', 'user'])) {
        $errors['role'] = 'Vai trò không hợp lệ.';
    }
    
    // Validate password (nếu có)
    if (!empty($new_password) && strlen($new_password) < 6) {
        $errors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    }
    
    // Không cho phép thay đổi role của chính mình
    if ($id == $_SESSION['user_id'] && $role !== $user['role']) {
        $errors['role'] = 'Bạn không thể thay đổi vai trò của chính mình.';
    }
    
    // Nếu không có lỗi thì cập nhật
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Cập nhật cả mật khẩu
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$email, $role, $hashedPassword, $id]);
            } else {
                // Chỉ cập nhật email và role
                $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
                $stmt->execute([$email, $role, $id]);
            }
            
            // Cập nhật session nếu sửa chính mình
            if ($id == $_SESSION['user_id']) {
                $_SESSION['email'] = $email;
            }
            
            setFlashMessage('success', 'Cập nhật thông tin người dùng thành công!');
            redirect('admin/users.php');
        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-edit me-2"></i>Sửa thông tin người dùng
            </div>
            <div class="card-body">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger"><?= escape($errors['general']) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= escape($user['username']) ?>" disabled>
                        <small class="text-muted">Tên đăng nhập không thể thay đổi</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" name="email" value="<?= escape($old['email']) ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                        <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" 
                                id="role" name="role" <?= $id == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                            <option value="user" <?= $old['role'] === 'user' ? 'selected' : '' ?>>User (Người dùng thường)</option>
                            <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin (Quản trị viên)</option>
                        </select>
                        <?php if ($id == $_SESSION['user_id']): ?>
                            <input type="hidden" name="role" value="<?= $old['role'] ?>">
                            <small class="text-muted">Bạn không thể thay đổi vai trò của chính mình</small>
                        <?php endif; ?>
                        <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['role']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                               id="new_password" name="new_password" minlength="6">
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback"><?= escape($errors['new_password']) ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cập nhật
                        </button>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
