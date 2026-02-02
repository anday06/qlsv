<?php
/**
 * Trang quản lý người dùng
 * Chỉ admin mới có quyền truy cập
 */
$pageTitle = 'Quản lý người dùng';
require_once '../includes/header.php';

// Kiểm tra quyền admin
requireAdmin();

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
    
    if (!empty($search)) {
        $whereClause = "WHERE (username LIKE ? OR email LIKE ?)";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam];
    }
    
    // Đếm tổng số users
    $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalUsers = $stmt->fetch()['total'];
    $totalPages = ceil($totalUsers / $perPage);
    
    // Lấy danh sách users với phân trang
    $sql = "SELECT u.*, 
                   (SELECT COUNT(*) FROM students WHERE user_id = u.id) as student_count
            FROM users u 
            {$whereClause} 
            ORDER BY u.created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Users List Error: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $totalPages = 0;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <i class="fas fa-users-cog me-2"></i>
            Danh sách người dùng 
            <span class="badge bg-primary"><?= $totalUsers ?></span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Form tìm kiếm -->
            <form class="d-flex" method="GET" action="">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Tìm theo username hoặc email..." 
                           value="<?= escape($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="users.php" class="btn btn-outline-danger" title="Xóa tìm kiếm">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-4x mb-3"></i>
                <p class="mb-0">
                    <?php if (!empty($search)): ?>
                        Không tìm thấy người dùng phù hợp với "<?= escape($search) ?>"
                    <?php else: ?>
                        Chưa có người dùng nào trong hệ thống
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px">ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>SV đã tạo</th>
                            <th>Ngày tạo</th>
                            <th style="width: 100px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <strong><?= escape($user['username']) ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge bg-info">Bạn</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="mailto:<?= escape($user['email']) ?>">
                                    <?= escape($user['email']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-user-shield me-1"></i>Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-user me-1"></i>User
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $user['student_count'] ?></span> sinh viên
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" 
                                       class="btn btn-outline-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete_user.php?id=<?= $user['id'] ?>" 
                                           class="btn btn-outline-danger" title="Xóa"
                                           onclick="return confirmDelete('Bạn có chắc muốn xóa người dùng này? Tất cả sinh viên do họ tạo cũng sẽ bị xóa.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
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
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
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
                        
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <p class="text-center text-muted mt-2 mb-0">
                    Hiển thị <?= count($users) ?> / <?= $totalUsers ?> người dùng
                </p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
