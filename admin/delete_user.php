<?php
/**
 * Xử lý xóa người dùng
 * Chỉ admin mới có quyền xóa
 * Lưu ý: Xóa user sẽ xóa cả sinh viên do user đó tạo (CASCADE)
 */
require_once '../config/config.php';

// Kiểm tra quyền admin
requireAdmin();

global $pdo;

// Lấy ID user từ URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage('error', 'ID người dùng không hợp lệ.');
    redirect('admin/users.php');
}

// Không cho phép xóa chính mình
if ($id == $_SESSION['user_id']) {
    setFlashMessage('error', 'Bạn không thể xóa tài khoản của chính mình.');
    redirect('admin/users.php');
}

try {
    // Kiểm tra user có tồn tại không
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        setFlashMessage('error', 'Không tìm thấy người dùng.');
        redirect('admin/users.php');
    }
    
    // Xóa user (sinh viên sẽ tự động xóa theo do CASCADE)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    setFlashMessage('success', 'Đã xóa người dùng "' . $user['username'] . '" thành công!');
    
} catch (PDOException $e) {
    error_log("Delete User Error: " . $e->getMessage());
    setFlashMessage('error', 'Có lỗi xảy ra khi xóa người dùng. Vui lòng thử lại.');
}

redirect('admin/users.php');
?>
