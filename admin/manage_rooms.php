<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\RoomController;

$controller = new RoomController();
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

$rooms_list = $controller->getAllRooms();
$theatres_list = $controller->getAllTheatres();
$edit_room = null;

if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    foreach ($rooms_list as $room) {
        if ((int) $room['id'] === $edit_id) {
            $edit_room = $room;
            break;
        }
    }
}
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý phòng chiếu</h1>
            <p class="mb-0 mt-2 text-muted">Gán phòng cho từng rạp. Tên phòng phải duy nhất trong toàn hệ thống.</p>
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
            <i class="bi bi-door-open me-2"></i><?= $edit_room ? 'Cập nhật phòng chiếu' : 'Thêm phòng chiếu' ?>
        </h5>
        <form action="manage_rooms.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_room ? 'edit' : 'add' ?>">
            <?php if ($edit_room): ?>
                <input type="hidden" name="id" value="<?= $edit_room['id'] ?>">
            <?php endif; ?>
            <?php renderRoomFormFields('room_form', $theatres_list, $edit_room); ?>
            <div class="mt-3 text-end">
                <?php if ($edit_room): ?>
                    <a href="manage_rooms.php" class="btn btn-outline-light me-2">Hủy</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-netflix-red">
                    <?= $edit_room ? 'Lưu thay đổi' : 'Thêm phòng' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h5 class="mb-3 text-white"><i class="bi bi-door-open me-2"></i>Danh sách phòng chiếu</h5>
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Rạp</th>
                        <th width="15%">Tên phòng</th>
                        <th width="10%">Số ghế</th>
                        <th width="10%">Ghế thực tế</th>
                        <th width="10%">Trạng thái</th>
                        <th width="15%">Ngày tạo</th>
                        <th width="15%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rooms_list)): ?>
                        <?php foreach ($rooms_list as $room): ?>
                            <tr>
                                <td><?= $room['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($room['theatre_name']) ?></strong>
                                    <br><span class="text-muted small"><?= htmlspecialchars($room['theatre_city'] ?? '') ?></span>
                                </td>
                                <td><?= htmlspecialchars($room['name']) ?></td>
                                <td><?= (int)$room['total_seats'] ?></td>
                                <td><?= (int)$room['seat_count'] ?></td>
                                <td>
                                    <?php if ($room['is_active']): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tạm ngưng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($room['created_at'])) ?></td>
                                <td class="text-center">
                                    <a href="manage_rooms.php?edit_id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-info admin-icon-btn me-1" title="Sửa phòng">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Xóa phòng này sẽ xóa toàn bộ ghế và suất chiếu liên quan. Bạn có chắc chắn?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $room['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa phòng">
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
                                    <i class="bi bi-door-open"></i>
                                    <span>Chưa có phòng chiếu nào. Hãy thêm phòng đầu tiên.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function renderRoomFormFields($prefix, $theatres_list, $room = null) {
?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Rạp chiếu <span class="text-danger">*</span></label>
            <select class="form-select" name="theatre_id" id="<?= $prefix ?>_theatre_id" required>
                <option value="">-- Chọn rạp --</option>
                <?php foreach ($theatres_list as $theatre): ?>
                    <option value="<?= $theatre['id'] ?>" <?= (int)($room['theatre_id'] ?? 0) === (int)$theatre['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($theatre['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Tên phòng <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" id="<?= $prefix ?>_name" required placeholder="VD: Phòng 1" value="<?= htmlspecialchars($room['name'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Số ghế <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="total_seats" id="<?= $prefix ?>_total_seats" min="1" value="<?= htmlspecialchars((string)($room['total_seats'] ?? 40)) ?>" required>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="<?= $prefix ?>_is_active" <?= !isset($room['is_active']) || $room['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= $prefix ?>_is_active">Phòng đang hoạt động</label>
            </div>
        </div>
    </div>
<?php } ?>

<?php require_once 'admin_footer.php'; ?>
