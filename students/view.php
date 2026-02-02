<?php
/**
 * Trang xem chi tiết sinh viên
 */
$pageTitle = 'Chi tiết sinh viên';
require_once '../includes/header.php';

global $pdo;

// Lấy ID sinh viên từ URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlashMessage('error', 'ID sinh viên không hợp lệ.');
    redirect('students/index.php');
}

// Lấy thông tin sinh viên
try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.username as created_by 
        FROM students s 
        LEFT JOIN users u ON s.user_id = u.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        setFlashMessage('error', 'Không tìm thấy sinh viên.');
        redirect('students/index.php');
    }
} catch (PDOException $e) {
    error_log("View Student Error: " . $e->getMessage());
    setFlashMessage('error', 'Có lỗi xảy ra.');
    redirect('students/index.php');
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-graduate me-2"></i>Thông tin sinh viên</span>
                <span class="badge bg-primary fs-6"><?= escape($student['student_code']) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Mã sinh viên</label>
                        <p class="form-control-plaintext fw-bold"><?= escape($student['student_code']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Họ và tên</label>
                        <p class="form-control-plaintext fw-bold"><?= escape($student['full_name']) ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Ngày sinh</label>
                        <p class="form-control-plaintext"><?= formatDate($student['birthday']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Giới tính</label>
                        <p class="form-control-plaintext">
                            <?php if ($student['gender'] === 'Nam'): ?>
                                <span class="badge bg-info"><i class="fas fa-mars me-1"></i>Nam</span>
                            <?php elseif ($student['gender'] === 'Nữ'): ?>
                                <span class="badge bg-pink" style="background-color: #e91e8c;">
                                    <i class="fas fa-venus me-1"></i>Nữ
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Khác</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="form-control-plaintext">
                            <a href="mailto:<?= escape($student['email']) ?>">
                                <i class="fas fa-envelope me-1"></i><?= escape($student['email']) ?>
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Số điện thoại</label>
                        <p class="form-control-plaintext">
                            <?php if ($student['phone']): ?>
                                <a href="tel:<?= escape($student['phone']) ?>">
                                    <i class="fas fa-phone me-1"></i><?= escape($student['phone']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Chưa cập nhật</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Địa chỉ</label>
                    <p class="form-control-plaintext">
                        <?php if ($student['address']): ?>
                            <i class="fas fa-map-marker-alt me-1"></i><?= escape($student['address']) ?>
                        <?php else: ?>
                            <span class="text-muted">Chưa cập nhật</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Người tạo</label>
                        <p class="form-control-plaintext">
                            <i class="fas fa-user me-1"></i><?= escape($student['created_by'] ?? 'N/A') ?>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Ngày tạo</label>
                        <p class="form-control-plaintext">
                            <i class="fas fa-calendar me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($student['created_at'])) ?>
                        </p>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-3">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="edit.php?id=<?= $id ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Sửa
                        </a>
                        <a href="delete.php?id=<?= $id ?>" class="btn btn-danger"
                           onclick="return confirmDelete('Bạn có chắc muốn xóa sinh viên này?')">
                            <i class="fas fa-trash me-2"></i>Xóa
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
