<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\ShowtimeController;

$controller = new ShowtimeController();
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

$showtimes_list = $controller->getAllShowtimes();
$movies_list = $controller->getAllMovies();
$rooms_list = $controller->getAllRooms();
$edit_showtime = null;

if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    foreach ($showtimes_list as $showtime) {
        if ((int) $showtime['id'] === $edit_id) {
            $edit_showtime = $showtime;
            break;
        }
    }
}
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý lịch chiếu</h1>
            <p class="mb-0 mt-2 text-muted">Gán suất chiếu cho phim và phòng. Giờ kết thúc được tự tính từ giờ bắt đầu và thời lượng phim.</p>
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
            <i class="bi bi-calendar-event me-2"></i><?= $edit_showtime ? 'Cập nhật suất chiếu' : 'Thêm suất chiếu' ?>
        </h5>
        <form action="manage_showtimes.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_showtime ? 'edit' : 'add' ?>">
            <?php if ($edit_showtime): ?>
                <input type="hidden" name="id" value="<?= $edit_showtime['id'] ?>">
            <?php endif; ?>
            <?php renderShowtimeFormFields('showtime_form', $movies_list, $rooms_list, $edit_showtime); ?>
            <div class="mt-3 text-end">
                <?php if ($edit_showtime): ?>
                    <a href="manage_showtimes.php" class="btn btn-outline-light me-2">Hủy</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-netflix-red">
                    <?= $edit_showtime ? 'Lưu thay đổi' : 'Thêm suất chiếu' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h5 class="mb-3 text-white"><i class="bi bi-calendar-event me-2"></i>Danh sách suất chiếu</h5>
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="18%">Phim</th>
                        <th width="18%">Rạp / Phòng</th>
                        <th width="10%">Ngày</th>
                        <th width="10%">Bắt đầu</th>
                        <th width="10%">Kết thúc</th>
                        <th width="10%">Giá vé</th>
                        <th width="9%">Trạng thái</th>
                        <th width="10%" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($showtimes_list)): ?>
                        <?php foreach ($showtimes_list as $showtime): ?>
                            <tr>
                                <td><?= $showtime['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($showtime['movie_title']) ?></strong>
                                    <br><span class="text-muted small"><?= (int)$showtime['movie_duration'] ?> phút</span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($showtime['theatre_name']) ?></strong>
                                    <br><span class="text-muted"><?= htmlspecialchars($showtime['room_name']) ?></span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($showtime['show_date'])) ?></td>
                                <td><?= date('H:i', strtotime($showtime['start_time'])) ?></td>
                                <td><?= date('H:i', strtotime($showtime['end_time'])) ?></td>
                                <td><?= number_format((float)$showtime['base_price'], 0, ',', '.') ?> đ</td>
                                <td>
                                    <?php if ($showtime['status'] === 'active'): ?>
                                        <span class="badge bg-success">Đang mở</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã hủy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="manage_showtimes.php?edit_id=<?= $showtime['id'] ?>" class="btn btn-sm btn-outline-info admin-icon-btn me-1" title="Sửa suất chiếu">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Xóa suất chiếu này? Vé đã đặt liên quan cũng sẽ bị ảnh hưởng.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $showtime['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa suất chiếu">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="admin-empty d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>Chưa có suất chiếu nào. Hãy thêm suất chiếu đầu tiên.</span>
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
function renderShowtimeFormFields($prefix, $movies_list, $rooms_list, $showtime = null) {
?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Phim <span class="text-danger">*</span></label>
            <select class="form-select" name="movie_id" id="<?= $prefix ?>_movie_id" required>
                <option value="">-- Chọn phim --</option>
                <?php foreach ($movies_list as $movie): ?>
                    <option value="<?= $movie['id'] ?>" data-duration="<?= (int)$movie['duration'] ?>" <?= (int)($showtime['movie_id'] ?? 0) === (int)$movie['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($movie['title']) ?> (<?= (int)$movie['duration'] ?> phút)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Phòng chiếu <span class="text-danger">*</span></label>
            <select class="form-select" name="room_id" id="<?= $prefix ?>_room_id" required>
                <option value="">-- Chọn phòng --</option>
                <?php foreach ($rooms_list as $room): ?>
                    <option value="<?= $room['id'] ?>" <?= (int)($showtime['room_id'] ?? 0) === (int)$room['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($room['theatre_name'] . ' - ' . $room['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ngày chiếu <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="show_date" id="<?= $prefix ?>_show_date" required value="<?= htmlspecialchars($showtime['show_date'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
            <input type="time" class="form-control" name="start_time" id="<?= $prefix ?>_start_time" required value="<?= htmlspecialchars(isset($showtime['start_time']) ? substr($showtime['start_time'], 0, 5) : '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Giờ kết thúc tự tính</label>
            <input type="text" class="form-control" id="<?= $prefix ?>_end_time_preview" value="<?= htmlspecialchars(isset($showtime['end_time']) ? substr($showtime['end_time'], 0, 5) : '') ?>" readonly>
            <div class="form-text">Hệ thống tự cộng thời lượng phim vào giờ bắt đầu.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Giá vé cơ bản (VNĐ) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="base_price" id="<?= $prefix ?>_base_price" min="1000" step="1000" value="<?= htmlspecialchars((string)($showtime['base_price'] ?? 80000)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Trạng thái</label>
            <select class="form-select" name="status" id="<?= $prefix ?>_status">
                <option value="active" <?= ($showtime['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Đang mở</option>
                <option value="canceled" <?= ($showtime['status'] ?? '') === 'canceled' ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </div>
    </div>
    <script>
    (function () {
        const movieSelect = document.getElementById('<?= $prefix ?>_movie_id');
        const startInput = document.getElementById('<?= $prefix ?>_start_time');
        const endPreview = document.getElementById('<?= $prefix ?>_end_time_preview');

        function updateEndTimePreview() {
            const selectedMovie = movieSelect.options[movieSelect.selectedIndex];
            const duration = selectedMovie ? parseInt(selectedMovie.getAttribute('data-duration') || '0', 10) : 0;
            const startTime = startInput.value;

            if (!duration || !startTime) {
                endPreview.value = '';
                return;
            }

            const parts = startTime.split(':').map(Number);
            const totalMinutes = ((parts[0] * 60) + parts[1] + duration + 5) % (24 * 60);
            const hours = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
            const minutes = String(totalMinutes % 60).padStart(2, '0');
            endPreview.value = hours + ':' + minutes;
        }

        movieSelect.addEventListener('change', updateEndTimePreview);
        startInput.addEventListener('input', updateEndTimePreview);
        updateEndTimePreview();
    })();
    </script>
<?php } ?>

<?php require_once 'admin_footer.php'; ?>
