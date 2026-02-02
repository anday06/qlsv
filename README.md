# Hệ thống Quản lý Sinh viên - PHP

## 📋 Mô tả dự án

Đây là ứng dụng web quản lý sinh viên được xây dựng bằng PHP thuần (không sử dụng framework). Ứng dụng bao gồm các chức năng:

- **Xác thực**: Đăng ký, đăng nhập, đăng xuất với session
- **Quản lý sinh viên**: Thêm, sửa, xóa, tìm kiếm sinh viên
- **Phân quyền**: Admin (full quyền) và User (chỉ xem)
- **Bảo mật**: Mã hóa mật khẩu, validate dữ liệu, chống XSS

## 🚀 Yêu cầu hệ thống

- **PHP**: >= 7.4
- **MySQL**: >= 5.7
- **Web Server**: Apache (với mod_rewrite) hoặc Nginx
- **Trình duyệt**: Chrome, Firefox, Edge (phiên bản mới)

## 📁 Cấu trúc thư mục

```
ASM2/
├── admin/                  # Quản lý admin
│   ├── users.php           # Danh sách users
│   ├── edit_user.php       # Sửa user
│   └── delete_user.php     # Xóa user
├── auth/                   # Xác thực
│   ├── login.php           # Đăng nhập
│   ├── register.php        # Đăng ký
│   └── logout.php          # Đăng xuất
├── config/                 # Cấu hình
│   ├── config.php          # Cấu hình chung
│   └── database.php        # Kết nối database
├── database/               # SQL
│   └── database.sql        # Script tạo database
├── includes/               # Components
│   ├── header.php          # Header template
│   └── footer.php          # Footer template
├── students/               # Quản lý sinh viên
│   ├── index.php           # Danh sách sinh viên
│   ├── add.php             # Thêm sinh viên
│   ├── edit.php            # Sửa sinh viên
│   ├── view.php            # Xem chi tiết
│   └── delete.php          # Xóa sinh viên
├── dashboard.php           # Trang chủ
├── profile.php             # Thông tin cá nhân
├── index.php               # Entry point
└── README.md               # Hướng dẫn
```

## ⚙️ Hướng dẫn cài đặt

### Bước 1: Clone/Download dự án

Đặt thư mục dự án vào thư mục web server:

- **Laragon**: `C:\laragon\www\ASM2`
- **XAMPP**: `C:\xampp\htdocs\ASM2`
- **WAMP**: `C:\wamp64\www\ASM2`

### Bước 2: Tạo Database

1. Mở **phpMyAdmin** hoặc công cụ quản lý MySQL
2. Chạy file SQL: `database/database.sql`

**Hoặc chạy lệnh command line:**

```bash
mysql -u root -p < database/database.sql
```

### Bước 3: Cấu hình Database

Mở file `config/database.php` và chỉnh sửa thông tin kết nối:

```php
define('DB_HOST', 'localhost');     // Host database
define('DB_NAME', 'student_management'); // Tên database
define('DB_USER', 'root');          // Username MySQL
define('DB_PASS', '');              // Password MySQL
```

### Bước 4: Cấu hình URL

Mở file `config/config.php` và chỉnh sửa BASE_URL:

```php
define('BASE_URL', 'http://localhost/ASM2');
```

### Bước 5: Truy cập ứng dụng

Mở trình duyệt và truy cập:

```
http://localhost/ASM2
```

## 🔐 Tài khoản mặc định

| Username | Password | Vai trò |
| -------- | -------- | ------- |
| admin    | password | Admin   |
| user1    | password | User    |
| user2    | password | User    |

> ⚠️ **Lưu ý**: Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu!

## 📊 Database Schema

### Bảng `users`

| Cột        | Kiểu dữ liệu         | Mô tả                  |
| ---------- | -------------------- | ---------------------- |
| id         | INT AUTO_INCREMENT   | ID người dùng          |
| username   | VARCHAR(50)          | Tên đăng nhập (unique) |
| password   | VARCHAR(255)         | Mật khẩu đã mã hóa     |
| email      | VARCHAR(100)         | Email (unique)         |
| role       | ENUM('admin','user') | Vai trò                |
| created_at | TIMESTAMP            | Thời gian tạo          |
| updated_at | TIMESTAMP            | Thời gian cập nhật     |

### Bảng `students`

| Cột          | Kiểu dữ liệu            | Mô tả                 |
| ------------ | ----------------------- | --------------------- |
| id           | INT AUTO_INCREMENT      | ID sinh viên          |
| student_code | VARCHAR(20)             | Mã sinh viên (unique) |
| full_name    | VARCHAR(100)            | Họ và tên             |
| birthday     | DATE                    | Ngày sinh             |
| gender       | ENUM('Nam','Nữ','Khác') | Giới tính             |
| email        | VARCHAR(100)            | Email                 |
| phone        | VARCHAR(15)             | Số điện thoại         |
| address      | TEXT                    | Địa chỉ               |
| user_id      | INT                     | ID người tạo (FK)     |
| created_at   | TIMESTAMP               | Thời gian tạo         |
| updated_at   | TIMESTAMP               | Thời gian cập nhật    |

## 🎯 Tính năng chính

### 1. Xác thực (Authentication)

- ✅ Đăng ký tài khoản mới
- ✅ Đăng nhập với session
- ✅ Đăng xuất (hủy session)
- ✅ Mã hóa mật khẩu với `password_hash()`

### 2. Quản lý sinh viên (CRUD)

- ✅ Xem danh sách sinh viên
- ✅ Thêm sinh viên mới
- ✅ Sửa thông tin sinh viên
- ✅ Xóa sinh viên (có xác nhận)
- ✅ Tìm kiếm theo tên/mã SV
- ✅ Phân trang

### 3. Phân quyền

- **Admin**:
  - Xem/Thêm/Sửa/Xóa sinh viên
  - Quản lý người dùng
- **User**:
  - Chỉ xem danh sách sinh viên
  - Xem thông tin cá nhân

### 4. Bảo mật

- ✅ Validate client-side (HTML5, JavaScript)
- ✅ Validate server-side (PHP)
- ✅ Escape output chống XSS
- ✅ Prepared statements chống SQL Injection
- ✅ Session regeneration chống Session Fixation

## 🎨 Giao diện

- Sử dụng **Bootstrap 5.3** cho giao diện responsive
- **Font Awesome 6** cho icons
- Gradient màu sắc hiện đại
- Sidebar navigation
- Flash messages thông báo

## 📱 Screenshots

### Trang đăng nhập

- Giao diện đẹp với gradient
- Validate form

### Dashboard

- Thống kê tổng quan
- Quick actions
- Danh sách sinh viên mới

### Danh sách sinh viên

- Bảng dữ liệu responsive
- Phân trang
- Tìm kiếm nhanh

### Form thêm/sửa sinh viên

- Validate đầy đủ
- UX thân thiện

## 🔧 Xử lý sự cố

### Lỗi kết nối database

- Kiểm tra MySQL đang chạy
- Kiểm tra thông tin trong `config/database.php`
- Đảm bảo database `student_management` đã được tạo

### Lỗi 404 Not Found

- Kiểm tra đường dẫn thư mục
- Chỉnh sửa `BASE_URL` trong `config/config.php`

### Lỗi session

- Kiểm tra quyền ghi thư mục temp
- Khởi động lại web server

## 📞 Liên hệ

Nếu có vấn đề, vui lòng liên hệ qua email hoặc tạo issue.

## 📄 License

Dự án này được phát triển cho mục đích học tập.

---

**Phát triển bởi**: [Tên của bạn]  
**Ngày tạo**: February 2026
