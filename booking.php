<?php
require_once 'config.php';

use App\Controllers\ShowtimeController;
use App\Controllers\SeatController;
use App\Controllers\TicketController;
use App\Controllers\BookingController;

$showtimeController = new ShowtimeController();
$seatController = new SeatController();
$ticketController = new TicketController();
$bookingController = new BookingController();

// check session
if(!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập để đặt vé!'); window.location='login.php';</script>";
    exit;
}

$result = $bookingController->handleRequest();
if ($result) {
    if ($result['status'] === 'success') {
        $bookingId = $result['booking_id'] ?? '';
        $successMessage = json_encode($result['message'] . "\nMã đặt vé: #" . $bookingId);
        echo "
            <script>
                alert($successMessage);
                window.location.href = 'booking_history.php';
            </script>
        ";
        exit;
    }
    if (isset($result['page'])) {
        echo "
            <script>
                alert('{$result['message']}');
                window.location='{$result['page']}';
            </script>
        ";    
    } else {
        echo "
            <script>
                alert('{$result['message']}');
            </script>
        "; 
    }
    exit;
}

$showtimeId = (int)(
    $_GET['showtime_id']
    ??
    $_POST['showtime_id']
    ??
    0
);

if ($showtimeId <= 0) {
    header('Location: index.php');
    exit;
}

$showtime = $showtimeController->getShowtimeDetail($showtimeId);
if (!$showtime) {
    echo "<script>alert('Suất chiếu không tồn tại!'); window.location='index.php';</script>";
    exit;
}

$seats = $seatController->getSeatsByRoomId((int)$showtime['room_id']);
$bookedSeatIds = $ticketController->getBookedSeatIdsByShowtimeId($showtimeId);

$seatsByRow = [];
foreach ($seats as $seat) {
    $seatsByRow[$seat['seat_row']][] = $seat;
}

$poster = !empty($showtime['movie_poster']) ? $showtime['movie_poster'] : 'https://via.placeholder.com/400x600?text=No+Image';
$address = trim(($showtime['theatre_address'] ?? '') . ', ' . ($showtime['theatre_city'] ?? ''), ', ');
$basePrice = (float)$showtime['base_price'];
$vipPrice = (float)$showtime['base_price'] + 20000;


require_once 'header.php';
?>

<link rel="stylesheet" href="css/booking.css">

<div class="booking-page">
    <div class="container">
        <a href="movie_details.php?id=<?= (int)$showtime['movie_id'] ?>" class="btn btn-outline-light btn-sm mb-4">
            <i class="bi bi-arrow-left"></i> Quay lại chi tiết phim
        </a>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="booking-card booking-sidebar">
                    <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($showtime['movie_title']) ?>" onerror="this.src='https://via.placeholder.com/400x600?text=No+Image';">
                    <h4 class="mb-3"><?= htmlspecialchars($showtime['movie_title']) ?></h4>

                    <p class="booking-meta">
                        <i class="bi bi-geo-alt-fill"></i>
                        <strong><?= htmlspecialchars($showtime['theatre_name']) ?></strong>
                    </p>
                    <p class="booking-address"><?= htmlspecialchars($address ?: 'Chưa cập nhật địa chỉ') ?></p>

                    <p class="booking-meta">
                        <i class="bi bi-calendar-fill"></i>
                        <?= date('d/m/Y', strtotime($showtime['show_date'])) ?>
                    </p>
                    <p class="booking-meta">
                        <i class="bi bi-clock-fill"></i>
                        <?= date('H:i', strtotime($showtime['start_time'])) ?> - <?= date('H:i', strtotime($showtime['end_time'])) ?>
                    </p>
                    <p class="booking-meta">
                        <i class="bi bi-display-fill"></i>
                        <?= htmlspecialchars($showtime['room_name']) ?>
                    </p>

                    <div class="booking-summary">
                        <p class="booking-meta">
                            Giá vé cơ bản:
                            <span class="summary-value"><?= number_format($basePrice, 0, ',', '.') ?>đ</span>
                        </p>
                        <p class="booking-meta">
                            Giá vé VIP:
                            <span class="summary-value"><?= number_format($vipPrice, 0, ',', '.') ?>đ</span>
                        </p>
                        <p class="booking-meta">
                            Ghế đã chọn:
                            <span id="selected-seats" class="summary-value">Chưa chọn</span>
                        </p>
                        <p class="booking-meta">
                            Số ghế:
                            <span id="seat-count" class="summary-value">0</span>
                        </p>
                        <p class="booking-meta">
                            Tổng tiền:
                            <span id="total-price" class="summary-total">0đ</span>
                        </p>

                        <div class="mb-3">
                            <label class="form-label text-white fw-bold">Phương thức thanh toán</label>
                            <div class="d-grid gap-2">
                                <label class="payment-option">
                                    <input class="form-check-input me-2" type="radio" name="payment_method" value="momo" checked>
                                    Momo
                                </label>
                                <label class="payment-option">
                                    <input class="form-check-input me-2" type="radio" name="payment_method" value="vnpay">
                                    VNPay
                                </label>
                                <label class="payment-option">
                                    <input class="form-check-input me-2" type="radio" name="payment_method" value="bank_transfer">
                                    Chuyển khoản
                                </label>
                            </div>
                        </div>

                        <button id="btn-confirm" type="button" class="btn btn-danger btn-confirm-booking w-100" disabled>
                            <i class="bi bi-ticket-perforated-fill"></i> XÁC NHẬN ĐẶT VÉ
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h2 class="mb-4">Chọn Ghế Ngồi</h2>

                <div class="booking-card seat-legend-box">
                    <div class="row text-center g-3">
                        <div class="col-6 col-lg-3">
                            <button class="seat available" type="button" disabled></button>
                            <span class="text-secondary ms-2">Ghế trống</span>
                        </div>
                        <div class="col-6 col-lg-3">
                            <button class="seat vip" type="button" disabled></button>
                            <span class="text-secondary ms-2">Ghế VIP</span>
                        </div>
                        <div class="col-6 col-lg-3">
                            <button class="seat selected" type="button" disabled></button>
                            <span class="text-secondary ms-2">Ghế đang chọn</span>
                        </div>
                        <div class="col-6 col-lg-3">
                            <button class="seat booked" type="button" disabled></button>
                            <span class="text-secondary ms-2">Ghế đã đặt</span>
                        </div>
                    </div>
                </div>

                <div class="screen"></div>
                <p class="text-center text-secondary mb-4">MÀN HÌNH</p>

                <div id="seat-map" class="booking-card seat-map">
                    <?php if (!empty($seatsByRow)): ?>
                        <?php foreach ($seatsByRow as $row => $rowSeats): ?>
                            <?php
                            usort($rowSeats, function ($a, $b) {
                                return (int)$a['seat_number'] <=> (int)$b['seat_number'];
                            });
                            $leftSeats = array_filter($rowSeats, function ($seat) {
                                return (int)$seat['seat_number'] <= 6;
                            });
                            $rightSeats = array_filter($rowSeats, function ($seat) {
                                return (int)$seat['seat_number'] > 6;
                            });
                            ?>
                            <div class="seat-row">
                                <span class="row-label"><?= htmlspecialchars($row) ?></span>

                                <div class="seat-group">
                                    <?php foreach ($leftSeats as $seat): ?>
                                        <?php renderSeatButton($seat, $bookedSeatIds, $basePrice); ?>
                                    <?php endforeach; ?>
                                </div>

                                <span class="seat-aisle"></span>

                                <div class="seat-group">
                                    <?php foreach ($rightSeats as $seat): ?>
                                        <?php renderSeatButton($seat, $bookedSeatIds, $basePrice); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-secondary mb-0">Phòng chiếu này chưa có dữ liệu ghế.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function renderSeatButton($seat, $bookedSeatIds, $basePrice) {
    $seatId = (int)$seat['seat_id'];
    $seatName = $seat['seat_row'] . $seat['seat_number'];
    $isBooked = in_array($seatId, $bookedSeatIds, true);
    $isInactive = !(bool)$seat['is_active'];
    $price = (float)$basePrice + (float)$seat['seat_type_price'];
    $isVip = strtoupper($seat['seat_type_name']) === 'VIP';
    $class = $isBooked ? 'booked' : ($isInactive ? 'inactive' : 'available' . ($isVip ? ' vip' : ''));
    $disabled = ($isBooked || $isInactive) ? 'disabled' : '';

    echo '<button type="button" class="seat ' . $class . '" data-seat-id="' . $seatId . '" data-seat-name="' . htmlspecialchars($seatName) . '" data-price="' . $price . '" title="' . htmlspecialchars($seat['seat_type_name']) . '" ' . $disabled . '>' . (int)$seat['seat_number'] . '</button>';
}
?>

<script>
    const seatButtons = document.querySelectorAll('.seat.available');
    const seatCount = document.getElementById('seat-count');
    const selectedSeats = document.getElementById('selected-seats');
    const totalPrice = document.getElementById('total-price');
    const confirmButton = document.getElementById('btn-confirm');
    const formatter = new Intl.NumberFormat('vi-VN');

    function getSelectedSeatButtons() {
        return Array.from(document.querySelectorAll('.seat.selected[data-seat-id]'));
    }

    function updateSummary() {
        const selected = getSelectedSeatButtons();
        const names = selected.map((seat) => seat.dataset.seatName);
        const total = selected.reduce((sum, seat) => sum + Number(seat.dataset.price || 0), 0);

        seatCount.textContent = selected.length;
        selectedSeats.textContent = names.length ? names.join(', ') : 'Chưa chọn';
        totalPrice.textContent = formatter.format(total) + 'đ';
        confirmButton.disabled = selected.length === 0;
    }

    seatButtons.forEach((button) => {
        button.addEventListener('click', () => {
            button.classList.toggle('available');
            button.classList.toggle('selected');
            updateSummary();
        });
    });

    confirmButton.addEventListener('click', () => {
            const selected = getSelectedSeatButtons();
            if (!selected.length) {
                alert("Vui lòng chọn ghế");
                return;
            }

            if (!confirm("Bạn có chắc chắn muốn đặt vé không?")) {
                return;
            }

            const payment = document.querySelector(
                'input[name="payment_method"]:checked'
            );

            const form = document.createElement('form');
            form.method = "POST";
            form.action = "booking.php";
            // action
            let action = document.createElement('input');

            action.type = "hidden";
            action.name = "action";
            action.value = "book_ticket";

            form.appendChild(action);

            // showtime id
            let showtime = document.createElement('input');

            showtime.type = "hidden";
            showtime.name = "showtime_id";
            showtime.value = "<?= $showtimeId ?>";

            form.appendChild(showtime);

            // payment
            let paymentInput = document.createElement('input');

            paymentInput.type = "hidden";
            paymentInput.name = "payment_method";
            paymentInput.value = payment.value;

            form.appendChild(paymentInput);
            // selected seats
            selected.forEach(seat => {
                let input = document.createElement('input');
                input.type = "hidden";
                input.name = "seats[]";
                input.value = seat.dataset.seatId;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        });
</script>

<?php require_once 'footer.php'; ?>
