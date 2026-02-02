<?php
/**
 * Application Configuration File
 * Cấu hình chung cho ứng dụng
 */

// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình đường dẫn
define('BASE_URL', 'http://localhost/ASM2');
define('ROOT_PATH', dirname(__DIR__));

// Cấu hình hiển thị lỗi (tắt khi production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once ROOT_PATH . '/config/database.php';

/**
 * Hàm redirect trang
 * @param string $url URL cần chuyển hướng
 */
function redirect($url) {
    header("Location: " . BASE_URL . "/" . $url);
    exit();
}

/**
 * Hàm escape HTML để tránh XSS
 * @param string $string Chuỗi cần escape
 * @return string
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Hàm hiển thị thông báo flash
 * @param string $type Loại thông báo (success, error, warning, info)
 * @param string $message Nội dung thông báo
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Hàm lấy và xóa thông báo flash
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra người dùng có phải admin không
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Yêu cầu đăng nhập để truy cập trang
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('auth/login.php');
    }
}

/**
 * Yêu cầu quyền admin để truy cập trang
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Bạn không có quyền truy cập trang này.');
        redirect('dashboard.php');
    }
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Việt Nam)
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    return preg_match('/^(0|\+84)[0-9]{9,10}$/', $phone);
}

/**
 * Format date từ Y-m-d sang d/m/Y
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>
