<?php
/**
 * Trang Dashboard
 * Hiển thị tổng quan thống kê và thông tin nhanh
 */
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

global $pdo;

// Lấy thống kê
try {
    // Tổng số sinh viên
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
    $totalStudents = $stmt->fetch()['total'];
    
    // Tổng số users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    // Sinh viên theo giới tính
    $stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM students GROUP BY gender");
    $genderStats = $stmt->fetchAll();
    
    // Sinh viên mới nhất
    $stmt = $pdo->query("SELECT s.*, u.username as created_by FROM students s 
                         LEFT JOIN users u ON s.user_id = u.id 
                         ORDER BY s.created_at DESC LIMIT 5");
    $recentStudents = $stmt->fetchAll();
    
    // Nếu là user thường, chỉ đếm sinh viên của họ
    if (!isAdmin()) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $myStudents = $stmt->fetch()['total'];
    }
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $totalStudents = 0;
    $totalUsers = 0;
    $genderStats = [];
    $recentStudents = [];
}

// Xử lý giới tính statistics
$maleCount = 0;
$femaleCount = 0;
foreach ($genderStats as $stat) {
    if ($stat['gender'] === 'Nam') {
        $maleCount = $stat['count'];
    } elseif ($stat['gender'] === 'Nữ') {
        $femaleCount = $stat['count'];
    }
}
?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stats-card bg-primary-gradient">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Tổng sinh viên</h6>
                    <h2 class="mb-0"><?= $totalStudents ?></h2>
                </div>
                <i class="fas fa-user-graduate fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    
    <?php if (isAdmin()): ?>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stats-card bg-success-gradient">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Tổng người dùng</h6>
                    <h2 class="mb-0"><?= $totalUsers ?></h2>
                </div>
                <i class="fas fa-users fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stats-card bg-success-gradient">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">SV tôi tạo</h6>
                    <h2 class="mb-0"><?= $myStudents ?? 0 ?></h2>
                </div>
                <i class="fas fa-user-plus fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stats-card bg-info-gradient">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Sinh viên Nam</h6>
                    <h2 class="mb-0"><?= $maleCount ?></h2>
                </div>
                <i class="fas fa-mars fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stats-card bg-warning-gradient">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Sinh viên Nữ</h6>
                    <h2 class="mb-0"><?= $femaleCount ?></h2>
                </div>
                <i class="fas fa-venus fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i>Thao tác nhanh
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>Xem danh sách sinh viên
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>/students/add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm sinh viên mới
                    </a>
                    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-secondary">
                        <i class="fas fa-users-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/profile.php" class="btn btn-outline-info">
                        <i class="fas fa-user me-2"></i>Xem thông tin cá nhân
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Students -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2"></i>Sinh viên mới thêm</span>
                <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-sm btn-outline-primary">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentStudents)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Chưa có sinh viên nào</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Mã SV</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Người tạo</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentStudents as $student): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= escape($student['student_code']) ?></span></td>
                                    <td><?= escape($student['full_name']) ?></td>
                                    <td><?= escape($student['email']) ?></td>
                                    <td>
                                        <small class="text-muted"><?= escape($student['created_by']) ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($student['created_at'])) ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- User Info Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Thông tin phiên đăng nhập
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tên đăng nhập:</strong> <?= escape($_SESSION['username']) ?></p>
                        <p><strong>Email:</strong> <?= escape($_SESSION['email']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Vai trò:</strong> 
                            <span class="badge <?= $_SESSION['role'] === 'admin' ? 'bg-danger' : 'bg-info' ?>">
                                <?= $_SESSION['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng' ?>
                            </span>
                        </p>
                        <p><strong>Ngày hiện tại:</strong> <?= date('d/m/Y H:i:s') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
