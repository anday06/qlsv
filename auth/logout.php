<?php
/**
 * Xử lý đăng xuất
 * Hủy session và chuyển về trang đăng nhập
 */
require_once '../config/config.php';

// Xóa tất cả biến session
$_SESSION = [];

// Xóa session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Bắt đầu session mới để set flash message
session_start();
setFlashMessage('success', 'Đăng xuất thành công!');

// Chuyển về trang đăng nhập
redirect('auth/login.php');
?>
