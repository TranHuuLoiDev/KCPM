<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\BookingController;

$controller = new BookingController();
$actionResult = $controller->handleAdminRequest();

$success_msg = '';
$error_msg = '';

if ($actionResult) {
    if ($actionResult['status'] === 'success') {
        $success_msg = $actionResult['message'];
    } else {
        $error_msg = $actionResult['message'];
    }
}

$filters = [
    'status' => $_GET['status'] ?? '',
    'from_date' => $_GET['from_date'] ?? '',
    'to_date' => $_GET['to_date'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$stats = $controller->getAdminBookingStats();
$bookings = $controller->getAdminBookings($filters);
$detailId = isset($_GET['detail_id']) ? (int)$_GET['detail_id'] : 0;
$bookingDetail = $detailId > 0 ? $controller->getAdminBookingDetail($detailId) : null;

$queryParams = array_filter($filters, function ($value) {
    return trim((string)$value) !== '';
});
$formAction = 'manage_booking.php' . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');

if ($detailId > 0 && !$bookingDetail && !$error_msg) {
    $error_msg = 'Không tìm thấy booking cần xem chi tiết.';
}
?>

<div class="container-fluid admin-booking-page">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý đặt vé</h1>
            <p class="mb-0 mt-2 text-muted">Theo dõi đơn đặt vé, trạng thái thanh toán, lịch chiếu và thao tác xử lý booking của khách hàng.</p>
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

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="admin-card d-flex align-items-center h-100" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-ticket-perforated-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= (int)$stats['total'] ?></h3>
                    <p class="mb-0 text-white-50">Tổng đặt vé</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="admin-card d-flex align-items-center h-100" style="background: linear-gradient(135deg, #4CAF50, #388E3C);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= (int)$stats['paid'] ?></h3>
                    <p class="mb-0 text-white-50">Đã xác nhận</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
            <div class="admin-card d-flex align-items-center h-100" style="background: linear-gradient(135deg, #f44336, #d32f2f);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-x-circle-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= (int)$stats['canceled'] ?></h3>
                    <p class="mb-0 text-white-50">Đã hủy</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-card d-flex align-items-center h-100" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                <div class="fs-1 me-4 text-white"><i class="bi bi-calendar-day-fill"></i></div>
                <div>
                    <h3 class="mb-1 text-white fw-bold"><?= (int)$stats['today'] ?></h3>
                    <p class="mb-0 text-white-50">Hôm nay</p>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card mb-4">
        <form method="GET" action="manage_booking.php" class="row g-3 align-items-end">
            <div class="col-xl-2 col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="paid" <?= ($filters['status'] === 'paid') ? 'selected' : '' ?>>Đã xác nhận</option>
                    <option value="canceled" <?= ($filters['status'] === 'canceled') ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-4">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($filters['from_date']) ?>">
            </div>
            <div class="col-xl-2 col-md-4">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($filters['to_date']) ?>">
            </div>
            <div class="col-xl-4 col-md-8">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Mã vé, tên KH, email, SĐT, phim, rạp..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-xl-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-netflix-red flex-fill">
                    <i class="bi bi-search me-1"></i>Tìm
                </button>
                <a href="manage_booking.php" class="btn btn-admin-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <h5 class="mb-0 text-white"><i class="bi bi-list-ul me-2"></i>Danh sách đặt vé</h5>
            <span class="text-muted small"><?= count($bookings) ?> kết quả</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm admin-table admin-booking-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Mã vé</th>
                        <th>Khách hàng</th>
                        <th>Phim</th>
                        <th>Rạp / Phòng</th>
                        <th>Ngày đặt</th>
                        <th>Suất chiếu</th>
                        <th>Ghế</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="11">
                                <div class="admin-empty d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-ticket-perforated"></i>
                                    <span>Không tìm thấy booking phù hợp.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><strong>#<?= (int)$booking['id'] ?></strong></td>
                                <td>
                                    <div class="fw-bold text-white">
                                        <?= htmlspecialchars(trim($booking['first_name'] . ' ' . $booking['last_name'])) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($booking['email']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($booking['phone']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($booking['movie_title'] ?: 'Chưa có dữ liệu') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($booking['theatre_name'] ?: 'Chưa có dữ liệu') ?></strong>
                                    <br><span class="text-muted"><?= htmlspecialchars($booking['room_name'] ?: 'Chưa có dữ liệu') ?></span>
                                </td>
                                <td><?= bookingAdminFormatDateTime($booking['created_at']) ?></td>
                                <td>
                                    <?= bookingAdminFormatDate($booking['show_date']) ?>
                                    <br><span class="text-muted"><?= bookingAdminFormatTime($booking['start_time']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($booking['seats'] ?: 'N/A') ?></span>
                                </td>
                                <td><strong class="text-success"><?= number_format((float)$booking['total_price'], 0, ',', '.') ?> đ</strong></td>
                                <td><?= htmlspecialchars(bookingAdminPaymentLabel($booking['payment_method'])) ?></td>
                                <td>
                                    <form action="<?= htmlspecialchars($formAction) ?>" method="POST" class="m-0">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                        <select name="status" class="form-select form-select-sm admin-booking-status-select <?= bookingAdminStatusSelectClass($booking['status']) ?>" onchange="this.form.submit()" aria-label="Cập nhật trạng thái booking #<?= (int)$booking['id'] ?>">
                                            <option value="pending" <?= ($booking['status'] === 'pending') ? 'selected' : '' ?>>Chờ xử lý</option>
                                            <option value="paid" <?= ($booking['status'] === 'paid') ? 'selected' : '' ?>>Đã xác nhận</option>
                                            <option value="canceled" <?= ($booking['status'] === 'canceled') ? 'selected' : '' ?>>Đã hủy</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $detailParams = $queryParams;
                                    $detailParams['detail_id'] = (int)$booking['id'];
                                    $detailUrl = 'manage_booking.php?' . http_build_query($detailParams);
                                    ?>
                                    <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn btn-sm btn-outline-info admin-icon-btn me-1" title="Xem chi tiết booking">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="<?= htmlspecialchars($formAction) ?>" method="POST" class="d-inline" onsubmit="return confirm('Xóa vĩnh viễn booking #<?= (int)$booking['id'] ?>? Vé liên quan cũng sẽ bị xóa.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa booking">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($bookingDetail): ?>
    <?php
    $detailBooking = $bookingDetail['booking'];
    $detailTickets = $bookingDetail['tickets'];
    ?>
    <div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-labelledby="bookingDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <div>
                        <h5 class="modal-title" id="bookingDetailModalLabel">
                            <i class="bi bi-ticket-perforated me-2"></i>Chi tiết booking #<?= (int)$detailBooking['id'] ?>
                        </h5>
                        <p class="mb-0 text-muted small">Ngày đặt: <?= bookingAdminFormatDateTime($detailBooking['created_at']) ?></p>
                    </div>
                    <a href="<?= htmlspecialchars($formAction) ?>" class="btn-close btn-close-white" aria-label="Đóng"></a>
                </div>
                <div class="modal-body">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-4">
                            <div class="admin-card h-100">
                                <h6 class="text-white mb-3"><i class="bi bi-person-circle me-2"></i>Khách hàng</h6>
                                <div class="fw-bold text-white mb-2">
                                    <?= htmlspecialchars(trim($detailBooking['first_name'] . ' ' . $detailBooking['last_name'])) ?>
                                </div>
                                <div class="text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($detailBooking['email']) ?></div>
                                <div class="text-muted mb-1"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($detailBooking['phone']) ?></div>
                                <div class="text-muted"><i class="bi bi-calendar-heart me-2"></i><?= bookingAdminFormatDate($detailBooking['birth_date']) ?></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="admin-card h-100">
                                <h6 class="text-white mb-3"><i class="bi bi-receipt me-2"></i>Thanh toán</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Trạng thái</span>
                                    <?= bookingAdminStatusBadge($detailBooking['status']) ?>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phương thức</span>
                                    <strong><?= htmlspecialchars(bookingAdminPaymentLabel($detailBooking['payment_method'])) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Số vé</span>
                                    <strong><?= (int)$detailBooking['ticket_count'] ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Tổng tiền</span>
                                    <strong class="text-success"><?= number_format((float)$detailBooking['total_price'], 0, ',', '.') ?> đ</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="admin-card h-100">
                                <h6 class="text-white mb-3"><i class="bi bi-camera-reels me-2"></i>Suất chiếu</h6>
                                <div class="fw-bold text-white mb-2"><?= htmlspecialchars($detailBooking['movie_title'] ?: 'Chưa có dữ liệu') ?></div>
                                <div class="text-muted mb-1"><i class="bi bi-building me-2"></i><?= htmlspecialchars($detailBooking['theatre_name'] ?: 'Chưa có dữ liệu') ?></div>
                                <div class="text-muted mb-1"><i class="bi bi-door-open me-2"></i><?= htmlspecialchars($detailBooking['room_name'] ?: 'Chưa có dữ liệu') ?></div>
                                <div class="text-muted">
                                    <i class="bi bi-clock me-2"></i><?= bookingAdminFormatDate($detailBooking['show_date']) ?>,
                                    <?= bookingAdminFormatTime($detailBooking['start_time']) ?> - <?= bookingAdminFormatTime($detailBooking['end_time']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 text-white"><i class="bi bi-grid me-2"></i>Danh sách vé</h6>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($detailBooking['seats'] ?: 'N/A') ?></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm admin-table admin-booking-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã vé</th>
                                        <th>Phim</th>
                                        <th>Rạp / Phòng</th>
                                        <th>Suất chiếu</th>
                                        <th>Ghế</th>
                                        <th>Loại ghế</th>
                                        <th>Giá</th>
                                        <th>Trạng thái vé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($detailTickets)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Booking này chưa có vé.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($detailTickets as $ticket): ?>
                                            <tr>
                                                <td><strong>#<?= (int)$ticket['ticket_id'] ?></strong></td>
                                                <td><?= htmlspecialchars($ticket['movie_title']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($ticket['theatre_name']) ?></strong>
                                                    <br><span class="text-muted"><?= htmlspecialchars($ticket['room_name']) ?></span>
                                                </td>
                                                <td>
                                                    <?= bookingAdminFormatDate($ticket['show_date']) ?>
                                                    <br><span class="text-muted">
                                                        <?= bookingAdminFormatTime($ticket['start_time']) ?> - <?= bookingAdminFormatTime($ticket['end_time']) ?>
                                                    </span>
                                                </td>
                                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($ticket['seat_row'] . $ticket['seat_number']) ?></span></td>
                                                <td><?= htmlspecialchars($ticket['seat_type_name'] ?: 'N/A') ?></td>
                                                <td><strong class="text-success"><?= number_format((float)$ticket['price'], 0, ',', '.') ?> đ</strong></td>
                                                <td><?= bookingAdminTicketStatusBadge($ticket['ticket_status']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <a href="<?= htmlspecialchars($formAction) ?>" class="btn btn-admin-secondary">Đóng</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var detailModal = new bootstrap.Modal(document.getElementById('bookingDetailModal'));
            detailModal.show();
        });
    </script>
<?php endif; ?>

<?php
function bookingAdminPaymentLabel($paymentMethod) {
    $labels = [
        'cash' => 'Tiền mặt',
        'momo' => 'MoMo',
        'vnpay' => 'VNPay',
        'bank_transfer' => 'Chuyển khoản'
    ];

    return $labels[$paymentMethod] ?? $paymentMethod;
}

function bookingAdminStatusSelectClass($status) {
    if ($status === 'paid') {
        return 'border-success';
    }

    if ($status === 'canceled') {
        return 'border-danger';
    }

    return 'border-warning';
}

function bookingAdminStatusBadge($status) {
    if ($status === 'paid') {
        return '<span class="badge bg-success">Đã xác nhận</span>';
    }

    if ($status === 'canceled') {
        return '<span class="badge bg-danger">Đã hủy</span>';
    }

    return '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
}

function bookingAdminTicketStatusBadge($status) {
    if ($status === 'booked') {
        return '<span class="badge bg-success">Đã đặt</span>';
    }

    if ($status === 'used') {
        return '<span class="badge bg-primary">Đã sử dụng</span>';
    }

    return '<span class="badge bg-danger">Đã hủy</span>';
}

function bookingAdminFormatDateTime($value) {
    if (empty($value)) {
        return '<span class="text-muted">N/A</span>';
    }

    return date('d/m/Y H:i', strtotime($value));
}

function bookingAdminFormatDate($value) {
    if (empty($value)) {
        return '<span class="text-muted">N/A</span>';
    }

    return date('d/m/Y', strtotime($value));
}

function bookingAdminFormatTime($value) {
    if (empty($value)) {
        return '<span class="text-muted">N/A</span>';
    }

    return date('H:i', strtotime($value));
}

require_once 'admin_footer.php';
?>
