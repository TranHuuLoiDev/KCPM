<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\TheatreController;

$controller = new TheatreController();
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

$theatres_list = $controller->getAllTheatres();
$edit_theatre = null;

if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    foreach ($theatres_list as $theatre) {
        if ((int) $theatre['id'] === $edit_id) {
            $edit_theatre = $theatre;
            break;
        }
    }
}
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý rạp chiếu</h1>
            <p class="mb-0 mt-2 text-muted">Thêm, sửa và xóa thông tin rạp. Xóa rạp sẽ xóa toàn bộ phòng, ghế và suất chiếu liên quan.</p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    <?php endif; ?>

    <div class="admin-card mb-4">
        <h5 class="mb-3 text-white">
            <i class="bi bi-building me-2"></i><?= $edit_theatre ? 'Cập nhật rạp' : 'Thêm rạp mới' ?>
        </h5>
        <form action="manage_theatres.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_theatre ? 'edit' : 'add' ?>">
            <?php if ($edit_theatre): ?>
                <input type="hidden" name="id" value="<?= $edit_theatre['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tên rạp <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        name="name"
                        required
                        placeholder="VD: CGV Vincom"
                        value="<?= htmlspecialchars($edit_theatre['name'] ?? '') ?>"
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số phòng chiếu <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        class="form-control"
                        name="total_screens"
                        min="1"
                        required
                        value="<?= htmlspecialchars((string)($edit_theatre['total_screens'] ?? 1)) ?>"
                    >
                </div>
                <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <input
                        type="text"
                        class="form-control"
                        name="address"
                        placeholder="Số nhà, đường, quận..."
                        value="<?= htmlspecialchars($edit_theatre['address'] ?? '') ?>"
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thành phố</label>
                    <input
                        type="text"
                        class="form-control"
                        name="city"
                        placeholder="VD: Hồ Chí Minh"
                        value="<?= htmlspecialchars($edit_theatre['city'] ?? '') ?>"
                    >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Điện thoại</label>
                    <input
                        type="text"
                        class="form-control"
                        name="phone"
                        placeholder="VD: 1900545415"
                        value="<?= htmlspecialchars($edit_theatre['phone'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="mt-3 text-end">
                <?php if ($edit_theatre): ?>
                    <a href="manage_theatres.php" class="btn btn-outline-light me-2">Hủy</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-netflix-red">
                    <?= $edit_theatre ? 'Lưu thay đổi' : 'Thêm rạp' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h5 class="mb-3 text-white"><i class="bi bi-building me-2"></i>Danh sách rạp</h5>
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="18%">Tên rạp</th>
                        <th width="22%">Địa chỉ</th>
                        <th width="10%">Thành phố</th>
                        <th width="12%">Điện thoại</th>
                        <th width="8%">Số phòng</th>
                        <th width="8%">Phòng thực tế</th>
                        <th width="17%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($theatres_list)): ?>
                        <?php foreach ($theatres_list as $theatre): ?>
                            <tr>
                                <td><?= $theatre['id'] ?></td>
                                <td><strong><?= htmlspecialchars($theatre['name']) ?></strong></td>
                                <td><?= htmlspecialchars($theatre['address'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($theatre['city'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($theatre['phone'] ?? '—') ?></td>
                                <td><?= (int)$theatre['total_screens'] ?></td>
                                <td><?= (int)$theatre['room_count'] ?></td>
                                <td class="text-center">
                                    <a href="manage_theatres.php?edit_id=<?= $theatre['id'] ?>" class="btn btn-sm btn-outline-info admin-icon-btn me-1" title="Sửa rạp">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Xóa rạp này sẽ xóa toàn bộ phòng, ghế và suất chiếu liên quan. Bạn có chắc chắn?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $theatre['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa rạp">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="admin-empty d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-building"></i>
                                    <span>Chưa có rạp nào. Hãy thêm rạp đầu tiên.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
