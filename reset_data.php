<?php
/**
 * Script reset dữ liệu mẫu
 * Chạy file này một lần để tạo lại dữ liệu đúng encoding UTF-8
 */
require_once 'config/config.php';

global $pdo;

try {
    // Tạo lại bảng users
    $pdo->exec("DROP TABLE IF EXISTS students");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_code VARCHAR(20) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        birthday DATE NOT NULL,
        gender VARCHAR(10) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15),
        address TEXT,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Thêm users mẫu (password = 'password')
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', $hashedPassword, 'admin@example.com', 'admin']);
    $stmt->execute(['user1', $hashedPassword, 'user1@example.com', 'user']);
    $stmt->execute(['user2', $hashedPassword, 'user2@example.com', 'user']);
    
    // Thêm sinh viên mẫu
    $students = [
        ['SV001', 'Nguyễn Văn An', '2000-05-15', 'Nam', 'nguyenvanan@email.com', '0901234567', 'Hà Nội', 1],
        ['SV002', 'Trần Thị Bình', '2001-08-20', 'Nữ', 'tranthibinh@email.com', '0912345678', 'Hồ Chí Minh', 1],
        ['SV003', 'Lê Hoàng Cường', '2000-12-10', 'Nam', 'lehoangcuong@email.com', '0923456789', 'Đà Nẵng', 1],
        ['SV004', 'Phạm Thị Dung', '2001-03-25', 'Nữ', 'phamthidung@email.com', '0934567890', 'Hải Phòng', 2],
        ['SV005', 'Hoàng Văn Em', '2000-07-18', 'Nam', 'hoangvanem@email.com', '0945678901', 'Cần Thơ', 2],
        ['SV006', 'Vũ Thị Phương', '2001-11-30', 'Nữ', 'vuthiphuong@email.com', '0956789012', 'Huế', 1],
        ['SV007', 'Đặng Quốc Hùng', '2000-02-14', 'Nam', 'dangquochung@email.com', '0967890123', 'Nha Trang', 1],
        ['SV008', 'Bùi Thị Hạnh', '2001-06-08', 'Nữ', 'buithihanh@email.com', '0978901234', 'Vũng Tàu', 2],
        ['SV009', 'Ngô Minh Khang', '2000-09-22', 'Nam', 'ngominhkhang@email.com', '0989012345', 'Biên Hòa', 1],
        ['SV010', 'Đinh Thị Lan', '2001-01-05', 'Nữ', 'dinhthilan@email.com', '0990123456', 'Bình Dương', 1],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($students as $student) {
        $stmt->execute($student);
    }
    
    echo "<h1 style='color:green'>✓ Reset dữ liệu thành công!</h1>";
    echo "<p>Đã tạo 3 users và 10 sinh viên mẫu.</p>";
    echo "<p><strong>Tài khoản đăng nhập:</strong></p>";
    echo "<ul>";
    echo "<li>Admin: admin / password</li>";
    echo "<li>User: user1 / password</li>";
    echo "<li>User: user2 / password</li>";
    echo "</ul>";
    echo "<p><a href='auth/login.php'>Đăng nhập ngay</a></p>";
    
} catch (PDOException $e) {
    echo "<h1 style='color:red'>✗ Lỗi!</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
