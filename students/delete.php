<?php
/**
 * Xử lý xóa sinh viên
 * Chỉ admin mới có quyền xóa
 */
require_once '../config/config.php';

// Kiểm tra quyền admin
requireAdmin();

global $pdo;

// Lấy ID sinh viên từ URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage('error', 'ID sinh viên không hợp lệ.');
    redirect('students/index.php');
}

try {
    // Kiểm tra sinh viên có tồn tại không
    $stmt = $pdo->prepare("SELECT id, full_name, student_code FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        setFlashMessage('error', 'Không tìm thấy sinh viên.');
        redirect('students/index.php');
    }
    
    // Xóa sinh viên
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    
    setFlashMessage('success', 'Đã xóa sinh viên "' . $student['full_name'] . '" (Mã: ' . $student['student_code'] . ') thành công!');
    
} catch (PDOException $e) {
    error_log("Delete Student Error: " . $e->getMessage());
    setFlashMessage('error', 'Có lỗi xảy ra khi xóa sinh viên. Vui lòng thử lại.');
}

redirect('students/index.php');
?>
