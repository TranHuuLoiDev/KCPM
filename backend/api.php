<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

$index = array_search('api.php', $segments);
if ($index !== false) {
    $segments = array_values(array_slice($segments, $index + 1));
}

$resource = $segments[0] ?? '';

if ($resource === 'movies') {
    $movieModel = new App\Models\MovieModel();
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $duration = (int)($input['duration'] ?? 0);

        if ($duration < 60 || $duration > 180) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Giá trị biên không hợp lệ: Thời lượng phim phải từ 60 đến 180 phút'
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Dữ liệu hợp lệ',
            'data' => ['duration' => $duration]
        ]);
        exit;
    }

    if ($method === 'GET') {
        if ($id > 0) {
            $movie = $movieModel->getMovieByIdWithGenres($id);
            if ($movie) {
                echo json_encode(['status' => 'success', 'data' => $movie]);
                exit;
            }
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Phim không tồn tại']);
            exit;
        }

        echo json_encode(['status' => 'success', 'data' => $movieModel->getAllMovies()]);
        exit;
    }
}

if ($resource === 'rooms') {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $totalSeats = (int)($input['total_seats'] ?? $input['capacity'] ?? 0);

        if ($totalSeats < 10 || $totalSeats > 200) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Giá trị biên không hợp lệ: Số lượng ghế phòng phải từ 10 đến 200'
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Dữ liệu phòng hợp lệ',
            'data' => ['total_seats' => $totalSeats]
        ]);
        exit;
    }
}

if ($resource === 'showtimes') {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $price = (float)($input['price'] ?? 0);

        if ($price < 50000 || $price > 200000) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Giá trị biên không hợp lệ: Giá vé phải từ 50,000 đến 200,000'
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Dữ liệu giá vé hợp lệ',
            'data' => ['price' => $price]
        ]);
        exit;
    }

    $showtimeModel = new App\Models\ShowtimeModel(); 
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    if ($id > 0) {
        $showtime = $showtimeModel->getShowtimeById($id);
        if ($showtime) {
            echo json_encode(['status' => 'success', 'data' => $showtime]);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Lịch chiếu không tồn tại']);
        exit;
    }

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

if ($resource === 'bookings') {
    if ($method === 'POST' && !isset($segments[2])) {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $userId = (int)($input['user_id'] ?? 1);
        $showtimeId = (int)($input['showtime_id'] ?? 0);
        $seatIds = $input['seat_ids'] ?? [];
        $paymentMethodId = $input['payment_method_id'] ?? $input['payment_method'] ?? 'momo';
        $totalPrice = (float)($input['total_price'] ?? 100000);

        if (!$showtimeId || empty($seatIds)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Vui lòng cung cấp showtime_id và seat_ids'
            ]);
            exit;
        }

        $bookingModel = new App\Models\BookingModel();
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

    if ($method === 'POST' && isset($segments[2]) && $segments[2] === 'cancel') {
        $bookingId = (int)$segments[1];
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $userId = (int)($input['user_id'] ?? 1);

        $bookingModel = new App\Models\BookingModel();
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

    if ($method === 'GET' && !isset($segments[1])) {
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;
        $bookingModel = new App\Models\BookingModel();
        $bookings = $bookingModel->getBookingsByUser($userId);

        echo json_encode(['status' => 'success', 'data' => $bookings]);
        exit;
    }

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

if ($resource === 'admin') {
    $subResource = $segments[1] ?? '';

    if ($subResource === 'bookings') {
        $bookingModel = new App\Models\BookingModel();

        if (isset($segments[2]) && $segments[2] === 'stats') {
            $stats = $bookingModel->getAdminBookingStats();
            echo json_encode(['status' => 'success', 'data' => $stats]);
            exit;
        }

        if (isset($segments[2]) && is_numeric($segments[2])) {
            $bookingId = (int)$segments[2];
            $detail = $bookingModel->getAdminBookingDetail($bookingId);

            if ($detail) {
                $detail['tickets'] = $bookingModel->getAdminBookingTickets($bookingId);
                echo json_encode(['status' => 'success', 'data' => $detail]);
                exit;
            }

            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }

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

http_response_code(404);
echo json_encode(['status' => 'error', 'message' => 'Endpoint không tồn tại']);