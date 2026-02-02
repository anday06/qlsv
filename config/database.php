<?php
/**
 * Database Configuration File
 * Cấu hình kết nối cơ sở dữ liệu MySQL sử dụng PDO
 */

// Thông tin kết nối database
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mật khẩu mặc định của Laragon là rỗng

/**
 * Tạo kết nối PDO đến database
 * @return PDO|null
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Bật chế độ exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch dạng mảng kết hợp
            PDO::ATTR_EMULATE_PREPARES => false, // Tắt emulate prepares để bảo mật hơn
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log lỗi và hiển thị thông báo thân thiện
        error_log("Database Connection Error: " . $e->getMessage());
        die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng liên hệ quản trị viên.");
    }
}

// Khởi tạo kết nối global
$pdo = getDBConnection();
?>
