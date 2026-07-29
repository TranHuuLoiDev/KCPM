<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\SeatController;

echo '<link rel="stylesheet" href="../css/admin-seat.css">';

$controller = new SeatController();
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

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$show_row = isset($_GET['show_row']) ? strtoupper(trim($_GET['show_row'])) : '';
$rooms_list = $controller->getAllRooms();
$seat_types_list = $controller->getAllSeatTypes();

$current_room = null;
if ($room_id > 0) {
    foreach ($rooms_list as $room) {
        if ((int) $room['id'] === $room_id) {
            $current_room = $room;
            break;
        }
    }
    if (!$current_room) {
        header('Location: manage_seats.php');
        exit;
    }
}

function buildSeatsByRow(array $seats): array
{
    $grouped = [];
    foreach ($seats as $seat) {
        $grouped[$seat['seat_row']][] = $seat;
    }
    foreach ($grouped as &$rowSeats) {
        usort($rowSeats, function ($a, $b) {
            return (int) $a['seat_number'] <=> (int) $b['seat_number'];
        });
    }
    unset($rowSeats);
    return $grouped;
}

function getAdminSeatClass(array $seat): string
{
    if (empty($seat['is_active'])) {
        return 'inactive';
    }
    $type = strtoupper(trim($seat['seat_type_name']));
    if ($type === 'VIP') {
        return 'vip';
    }
    return 'available';
}

function getRankPickerClass(string $typeName): string
{
    $name = strtoupper(trim($typeName));
    if ($name === 'VIP') {
        return 'rank-vip';
    }
    return 'rank-regular';
}

function renderAdminSeatButton(array $seat): void
{
    $label = htmlspecialchars($seat['seat_row'] . $seat['seat_number']);
    $class = getAdminSeatClass($seat);
    ?>
    <button
        type="button"
        class="seat <?= $class ?>"
        title="Ghế <?= $label ?>"
        data-seat='<?= htmlspecialchars(json_encode($seat, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
        onclick="selectSeat(this)"
    ><?= $label ?></button>
    <?php
}

function renderAdminSeatRow(string $rowLabel, array $rowSeats, string $formAction, int $roomId): void
{
    $leftSeats = array_values(array_filter($rowSeats, function ($seat) {
        return (int) $seat['seat_number'] <= 6;
    }));
    $rightSeats = array_values(array_filter($rowSeats, function ($seat) {
        return (int) $seat['seat_number'] > 6;
    }));
    ?>
    <div class="seat-row">
        <span class="row-label"><?= htmlspecialchars($rowLabel) ?></span>

        <div class="seat-group">
            <?php foreach ($leftSeats as $seat): ?>
                <?php renderAdminSeatButton($seat); ?>
            <?php endforeach; ?>
        </div>

        <span class="seat-aisle"></span>

        <div class="seat-group">
            <?php foreach ($rightSeats as $seat): ?>
                <?php renderAdminSeatButton($seat); ?>
            <?php endforeach; ?>

            <form method="POST" action="<?= htmlspecialchars($formAction) ?>" class="d-inline">
                <input type="hidden" name="action" value="quick_add">
                <input type="hidden" name="room_id" value="<?= $roomId ?>">
                <input type="hidden" name="seat_row" value="<?= htmlspecialchars($rowLabel) ?>">
                <button type="submit" class="seat-add" title="Thêm ghế hàng <?= htmlspecialchars($rowLabel) ?>">+</button>
            </form>
        </div>
    </div>
    <?php
}
?>

<div class="container-fluid">
    <?php if (!$current_room): ?>
        <div class="admin-page-header mb-4">
            <h1 class="mb-0 text-white fw-bold">Quản lý ghế</h1>
            <p class="mb-0 mt-2 text-muted">Chọn phòng chiếu để quản lý sơ đồ ghế.</p>
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

        <div class="row g-4">
            <?php if (!empty($rooms_list)): ?>
                <?php foreach ($rooms_list as $room): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="admin-card admin-room-card h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($room['theatre_name']) ?>
                                    </div>
                                    <h5 class="text-white mb-0"><?= htmlspecialchars($room['name']) ?></h5>
                                </div>
                                <?php if ($room['is_active']): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex flex-wrap gap-3 mb-4 text-muted small">
                                <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($room['theatre_city'] ?? 'Chưa cập nhật') ?></span>
                                <span><i class="bi bi-grid me-1"></i><?= (int) $room['seat_count'] ?> ghế</span>
                            </div>
                            <a href="manage_seats.php?room_id=<?= (int) $room['id'] ?>" class="btn btn-netflix-red w-100">
                                <i class="bi bi-grid-3x3-gap me-1"></i> Quản lý sơ đồ ghế
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="admin-card text-center py-5 text-muted">
                        <i class="bi bi-door-open fs-2 d-block mb-2"></i>
                        Chưa có phòng chiếu nào.
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <?php
        $seats_list = $controller->getAllSeats($room_id);
        $seats_by_row = buildSeatsByRow($seats_list);
        $display_rows = $controller->getDisplayRows($room_id, $show_row);
        $next_row = $controller->getNextRowLetter($display_rows);
        $room_query = 'manage_seats.php?room_id=' . $room_id;
        $form_action = $room_query . ($show_row !== '' ? '&show_row=' . urlencode($show_row) : '');
        ?>

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

        <div class="booking-page">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <a href="manage_seats.php" class="btn btn-outline-light btn-sm mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Danh sách phòng
                    </a>
                    <h1 class="mb-0 text-white fw-bold h3"><?= htmlspecialchars($current_room['name']) ?></h1>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#bulkGeneratePanel" aria-expanded="false" aria-controls="bulkGeneratePanel">
                        <i class="bi bi-plus-circle me-1"></i> Tạo hàng loạt
                    </button>
                    <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#bulkDeletePanel" aria-expanded="false" aria-controls="bulkDeletePanel">
                        <i class="bi bi-trash3 me-1"></i> Xóa hàng loạt
                    </button>
                </div>
            </div>

            <div class="collapse mb-4" id="bulkGeneratePanel">
                <div class="booking-card">
                    <h5 class="text-white mb-3"><i class="bi bi-plus-circle me-2"></i>Tạo ghế hàng loạt</h5>
                    <form action="<?= htmlspecialchars($form_action) ?>" method="POST">
                        <input type="hidden" name="action" value="generate">
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Hàng bắt đầu</label>
                                <select class="form-select" name="start_row">
                                    <?php foreach (range('A', 'H') as $row): ?>
                                        <option value="<?= $row ?>"><?= $row ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Hàng kết thúc</label>
                                <select class="form-select" name="end_row">
                                    <?php foreach (range('A', 'H') as $row): ?>
                                        <option value="<?= $row ?>" <?= $row === 'H' ? 'selected' : '' ?>><?= $row ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Ghế mỗi hàng</label>
                                <input type="number" class="form-control" name="seats_per_row" min="1" max="12" value="12" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Loại ghế</label>
                                <select class="form-select" name="seat_type_id" required>
                                    <?php foreach ($seat_types_list as $type): ?>
                                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-netflix-red">
                                <i class="bi bi-plus-lg me-1"></i>Tạo ghế
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="collapse mb-4" id="bulkDeletePanel">
                <div class="booking-card">
                    <h5 class="text-white mb-3"><i class="bi bi-trash3 me-2"></i>Xóa ghế hàng loạt</h5>
                    <form action="<?= htmlspecialchars($form_action) ?>" method="POST" onsubmit="return confirm('Xóa toàn bộ ghế trong khoảng đã chọn? Thao tác này cũng có thể ảnh hưởng vé liên quan đến các ghế đó.');">
                        <input type="hidden" name="action" value="bulk_delete">
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Hàng bắt đầu</label>
                                <select class="form-select" name="delete_start_row">
                                    <?php foreach (range('A', 'H') as $row): ?>
                                        <option value="<?= $row ?>"><?= $row ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Hàng kết thúc</label>
                                <select class="form-select" name="delete_end_row">
                                    <?php foreach (range('A', 'H') as $row): ?>
                                        <option value="<?= $row ?>" <?= $row === 'H' ? 'selected' : '' ?>><?= $row ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Số ghế bắt đầu</label>
                                <input type="number" class="form-control" name="delete_start_number" min="1" max="12" value="1" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Số ghế kết thúc</label>
                                <input type="number" class="form-control" name="delete_end_number" min="1" max="12" value="12" required>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash3 me-1"></i>Xóa ghế
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="booking-card booking-sidebar sticky-top" style="top: 24px;">
                        <div class="text-center py-4 mb-2 rounded" style="background: linear-gradient(135deg,#1a1a1a,#2d2d2d);">
                            <i class="bi bi-display fs-1 text-danger"></i>
                        </div>
                        <h4 class="mb-3 text-white"><?= htmlspecialchars($current_room['name']) ?></h4>

                        <p class="booking-meta">
                            <i class="bi bi-building"></i>
                            <strong><?= htmlspecialchars($current_room['theatre_name']) ?></strong>
                        </p>
                        <p class="booking-address"><?= htmlspecialchars($current_room['theatre_city'] ?? 'Chưa cập nhật') ?></p>

                        <p class="booking-meta">
                            <i class="bi bi-grid"></i>
                            <?= count($seats_list) ?> ghế đã tạo
                        </p>
                        <p class="booking-meta">
                            <i class="bi bi-layers"></i>
                            <?= count($display_rows) ?> hàng ghế
                        </p>

                        <div class="booking-summary">
                            <div id="seatEditorPlaceholder" class="text-muted text-center py-3">
                                <i class="bi bi-hand-index-thumb fs-4 d-block mb-2"></i>
                                Chọn ghế trên sơ đồ để chỉnh sửa
                            </div>

                            <div id="seatEditorForm" class="d-none">
                                <p class="booking-meta mb-1">
                                    Đang chỉnh sửa:
                                    <span id="editorPosition" class="summary-value">A1</span>
                                </p>

                                <form method="POST" action="<?= htmlspecialchars($form_action) ?>" id="seatEditForm">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" id="editor_id">
                                    <input type="hidden" name="room_id" value="<?= $room_id ?>">
                                    <input type="hidden" name="seat_row" id="editor_row">
                                    <input type="hidden" name="seat_number" id="editor_number">
                                    <input type="hidden" name="is_active" id="editor_is_active" value="1">
                                    <input type="hidden" name="seat_type_id" id="editor_seat_type_id">

                                    <label class="form-label text-white fw-bold mt-3">Trạng thái ghế</label>
                                    <div class="admin-seat-picker-group mb-3" id="statusPickerGroup">
                                        <button type="button" class="admin-seat-picker" onclick="setSeatStatus(1, this)">
                                            <span class="admin-seat-picker-swatch status-active"></span>
                                            <span>Hoạt động</span>
                                            <i class="bi bi-check-circle-fill admin-seat-picker-check"></i>
                                        </button>
                                        <button type="button" class="admin-seat-picker" onclick="setSeatStatus(0, this)">
                                            <span class="admin-seat-picker-swatch status-inactive"></span>
                                            <span>Tạm ngưng</span>
                                            <i class="bi bi-check-circle-fill admin-seat-picker-check"></i>
                                        </button>
                                    </div>

                                    <label class="form-label text-white fw-bold">Hạng ghế</label>
                                    <div class="admin-seat-picker-group mb-3" id="rankPickerGroup">
                                        <?php foreach ($seat_types_list as $type): ?>
                                            <button type="button" class="admin-seat-picker" data-type-id="<?= (int) $type['id'] ?>" onclick="setSeatRank(<?= (int) $type['id'] ?>, this)">
                                                <span class="admin-seat-picker-swatch <?= getRankPickerClass($type['name']) ?>"></span>
                                                <span>
                                                    <?= htmlspecialchars($type['name']) ?>
                                                    <small class="d-block text-muted">+<?= number_format((float) $type['price'], 0, ',', '.') ?> đ</small>
                                                </span>
                                                <i class="bi bi-check-circle-fill admin-seat-picker-check"></i>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="submit" class="btn btn-danger w-100 mb-2">
                                        <i class="bi bi-check2 me-1"></i>Lưu thay đổi
                                    </button>
                                </form>

                                <form method="POST" action="<?= htmlspecialchars($form_action) ?>" onsubmit="return confirm('Xóa ghế này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" id="editor_delete_id">
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-trash me-1"></i>Xóa ghế
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <h2 class="mb-4 text-white">Sơ đồ ghế phòng chiếu</h2>

                    <div class="booking-card seat-legend-box mb-4">
                        <div class="row g-3">
                            <div class="col-6 col-lg-4">
                                <div class="seat-legend-item">
                                    <button class="seat available seat-legend-seat" type="button" disabled></button>
                                    <span class="seat-legend-label">Thường</span>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="seat-legend-item">
                                    <button class="seat vip seat-legend-seat" type="button" disabled></button>
                                    <span class="seat-legend-label">VIP</span>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="seat-legend-item">
                                    <button class="seat status-active seat-legend-seat" type="button" disabled></button>
                                    <span class="seat-legend-label">Hoạt động</span>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="seat-legend-item">
                                    <button class="seat inactive seat-legend-seat" type="button" disabled></button>
                                    <span class="seat-legend-label">Tạm ngưng</span>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="seat-legend-item">
                                    <button class="seat selected seat-legend-seat" type="button" disabled></button>
                                    <span class="seat-legend-label">Đang chọn</span>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4">
                                <div class="seat-legend-item">
                                    <button class="seat seat-add-demo seat-legend-seat" type="button" disabled>+</button>
                                    <span class="seat-legend-label">Thêm ghế</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="screen"></div>
                    <p class="text-center text-secondary mb-4">MÀN HÌNH</p>

                    <div class="seat-map booking-card">
                        <?php if (!empty($display_rows)): ?>
                            <?php foreach ($display_rows as $rowLabel): ?>
                                <?php renderAdminSeatRow($rowLabel, $seats_by_row[$rowLabel] ?? [], $form_action, $room_id); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-secondary mb-3">Chưa có hàng ghế. Bấm + bên dưới để thêm hàng đầu tiên.</p>
                        <?php endif; ?>

                        <?php if ($next_row): ?>
                            <div class="seat-add-row-bar">
                                <span class="line"></span>
                                <a href="<?= htmlspecialchars($room_query . '&show_row=' . urlencode($next_row)) ?>" class="seat-add text-decoration-none" title="Thêm hàng <?= htmlspecialchars($next_row) ?>">+</a>
                                <span class="line"></span>
                            </div>
                            <p class="text-center text-secondary small mb-0 mt-2">
                                Thêm hàng mới<?= !empty($display_rows) ? ' (' . htmlspecialchars($next_row) . ')' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <p class="text-muted small mt-3">
                        Mỗi hàng có lối đi giữa (sau ghế số 6). Bấm <strong>+</strong> ở cuối hàng để thêm ghế tiếp theo.
                    </p>
                </div>
            </div>
        </div>

        <script>
        let selectedSeatButton = null;

        function selectSeat(button) {
            const seat = JSON.parse(button.getAttribute('data-seat'));

            if (selectedSeatButton) {
                selectedSeatButton.classList.remove('selected');
            }

            selectedSeatButton = button;
            button.classList.add('selected');

            document.getElementById('seatEditorPlaceholder').classList.add('d-none');
            document.getElementById('seatEditorForm').classList.remove('d-none');

            document.getElementById('editorPosition').textContent = seat.seat_row + seat.seat_number;
            document.getElementById('editor_id').value = seat.id;
            document.getElementById('editor_delete_id').value = seat.id;
            document.getElementById('editor_row').value = seat.seat_row;
            document.getElementById('editor_number').value = seat.seat_number;
            document.getElementById('editor_is_active').value = seat.is_active == 1 ? '1' : '0';
            document.getElementById('editor_seat_type_id').value = seat.seat_type_id;

            highlightPicker(
                document.querySelectorAll('#statusPickerGroup .admin-seat-picker'),
                (el, index) => (seat.is_active == 1 ? index === 0 : index === 1)
            );

            highlightPicker(
                document.querySelectorAll('#rankPickerGroup .admin-seat-picker'),
                (el) => el.getAttribute('data-type-id') === String(seat.seat_type_id)
            );
        }

        function setSeatStatus(value, button) {
            document.getElementById('editor_is_active').value = String(value);
            highlightPicker(document.querySelectorAll('#statusPickerGroup .admin-seat-picker'), (el) => el === button);
        }

        function setSeatRank(typeId, button) {
            document.getElementById('editor_seat_type_id').value = String(typeId);
            highlightPicker(document.querySelectorAll('#rankPickerGroup .admin-seat-picker'), (el) => el === button);
        }

        function highlightPicker(nodeList, matcher) {
            nodeList.forEach((el, index) => {
                el.classList.toggle('is-active', matcher(el, index));
            });
        }
        </script>
    <?php endif; ?>
</div>

<?php require_once 'admin_footer.php'; ?>
