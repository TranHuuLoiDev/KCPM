<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\UserController;

$controller = new UserController();
$actionResult = $controller->handleRequest();

$success_msg = '';
$error_msg = '';

if ($actionResult) {
    if ($actionResult['status'] === 'success') {
        $success_msg = $actionResult['message'];
    } else {
        $error_msg = $actionResult['message'];
    }
}

// lấy thông tin user nếu đang sửa
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_user = $controller->getUserById($_GET['edit_id']);
}

// Tìm kiếm
$search = $_GET['search'] ?? '';
$users_list = trim($search) !== ''
    ? $controller->getUsersByFilter($search)
    : $controller->getAllUsers();

// Thống kê
$stats = $controller->getStats();
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý người dùng</h1>
            <p class="mb-0 mt-2 text-muted">Theo dõi danh sách người dùng, thống kê và quản lý tài khoản trên hệ thống.</p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <script>window.alert('<?= addslashes($success_msg) ?>');</script>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <script>window.alert('Lỗi: <?= addslashes($error_msg) ?>');</script>
    <?php endif; ?>

    <!-- Thống kê -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="admin-card d-flex align-items-center" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-people-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= $stats['total_users'] ?></h3>
                    <p class="mb-0 text-white-50">Tổng người dùng (Khách)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="admin-card d-flex align-items-center" style="background: linear-gradient(135deg, #4CAF50, #388E3C);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-person-plus-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= $stats['new_this_month'] ?></h3>
                    <p class="mb-0 text-white-50">Đăng ký tháng này</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card d-flex align-items-center" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= $stats['active_users'] ?></h3>
                    <p class="mb-0 text-white-50">Đã từng mua vé</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Thêm/Sửa -->
    <div class="admin-card mb-4">
        <h5 class="mb-3 text-white"><i class="bi bi-person-badge me-2"></i><?= $edit_user ? 'Cập nhật tài khoản' : 'Thêm tài khoản mới' ?></h5>
        <form action="manage_users.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_user ? 'edit' : 'add' ?>">
            <?php if ($edit_user): ?>
                <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Họ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="first_name" required placeholder="VD: Nguyễn" value="<?= htmlspecialchars($edit_user['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="last_name" required placeholder="VD: Văn An" value="<?= htmlspecialchars($edit_user['last_name'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required placeholder="VD: email@example.com" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="phone" required placeholder="VD: 0912345678" value="<?= htmlspecialchars($edit_user['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" class="form-control" name="birth_date" value="<?= htmlspecialchars($edit_user['birth_date'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mật khẩu <?= $edit_user ? '(Bỏ trống nếu không đổi)' : '<span class="text-danger">*</span>' ?></label>
                    <input type="password" class="form-control" name="password" <?= $edit_user ? '' : 'required' ?> placeholder="Nhập mật khẩu...">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vai trò</label>
                    <select class="form-select" name="role">
                        <option value="user" <?= ($edit_user && $edit_user['role'] == 'user') ? 'selected' : '' ?>>Khách hàng (User)</option>
                        <option value="admin" <?= ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : '' ?>>Quản trị (Admin)</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 text-end">
                <?php if ($edit_user): ?>
                    <a href="manage_users.php" class="btn btn-admin-secondary me-2">Hủy</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-netflix-red">
                    <?= $edit_user ? 'Lưu thay đổi' : 'Thêm người dùng' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="admin-card mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0 text-white"><i class="bi bi-list-ul me-2"></i>Danh sách người dùng</h5>
        <form action="manage_users.php" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email, sđt..." value="<?= htmlspecialchars($search) ?>" style="max-width: 250px;">
            <button type="submit" class="btn btn-outline-light"><i class="bi bi-search"></i></button>
            <?php if (!empty($search)): ?>
                <a href="manage_users.php" class="btn btn-outline-secondary">Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Bảng danh sách -->
    <div class="admin-card">
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Thông tin liên hệ</th>
                        <th>Ngày sinh</th>
                        <th>Vai trò</th>
                        <th>Số vé mua</th>
                        <th>Tổng chi tiêu</th>
                        <th>Ngày đăng ký</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users_list)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Không tìm thấy dữ liệu người dùng</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users_list as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-white"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                </td>
                                <td>
                                    <div><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($u['email']) ?></div>
                                    <div><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($u['phone']) ?></div>
                                </td>
                                <td><?= $u['birth_date'] ? date('d/m/Y', strtotime($u['birth_date'])) : '<span class="text-muted">Chưa cập nhật</span>' ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= $u['total_bookings'] ?></span></td>
                                <td><strong class="text-success"><?= number_format($u['total_spent'], 0, ',', '.') ?>đ</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="manage_users.php?edit_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-info admin-icon-btn" title="Sửa">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
