<?php
/**
 * Trang danh sách sinh viên
 * Hiển thị danh sách và tìm kiếm sinh viên
 */
$pageTitle = 'Danh sách sinh viên';
require_once '../includes/header.php';

global $pdo;

// Lấy tham số tìm kiếm
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Xây dựng query
    $whereClause = "";
    $params = [];
    
    // Tìm kiếm theo tên hoặc mã sinh viên
    if (!empty($search)) {
        $whereClause = "WHERE (s.full_name LIKE ? OR s.student_code LIKE ?)";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam];
    }
    
    // Đếm tổng số sinh viên
    $countSql = "SELECT COUNT(*) as total FROM students s {$whereClause}";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalStudents = $stmt->fetch()['total'];
    $totalPages = ceil($totalStudents / $perPage);
    
    // Lấy danh sách sinh viên với phân trang
    $sql = "SELECT s.*, u.username as created_by 
            FROM students s 
            LEFT JOIN users u ON s.user_id = u.id 
            {$whereClause} 
            ORDER BY s.created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Students List Error: " . $e->getMessage());
    $students = [];
    $totalStudents = 0;
    $totalPages = 0;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <i class="fas fa-user-graduate me-2"></i>
            Danh sách sinh viên 
            <span class="badge bg-primary"><?= $totalStudents ?></span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Form tìm kiếm -->
            <form class="d-flex" method="GET" action="">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Tìm theo tên hoặc mã SV..." 
                           value="<?= escape($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn btn-outline-danger" title="Xóa tìm kiếm">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php if (isAdmin()): ?>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Thêm mới
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (empty($students)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-4x mb-3"></i>
                <p class="mb-0">
                    <?php if (!empty($search)): ?>
                        Không tìm thấy sinh viên phù hợp với "<?= escape($search) ?>"
                    <?php else: ?>
                        Chưa có sinh viên nào trong hệ thống
                    <?php endif; ?>
                </p>
                <?php if (isAdmin()): ?>
                    <a href="add.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i>Thêm sinh viên đầu tiên
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px">ID</th>
                            <th>Mã SV</th>
                            <th>Họ tên</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Địa chỉ</th>
                            <?php if (isAdmin()): ?>
                                <th style="width: 120px">Thao tác</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $student['id'] ?></td>
                            <td>
                                <span class="badge bg-primary"><?= escape($student['student_code']) ?></span>
                            </td>
                            <td>
                                <strong><?= escape($student['full_name']) ?></strong>
                            </td>
                            <td><?= formatDate($student['birthday']) ?></td>
                            <td>
                                <?php if ($student['gender'] === 'Nam'): ?>
                                    <span class="badge bg-info"><i class="fas fa-mars me-1"></i>Nam</span>
                                <?php elseif ($student['gender'] === 'Nữ'): ?>
                                    <span class="badge bg-pink" style="background-color: #e91e8c;">
                                        <i class="fas fa-venus me-1"></i>Nữ
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Khác</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="mailto:<?= escape($student['email']) ?>">
                                    <?= escape($student['email']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($student['phone']): ?>
                                    <a href="tel:<?= escape($student['phone']) ?>">
                                        <?= escape($student['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $student['address'] ? escape($student['address']) : '<span class="text-muted">-</span>' ?>
                            </td>
                            <?php if (isAdmin()): ?>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?= $student['id'] ?>" 
                                       class="btn btn-outline-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $student['id'] ?>" 
                                       class="btn btn-outline-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $student['id'] ?>" 
                                       class="btn btn-outline-danger" title="Xóa"
                                       onclick="return confirmDelete('Bạn có chắc muốn xóa sinh viên này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Phân trang -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <!-- Trang trước -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Các trang -->
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        ?>
                        
                        <?php if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>">
                                    <?= $totalPages ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Trang sau -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <p class="text-center text-muted mt-2 mb-0">
                    Hiển thị <?= count($students) ?> / <?= $totalStudents ?> sinh viên
                </p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
