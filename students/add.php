<?php
/**
 * Trang thêm sinh viên mới
 * Chỉ admin mới có quyền truy cập
 */
$pageTitle = 'Thêm sinh viên mới';
require_once '../includes/header.php';

// Kiểm tra quyền admin
requireAdmin();

global $pdo;

$errors = [];
$old = [];

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $student_code = trim($_POST['student_code'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $birthday = $_POST['birthday'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Lưu dữ liệu cũ
    $old = compact('student_code', 'full_name', 'birthday', 'gender', 'email', 'phone', 'address');
    
    // Validate mã sinh viên
    if (empty($student_code)) {
        $errors['student_code'] = 'Mã sinh viên không được để trống.';
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $student_code)) {
        $errors['student_code'] = 'Mã sinh viên chỉ chứa chữ cái và số.';
    } else {
        // Kiểm tra mã đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM students WHERE student_code = ?");
        $stmt->execute([$student_code]);
        if ($stmt->fetch()) {
            $errors['student_code'] = 'Mã sinh viên đã tồn tại.';
        }
    }
    
    // Validate họ tên
    if (empty($full_name)) {
        $errors['full_name'] = 'Họ tên không được để trống.';
    } elseif (strlen($full_name) > 100) {
        $errors['full_name'] = 'Họ tên không được quá 100 ký tự.';
    }
    
    // Validate ngày sinh
    if (empty($birthday)) {
        $errors['birthday'] = 'Ngày sinh không được để trống.';
    } else {
        $birthdayDate = strtotime($birthday);
        if ($birthdayDate === false) {
            $errors['birthday'] = 'Ngày sinh không hợp lệ.';
        } elseif ($birthdayDate > time()) {
            $errors['birthday'] = 'Ngày sinh không thể trong tương lai.';
        }
    }
    
    // Validate giới tính
    if (empty($gender)) {
        $errors['gender'] = 'Vui lòng chọn giới tính.';
    } elseif (!in_array($gender, ['Nam', 'Nữ', 'Khác'])) {
        $errors['gender'] = 'Giới tính không hợp lệ.';
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!isValidEmail($email)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    
    // Validate phone (optional)
    if (!empty($phone) && !isValidPhone($phone)) {
        $errors['phone'] = 'Số điện thoại không hợp lệ.';
    }
    
    // Nếu không có lỗi thì thêm sinh viên
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $student_code, 
                $full_name, 
                $birthday, 
                $gender, 
                $email, 
                $phone ?: null, 
                $address ?: null,
                $_SESSION['user_id']
            ]);
            
            setFlashMessage('success', 'Thêm sinh viên thành công!');
            redirect('students/index.php');
        } catch (PDOException $e) {
            error_log("Add Student Error: " . $e->getMessage());
            $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus me-2"></i>Thêm sinh viên mới
            </div>
            <div class="card-body">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger"><?= escape($errors['general']) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="addStudentForm" novalidate>
                    <div class="row">
                        <!-- Mã sinh viên -->
                        <div class="col-md-6 mb-3">
                            <label for="student_code" class="form-label">
                                Mã sinh viên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control <?= isset($errors['student_code']) ? 'is-invalid' : '' ?>" 
                                   id="student_code" name="student_code" 
                                   value="<?= escape($old['student_code'] ?? '') ?>" 
                                   required pattern="[A-Za-z0-9]+" maxlength="20">
                            <?php if (isset($errors['student_code'])): ?>
                                <div class="invalid-feedback"><?= escape($errors['student_code']) ?></div>
                            <?php endif; ?>
                            <small class="text-muted">Chỉ chứa chữ cái và số, VD: SV001</small>
                        </div>
                        
                        <!-- Họ tên -->
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                                   id="full_name" name="full_name" 
                                   value="<?= escape($old['full_name'] ?? '') ?>" 
                                   required maxlength="100">
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback"><?= escape($errors['full_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Ngày sinh -->
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">
                                Ngày sinh <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control <?= isset($errors['birthday']) ? 'is-invalid' : '' ?>" 
                                   id="birthday" name="birthday" 
                                   value="<?= escape($old['birthday'] ?? '') ?>" 
                                   required max="<?= date('Y-m-d') ?>">
                            <?php if (isset($errors['birthday'])): ?>
                                <div class="invalid-feedback"><?= escape($errors['birthday']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Giới tính -->
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">
                                Giới tính <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" 
                                    id="gender" name="gender" required>
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?= ($old['gender'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= ($old['gender'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                                <option value="Khác" <?= ($old['gender'] ?? '') === 'Khác' ? 'selected' : '' ?>>Khác</option>
                            </select>
                            <?php if (isset($errors['gender'])): ?>
                                <div class="invalid-feedback"><?= escape($errors['gender']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" 
                                   value="<?= escape($old['email'] ?? '') ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= escape($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Điện thoại -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   id="phone" name="phone" 
                                   value="<?= escape($old['phone'] ?? '') ?>" 
                                   placeholder="VD: 0901234567">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= escape($errors['phone']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Địa chỉ -->
                    <div class="mb-4">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?= escape($old['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu sinh viên
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Client-side validation
    document.getElementById('addStudentForm').addEventListener('submit', function(e) {
        const studentCode = document.getElementById('student_code');
        const pattern = /^[A-Za-z0-9]+$/;
        
        if (!pattern.test(studentCode.value)) {
            e.preventDefault();
            studentCode.setCustomValidity('Mã sinh viên chỉ chứa chữ cái và số');
            studentCode.reportValidity();
        } else {
            studentCode.setCustomValidity('');
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
