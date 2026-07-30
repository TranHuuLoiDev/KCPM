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
    echo json_encode(['status' => 'success', 'data' => $movieModel->getAllMovies()]);
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

echo json_encode(['status' => 'error', 'message' => 'Endpoint không tồn tại']);
