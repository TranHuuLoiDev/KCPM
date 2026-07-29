<?php
require_once 'config.php';

use App\Controllers\BookingController;

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$bookingController = new BookingController();
$actionResult = $bookingController->handleRequest();

$userId = (int)($_SESSION['user']['id'] ?? 0);
$bookings = $bookingController->getUserBookings($userId);
$fullName = trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''));
$email = $_SESSION['user']['email'] ?? '';

function bookingStatusMeta($status) {
    if ($status === 'paid') {
        return [
            'class' => 'confirmed',
            'text' => 'Đã xác nhận',
            'icon' => 'bi-check-circle-fill'
        ];
    }

    if ($status === 'canceled') {
        return [
            'class' => 'canceled',
            'text' => 'Đã hủy',
            'icon' => 'bi-x-circle-fill'
        ];
    }

    return [
        'class' => 'pending',
        'text' => 'Chờ xác nhận',
        'icon' => 'bi-hourglass-split'
    ];
}

function canCancelBooking($booking) {
    if (($booking['status'] ?? '') === 'canceled') {
        return false;
    }

    if (empty($booking['show_date']) || empty($booking['start_time'])) {
        return false;
    }

    $showDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $booking['show_date'] . ' ' . $booking['start_time']);
    if (!$showDateTime) {
        return false;
    }

    return $showDateTime > new DateTime();
}

require_once 'header.php';
?>

<link rel="stylesheet" href="css/profile.css">
<link rel="stylesheet" href="css/booking_history.css">

<div class="profile-page booking-history-page">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <aside class="profile-card p-4 text-center">
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($fullName ?: 'Người dùng') ?></h4>
                    <p class="profile-muted small mb-0"><?= htmlspecialchars($email) ?></p>
                </aside>

                <nav class="profile-card profile-menu p-3 mt-4 d-grid gap-2">
                    <a href="profile.php" class="btn">
                        <i class="bi bi-person-circle"></i>
                        <span>Thông tin cá nhân</span>
                    </a>
                    <a href="booking_history.php" class="btn active">
                        <i class="bi bi-ticket-perforated-fill"></i>
                        <span>Lịch sử đặt vé</span>
                    </a>
                    <a href="logout.php" class="btn btn-profile-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất</span>
                    </a>
                </nav>
            </div>

            <div class="col-lg-9">
                <div class="booking-history-heading d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <h2><i class="bi bi-ticket-perforated-fill"></i> Lịch Sử Đặt Vé</h2>
                    <a href="index.php#movies-list" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left"></i>
                        <span>Tiếp tục đặt vé</span>
                    </a>
                </div>

                <?php if ($actionResult): ?>
                    <?php $alertClass = $actionResult['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?>
                    <div class="alert <?= $alertClass ?> d-flex align-items-center gap-2" role="alert">
                        <i class="bi <?= $actionResult['status'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
                        <span><?= htmlspecialchars($actionResult['message']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($bookings)): ?>
                    <div class="booking-history-list">
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $statusMeta = bookingStatusMeta($booking['status'] ?? 'pending');
                            $poster = !empty($booking['movie_poster']) ? $booking['movie_poster'] : 'https://via.placeholder.com/300x450?text=No+Image';
                            $address = trim(($booking['theatre_address'] ?? '') . ', ' . ($booking['theatre_city'] ?? ''), ', ');
                            $showDate = !empty($booking['show_date']) ? date('d/m/Y', strtotime($booking['show_date'])) : 'Chưa cập nhật';
                            $startTime = !empty($booking['start_time']) ? date('H:i', strtotime($booking['start_time'])) : '--:--';
                            $endTime = !empty($booking['end_time']) ? date('H:i', strtotime($booking['end_time'])) : '';
                            $timeRange = $endTime !== '' ? $startTime . ' - ' . $endTime : $startTime;
                            ?>

                            <article class="booking-history-card status-<?= htmlspecialchars($statusMeta['class']) ?>">
                                <div class="row g-4 align-items-stretch">
                                    <div class="col-md-3 col-xl-2">
                                        <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($booking['movie_title'] ?? 'Movie poster') ?>" class="booking-history-poster" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image';">
                                    </div>

                                    <div class="col-md-6 col-xl-7">
                                        <div class="booking-history-info">
                                            <h3><?= htmlspecialchars($booking['movie_title'] ?? 'Phim không xác định') ?></h3>

                                            <p>
                                                <i class="bi bi-upc-scan"></i>
                                                <strong>Mã đặt vé:</strong>
                                                <span>#<?= (int)$booking['id'] ?></span>
                                            </p>
                                            <p>
                                                <i class="bi bi-geo-alt-fill"></i>
                                                <strong><?= htmlspecialchars($booking['theatre_name'] ?? 'Rạp chưa cập nhật') ?></strong>
                                            </p>
                                            <p class="booking-history-address"><?= htmlspecialchars($address ?: 'Chưa cập nhật địa chỉ') ?></p>
                                            <p>
                                                <i class="bi bi-calendar-event-fill"></i>
                                                <span><?= htmlspecialchars($showDate) ?></span>
                                                <i class="bi bi-clock-fill ms-3"></i>
                                                <span><?= htmlspecialchars($timeRange) ?></span>
                                            </p>
                                            <p>
                                                <i class="bi bi-display-fill"></i>
                                                <strong>Phòng:</strong>
                                                <span><?= htmlspecialchars($booking['room_name'] ?? 'Chưa cập nhật') ?></span>
                                            </p>
                                            <p>
                                                <i class="bi bi-grid-3x3-gap-fill"></i>
                                                <strong>Ghế:</strong>
                                                <span><?= htmlspecialchars($booking['seats'] ?? 'Chưa có ghế') ?></span>
                                            </p>
                                            <p class="mb-0">
                                                <i class="bi bi-calendar-check-fill"></i>
                                                <strong>Thời gian đặt:</strong>
                                                <span><?= !empty($booking['created_at']) ? date('d/m/Y H:i', strtotime($booking['created_at'])) : 'Chưa cập nhật' ?></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="booking-history-actions">
                                            <div class="booking-history-total">
                                                <span>Thành tiền</span>
                                                <strong><?= number_format((float)($booking['total_price'] ?? 0), 0, ',', '.') ?>đ</strong>
                                            </div>

                                            <div class="booking-history-status">
                                                <i class="bi <?= htmlspecialchars($statusMeta['icon']) ?>"></i>
                                                <span><?= htmlspecialchars($statusMeta['text']) ?></span>
                                            </div>

                                            <?php if (canCancelBooking($booking)): ?>
                                                <form method="POST" action="booking_history.php" onsubmit="return confirm('Bạn có chắc muốn hủy vé này?');">
                                                    <input type="hidden" name="action" value="cancel_booking">
                                                    <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                                    <button type="submit" class="btn btn-cancel-booking w-100">
                                                        <i class="bi bi-x-lg"></i>
                                                        <span>Hủy vé</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <section class="booking-history-empty">
                        <i class="bi bi-ticket-perforated"></i>
                        <h3>Chưa có vé nào</h3>
                        <p>Hãy đặt vé xem phim yêu thích của bạn ngay.</p>
                        <a href="index.php#movies-list" class="btn">
                            <i class="bi bi-search"></i>
                            <span>Khám phá phim</span>
                        </a>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
