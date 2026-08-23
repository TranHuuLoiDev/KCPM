<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$segments = array_values(array_filter(explode('/', $path), static function ($segment) {
    return $segment !== '';
}));

if (($segments[0] ?? '') === 'movie-ticket-booking') {
    array_shift($segments);
}

if (($segments[0] ?? '') === 'backend') {
    array_shift($segments);
}

if (($segments[0] ?? '') === 'api.php') {
    array_shift($segments);
}

$resource = $segments[0] ?? '';

if ($method === 'GET' && $resource === 'movies') {
    $movieModel = new App\Models\MovieModel();

    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    // Nếu có truyền ID (vd: /movies/2) -> Lấy chi tiết 1 phim
    if ($id > 0) {
        $movie = $movieModel->getMovieByIdWithGenres($id);
        if ($movie) {
            echo json_encode(['status' => 'success', 'data' => $movie]);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Phim không tồn tại']);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $movieModel->getAllMovies()]);
    exit;
}

if ($method === 'GET' && $resource === 'showtimes') {
    $showtimeModel = new App\Models\ShowtimeModel(); // Kiểm tra đúng tên Model trong project của bạn
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    // Trường hợp 1: Lấy chi tiết 1 lịch chiếu theo ID (vd: GET /showtimes/1)
    if ($id > 0) {
        $showtime = $showtimeModel->getShowtimeById($id); // Hoặc tên hàm tương đương trong Model
        if ($showtime) {
            echo json_encode(['status' => 'success', 'data' => $showtime]);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Lịch chiếu không tồn tại']);
        exit;
    }

    // Trường hợp 2: Lấy danh sách lịch chiếu (có hỗ trợ lọc theo movie_id nếu truyền ?movie_id=1)
    $movieId = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
    if ($movieId > 0) {
        $showtimes = $showtimeModel->getByMovieId($movieId);
    } else {
        $showtimes = $showtimeModel->getAllWithDetails();
    }

    echo json_encode(['status' => 'success', 'data' => $showtimes]);
    exit;
}

if ($method === 'POST' && $resource === 'login') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    $authService = new App\Services\AuthService();
    $result = $authService->login($email, $password);

    echo json_encode($result);
    exit;
}

if ($method === 'POST' && $resource === 'register') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $authService = new App\Services\AuthService();
    $result = $authService->register([
        'first_name' => trim($input['first_name'] ?? ''),
        'last_name' => trim($input['last_name'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'phone' => trim($input['phone'] ?? ''),
        'birth_date' => trim($input['birth_date'] ?? ''),
        'password' => $input['password'] ?? '',
        'confirm_password' => $input['confirm_password'] ?? ''
    ]);

    echo json_encode($result);
    exit;
}

if ($method === 'GET' && $resource === 'users') {
    $userController = new App\Controllers\UserController();
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    if ($id > 0) {
        $user = $userController->getUserById($id);
        if ($user) {
            echo json_encode(['status' => 'success', 'data' => $user]);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Người dùng không tồn tại']);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $userController->getAllUsers()]);
    exit;
}

// BVA AUTOMATION - SEAT VALIDATION
if (
    $method === 'POST'
    && $resource === 'seats'
    && ($segments[1] ?? '') === 'validate'
) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $seatService = new App\Services\SeatService();

    $result = $seatService->validateSeatInput([
        'room_id' => (int)($input['room_id'] ?? 0),
        'seat_row' => trim($input['seat_row'] ?? ''),
        'seat_number' => (int)($input['seat_number'] ?? 0),
        'seat_type_id' => (int)($input['seat_type_id'] ?? 0),
        'is_active' => (bool)($input['is_active'] ?? true)
    ]);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// BVA AUTOMATION - ROOM VALIDATION
if (
    $method === 'POST'
    && $resource === 'rooms'
    && ($segments[1] ?? '') === 'validate'
) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $roomService = new App\Services\RoomService();

    $result = $roomService->validateRoomInput([
        'theatre_id' => (int)($input['theatre_id'] ?? 0),
        'name' => trim($input['name'] ?? ''),
        'total_seats' => (int)($input['total_seats'] ?? 0),
        'is_active' => (bool)($input['is_active'] ?? true)
    ]);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

//1. ĐẶT VÉ (POST /bookings)
if ($resource === 'bookings') {

    if ($method === 'POST' && !isset($segments[2])) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $userId = (int)($input['user_id'] ?? 1); // Mặc định ID user test là 1 nếu chưa truyền
        $showtimeId = (int)($input['showtime_id'] ?? 0);
        $seatIds = $input['seat_ids'] ?? [];
        $paymentMethodId = $input['payment_method_id'] ?? $input['payment_method'] ?? 'momo';
        $totalPrice = (float)($input['total_price'] ?? 100000); // Giá định danh hoặc tính toán

        if (!$showtimeId || empty($seatIds)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Vui lòng cung cấp showtime_id và seat_ids'
            ]);
            exit;
        }

        $bookingModel = new App\Models\BookingModel();

        // Gọi hàm createBooking đúng 3 tham số ($userId, $totalPrice, $paymentMethod) theo BookingModel
        $bookingId = $bookingModel->createBooking($userId, $totalPrice, $paymentMethodId);

        if ($bookingId) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Đặt vé thành công',
                'data' => [
                    'booking_id' => $bookingId,
                    'user_id' => $userId,
                    'total_price' => $totalPrice,
                    'payment_method_id' => $paymentMethodId
                ]
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Đặt vé thất bại: ' . $bookingModel->getError()
        ]);
        exit;
    }

    // 2. HỦY ĐƠN ĐẶT VÉ (POST /bookings/{id}/cancel)

    if ($method === 'POST' && isset($segments[2]) && $segments[2] === 'cancel') {
        $bookingId = (int)$segments[1];

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $userId = (int)($input['user_id'] ?? 1); // Lấy userId tương ứng đơn hàng

        $bookingModel = new App\Models\BookingModel();

        // Gọi hàm cancelBooking đúng 2 tham số ($bookingId, $userId) theo BookingModel
        $cancelled = $bookingModel->cancelBooking($bookingId, $userId);

        if ($cancelled) {
            echo json_encode(['status' => 'success', 'message' => 'Hủy vé thành công']);
            exit;
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'Hủy vé thất bại (Đơn hàng không tồn tại hoặc đã bị hủy từ trước)'
        ]);
        exit;
    }

    // 3. LẤY DANH SÁCH BOOKING CỦA USER (GET /bookings?user_id=1)

    if ($method === 'GET' && !isset($segments[1])) {
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;

        $bookingModel = new App\Models\BookingModel();
        $bookings = $bookingModel->getBookingsByUser($userId);

        echo json_encode(['status' => 'success', 'data' => $bookings]);
        exit;
    }


    //4. LẤY CHI TIẾT 1 BOOKING (GET /bookings/{id})

    if ($method === 'GET' && isset($segments[1])) {
        $bookingId = (int)$segments[1];

        $bookingModel = new App\Models\BookingModel();
        $booking = $bookingModel->getById($bookingId);

        if ($booking) {
            echo json_encode(['status' => 'success', 'data' => $booking]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn đặt vé']);
        exit;
    }
}


// 👑 ADMIN BOOKING MANAGEMENT

if ($resource === 'admin') {
    $subResource = $segments[1] ?? '';

    if ($subResource === 'bookings') {
        $bookingModel = new App\Models\BookingModel();

        // 1. Thống kê Booking (GET /admin/bookings/stats)
        if (isset($segments[2]) && $segments[2] === 'stats') {
            $stats = $bookingModel->getAdminBookingStats();
            echo json_encode(['status' => 'success', 'data' => $stats]);
            exit;
        }

        // 2. Chi tiết 1 Booking cho Admin (GET /admin/bookings/{id})
        if (isset($segments[2]) && is_numeric($segments[2])) {
            $bookingId = (int)$segments[2];
            $detail = $bookingModel->getAdminBookingDetail($bookingId);

            if ($detail) {
                // Lấy thêm danh sách vé đi kèm đơn
                $detail['tickets'] = $bookingModel->getAdminBookingTickets($bookingId);
                echo json_encode(['status' => 'success', 'data' => $detail]);
                exit;
            }

            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }

        // 3. Lấy tất cả Booking cho Admin (GET /admin/bookings)
        if ($method === 'GET') {
            $filters = [
                'status'    => $_GET['status'] ?? '',
                'from_date' => $_GET['from_date'] ?? '',
                'to_date'   => $_GET['to_date'] ?? '',
                'search'    => $_GET['search'] ?? ''
            ];

            $bookings = $bookingModel->getAdminBookings($filters);
            echo json_encode(['status' => 'success', 'data' => $bookings]);
            exit;
        }
    }
}


echo json_encode(['status' => 'error', 'message' => 'Endpoint không tồn tại']);