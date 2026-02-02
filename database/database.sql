-- =====================================================
-- Database: student_management
-- Hệ thống quản lý sinh viên
-- Tạo ngày: 2026-02-02
-- =====================================================

-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS student_management 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE student_management;

-- =====================================================
-- Bảng users: Lưu thông tin người dùng hệ thống
-- =====================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên đăng nhập',
    password VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa bằng password_hash',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email người dùng',
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user' COMMENT 'Vai trò: admin hoặc user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật',
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng người dùng hệ thống';

-- =====================================================
-- Bảng students: Lưu thông tin sinh viên
-- =====================================================
DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã sinh viên',
    full_name VARCHAR(100) NOT NULL COMMENT 'Họ và tên',
    birthday DATE NOT NULL COMMENT 'Ngày sinh',
    gender ENUM('Nam', 'Nữ', 'Khác') NOT NULL COMMENT 'Giới tính',
    email VARCHAR(100) NOT NULL COMMENT 'Email sinh viên',
    phone VARCHAR(15) COMMENT 'Số điện thoại',
    address TEXT COMMENT 'Địa chỉ',
    user_id INT NOT NULL COMMENT 'ID người tạo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_code (student_code),
    INDEX idx_full_name (full_name),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng thông tin sinh viên';

-- =====================================================
-- Dữ liệu mẫu cho bảng users
-- Mật khẩu: admin123 và user123 (đã mã hóa bằng password_hash)
-- =====================================================
INSERT INTO users (username, password, email, role) VALUES
-- Mật khẩu: admin123
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin'),
-- Mật khẩu: user123
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@example.com', 'user'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@example.com', 'user');

-- =====================================================
-- Dữ liệu mẫu cho bảng students
-- =====================================================
INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) VALUES
('SV001', 'Nguyễn Văn An', '2000-05-15', 'Nam', 'nguyenvanan@email.com', '0901234567', 'Hà Nội', 1),
('SV002', 'Trần Thị Bình', '2001-08-20', 'Nữ', 'tranthibinh@email.com', '0912345678', 'Hồ Chí Minh', 1),
('SV003', 'Lê Hoàng Cường', '2000-12-10', 'Nam', 'lehoangcuong@email.com', '0923456789', 'Đà Nẵng', 1),
('SV004', 'Phạm Thị Dung', '2001-03-25', 'Nữ', 'phamthidung@email.com', '0934567890', 'Hải Phòng', 2),
('SV005', 'Hoàng Văn Em', '2000-07-18', 'Nam', 'hoangvanem@email.com', '0945678901', 'Cần Thơ', 2),
('SV006', 'Vũ Thị Phương', '2001-11-30', 'Nữ', 'vuthiphuong@email.com', '0956789012', 'Huế', 1),
('SV007', 'Đặng Quốc Hùng', '2000-02-14', 'Nam', 'dangquochung@email.com', '0967890123', 'Nha Trang', 1),
('SV008', 'Bùi Thị Hạnh', '2001-06-08', 'Nữ', 'buithihanh@email.com', '0978901234', 'Vũng Tàu', 2),
('SV009', 'Ngô Minh Khang', '2000-09-22', 'Nam', 'ngominhkhang@email.com', '0989012345', 'Biên Hòa', 1),
('SV010', 'Đinh Thị Lan', '2001-01-05', 'Nữ', 'dinhthilan@email.com', '0990123456', 'Bình Dương', 1);

-- =====================================================
-- Lưu ý: Mật khẩu mẫu
-- Admin: username = admin, password = password (BCrypt hash)
-- User: username = user1/user2, password = password
-- Bạn có thể thay đổi mật khẩu sau khi đăng nhập
-- =====================================================
