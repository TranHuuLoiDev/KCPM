<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function sendJson($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getJsonInput() {
    $rawInput = file_get_contents('php://input');
    if ($rawInput === false || trim($rawInput) === '') {
        return $_POST;
    }

    $decoded = json_decode($rawInput, true);
    return is_array($decoded) ? $decoded : [];
}

function getRouteSegments() {
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

    if (($segments[0] ?? '') === 'api') {
        array_shift($segments);
    }

    return $segments;
}

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$segments = getRouteSegments();
$resource = $segments[0] ?? '';

if ($resource === '' || $resource === 'health') {
    sendJson([
        'status' => 'success',
        'message' => 'Movie ticket booking API is ready.',
        'endpoints' => [
            'GET /api/health',
            'POST /api/auth',
            'POST /api/register',
            'GET /api/movies',
            'GET /api/movies/{id}',
            'GET /api/showtimes',
            'GET /api/showtimes/{id}',
            'GET /api/bookings',
            'POST /api/bookings',
            'POST /api/bookings/{id}/cancel'
        ]
    ]);
}

if (($resource === 'auth' || $resource === 'login') && $method === 'POST') {
    $input = getJsonInput();
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    $authService = new App\Services\AuthService();
    $result = $authService->login($email, $password);

    sendJson($result, ($result['status'] ?? '') === 'success' ? 200 : 401);
}

if ($resource === 'register' && $method === 'POST') {
    $input = getJsonInput();

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

    sendJson($result, ($result['status'] ?? '') === 'success' ? 201 : 400);
}

if ($resource === 'movies' && $method === 'GET') {
    $movieModel = new App\Models\MovieModel();
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    if ($id > 0) {
        $movie = $movieModel->getMovieByIdWithGenres($id);
        if ($movie) {
            sendJson(['status' => 'success', 'data' => $movie]);
        }
        sendJson(['status' => 'error', 'message' => 'Phim không tồn tại'], 404);
    }

    $jsonFile = __DIR__ . '/data/movies.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        if (is_array($jsonData)) {
            sendJson(['status' => 'success', 'data' => $jsonData['movies'] ?? $jsonData]);
        }
    }

    sendJson(['status' => 'success', 'data' => $movieModel->getAllMoviesWithGenres()]);
}

if ($resource === 'showtimes' && $method === 'GET') {
    $showtimeModel = new App\Models\ShowtimeModel();
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    if ($id > 0) {
        $showtime = $showtimeModel->getDetailById($id);
        if ($showtime) {
            sendJson(['status' => 'success', 'data' => $showtime]);
        }
        sendJson(['status' => 'error', 'message' => 'Suất chiếu không tồn tại'], 404);
    }

    $movieId = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
    if ($movieId > 0) {
        sendJson(['status' => 'success', 'data' => $showtimeModel->getByMovieId($movieId)]);
    }

    sendJson(['status' => 'success', 'data' => []]);
}

if ($resource === 'bookings') {
    $userId = (int)($_SESSION['user']['id'] ?? 0);
    if ($userId <= 0) {
        sendJson(['status' => 'error', 'message' => 'Vui lòng đăng nhập để tiếp tục'], 401);
    }

    $bookingService = new App\Services\BookingService();

    if ($method === 'GET') {
        sendJson(['status' => 'success', 'data' => $bookingService->getUserBookings($userId)]);
    }

    if ($method === 'POST') {
        $bookingId = isset($segments[1]) ? (int)$segments[1] : 0;
        if ($bookingId > 0 && ($segments[2] ?? '') === 'cancel') {
            $result = $bookingService->cancelBooking($userId, $bookingId);
            sendJson($result, ($result['status'] ?? '') === 'success' ? 200 : 400);
        }

        $input = getJsonInput();
        $showtimeId = (int)($input['showtime_id'] ?? 0);
        $seatIds = $input['seat_ids'] ?? $input['seats'] ?? [];
        $paymentMethod = trim((string)($input['payment_method'] ?? 'cash'));

        $result = $bookingService->processBooking($userId, $showtimeId, $seatIds, $paymentMethod);
        sendJson($result, ($result['status'] ?? '') === 'success' ? 200 : 400);
    }
}

if ($resource === 'users' && $method === 'GET') {
    $userController = new App\Controllers\UserController();
    $id = isset($segments[1]) ? (int)$segments[1] : 0;

    if ($id > 0) {
        $user = $userController->getUserById($id);
        if ($user) {
            sendJson(['status' => 'success', 'data' => $user]);
        }
        sendJson(['status' => 'error', 'message' => 'Người dùng không tồn tại'], 404);
    }

    sendJson(['status' => 'success', 'data' => $userController->getAllUsers()]);
}

sendJson(['status' => 'error', 'message' => 'Endpoint không tồn tại'], 404);
