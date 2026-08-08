<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function readRequestPayload(): array {
    $raw = file_get_contents('php://input');
    if ($raw === '') {
        return $_POST;
    }

    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    $parsed = [];
    parse_str($raw, $parsed);
    return is_array($parsed) ? $parsed : [];
}

function sendJson($payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

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
$id = isset($segments[1]) ? (int)$segments[1] : 0;
$input = readRequestPayload();

if ($method === 'GET' && $resource === 'health') {
    sendJson(['status' => 'success', 'message' => 'API is running']);
}

if ($method === 'POST' && $resource === 'login') {
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    $authService = new App\Services\AuthService();
    $result = $authService->login($email, $password);

    sendJson($result);
}

if ($method === 'POST' && $resource === 'register') {
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

    sendJson($result);
}

if ($resource === 'movies') {
    $movieService = new App\Services\MovieService();

    if ($method === 'GET') {
        if ($id > 0) {
            $movie = $movieService->getMovieById($id);
            if ($movie) {
                sendJson(['status' => 'success', 'data' => $movie]);
            }
            sendJson(['status' => 'error', 'message' => 'Phim không tồn tại'], 404);
        }

        sendJson(['status' => 'success', 'data' => $movieService->getAllMovies()]);
    }

    if ($method === 'POST') {
        $genres = $input['genre_ids'] ?? $input['genres'] ?? [];
        $genreIds = is_array($genres) ? array_map('intval', $genres) : [];

        $data = [
            'title' => trim($input['title'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'director' => trim($input['director'] ?? ''),
            'cast' => trim($input['cast'] ?? ''),
            'age_restriction' => (int)($input['age_restriction'] ?? 0),
            'country' => trim($input['country'] ?? ''),
            'duration' => (int)($input['duration'] ?? 0),
            'screening_date' => trim($input['screening_date'] ?? ''),
            'trailer_url' => trim($input['trailer_url'] ?? ''),
            'status' => $input['status'] ?? 'coming'
        ];

        $result = $movieService->addMovie($data, $genreIds, null);
        sendJson($result, $result['status'] === 'success' ? 201 : 400);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID phim không hợp lệ'], 400);
        }

        $genres = $input['genre_ids'] ?? $input['genres'] ?? [];
        $genreIds = is_array($genres) ? array_map('intval', $genres) : [];

        $data = [
            'title' => trim($input['title'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'director' => trim($input['director'] ?? ''),
            'cast' => trim($input['cast'] ?? ''),
            'age_restriction' => (int)($input['age_restriction'] ?? 0),
            'country' => trim($input['country'] ?? ''),
            'duration' => (int)($input['duration'] ?? 0),
            'screening_date' => trim($input['screening_date'] ?? ''),
            'trailer_url' => trim($input['trailer_url'] ?? ''),
            'status' => $input['status'] ?? 'coming'
        ];

        $result = $movieService->updateMovie($id, $data, $genreIds, null);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }

    if ($method === 'DELETE') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID phim không hợp lệ'], 400);
        }

        $result = $movieService->deleteMovie($id);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }
}

if ($resource === 'genres') {
    $genreService = new App\Services\GenreService();

    if ($method === 'GET') {
        if ($id > 0) {
            $genre = $genreService->getGenreById($id);
            if ($genre) {
                sendJson(['status' => 'success', 'data' => $genre]);
            }
            sendJson(['status' => 'error', 'message' => 'Thể loại không tồn tại'], 404);
        }
        sendJson(['status' => 'success', 'data' => $genreService->getAllGenres()]);
    }

    if ($method === 'POST') {
        $result = $genreService->addGenre(trim($input['name'] ?? ''), trim($input['description'] ?? ''));
        sendJson($result, $result['status'] === 'success' ? 201 : 400);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID thể loại không hợp lệ'], 400);
        }
        $result = $genreService->updateGenre($id, trim($input['name'] ?? ''), trim($input['description'] ?? ''));
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }

    if ($method === 'DELETE') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID thể loại không hợp lệ'], 400);
        }
        $result = $genreService->deleteGenre($id);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }
}

if ($resource === 'theatres') {
    $theatreService = new App\Services\TheatreService();

    if ($method === 'GET') {
        sendJson(['status' => 'success', 'data' => $theatreService->getAllTheatres()]);
    }

    if ($method === 'POST') {
        $data = [
            'name' => trim($input['name'] ?? ''),
            'address' => trim($input['address'] ?? ''),
            'city' => trim($input['city'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'total_screens' => (int)($input['total_screens'] ?? 1)
        ];
        $result = $theatreService->addTheatre($data);
        sendJson($result, $result['status'] === 'success' ? 201 : 400);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID rạp không hợp lệ'], 400);
        }
        $data = [
            'name' => trim($input['name'] ?? ''),
            'address' => trim($input['address'] ?? ''),
            'city' => trim($input['city'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'total_screens' => (int)($input['total_screens'] ?? 1)
        ];
        $result = $theatreService->updateTheatre($id, $data);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }

    if ($method === 'DELETE') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID rạp không hợp lệ'], 400);
        }
        $result = $theatreService->deleteTheatre($id);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }
}

if ($resource === 'rooms') {
    $roomService = new App\Services\RoomService();

    if ($method === 'GET') {
        sendJson(['status' => 'success', 'data' => $roomService->getAllRooms()]);
    }

    if ($method === 'POST') {
        $data = [
            'theatre_id' => (int)($input['theatre_id'] ?? 0),
            'name' => trim($input['name'] ?? ''),
            'total_seats' => (int)($input['total_seats'] ?? 0),
            'is_active' => !empty($input['is_active'])
        ];
        $result = $roomService->addRoom($data);
        sendJson($result, $result['status'] === 'success' ? 201 : 400);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID phòng không hợp lệ'], 400);
        }
        $data = [
            'theatre_id' => (int)($input['theatre_id'] ?? 0),
            'name' => trim($input['name'] ?? ''),
            'total_seats' => (int)($input['total_seats'] ?? 0),
            'is_active' => !empty($input['is_active'])
        ];
        $result = $roomService->updateRoom($id, $data);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }

    if ($method === 'DELETE') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID phòng không hợp lệ'], 400);
        }
        $result = $roomService->deleteRoom($id);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }
}

if ($resource === 'showtimes') {
    $showtimeService = new App\Services\ShowtimeService();

    if ($method === 'GET') {
        if ($id > 0) {
            $showtime = $showtimeService->getShowtimeDetail($id);
            if ($showtime) {
                sendJson(['status' => 'success', 'data' => $showtime]);
            }
            sendJson(['status' => 'error', 'message' => 'Suất chiếu không tồn tại'], 404);
        }
        sendJson(['status' => 'success', 'data' => $showtimeService->getAllShowtimes()]);
    }

    if ($method === 'POST') {
        $data = [
            'movie_id' => (int)($input['movie_id'] ?? 0),
            'room_id' => (int)($input['room_id'] ?? 0),
            'show_date' => trim($input['show_date'] ?? ''),
            'start_time' => trim($input['start_time'] ?? ''),
            'base_price' => (float)($input['base_price'] ?? 0),
            'status' => $input['status'] ?? 'active'
        ];
        $result = $showtimeService->addShowtime($data);
        sendJson($result, $result['status'] === 'success' ? 201 : 400);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID suất chiếu không hợp lệ'], 400);
        }
        $data = [
            'movie_id' => (int)($input['movie_id'] ?? 0),
            'room_id' => (int)($input['room_id'] ?? 0),
            'show_date' => trim($input['show_date'] ?? ''),
            'start_time' => trim($input['start_time'] ?? ''),
            'base_price' => (float)($input['base_price'] ?? 0),
            'status' => $input['status'] ?? 'active'
        ];
        $result = $showtimeService->updateShowtime($id, $data);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }

    if ($method === 'DELETE') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID suất chiếu không hợp lệ'], 400);
        }
        $result = $showtimeService->deleteShowtime($id);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }
}

if ($resource === 'users') {
    $userService = new App\Services\UserService();

    if ($method === 'GET') {
        if ($id > 0) {
            $user = $userService->getUserById($id);
            if ($user) {
                sendJson(['status' => 'success', 'data' => $user]);
            }
            sendJson(['status' => 'error', 'message' => 'Người dùng không tồn tại'], 404);
        }
        sendJson(['status' => 'success', 'data' => $userService->getAllUsers()]);
    }

    if ($method === 'POST') {
        $data = [
            'first_name' => trim($input['first_name'] ?? ''),
            'last_name' => trim($input['last_name'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'password' => $input['password'] ?? '',
            'birth_date' => trim($input['birth_date'] ?? ''),
            'role' => $input['role'] ?? 'user'
        ];
        $result = $userService->addUser($data);
        sendJson($result, $result['status'] === 'success' ? 201 : 400);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID người dùng không hợp lệ'], 400);
        }
        $data = [
            'first_name' => trim($input['first_name'] ?? ''),
            'last_name' => trim($input['last_name'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'password' => $input['password'] ?? '',
            'birth_date' => trim($input['birth_date'] ?? ''),
            'role' => $input['role'] ?? 'user'
        ];
        $result = $userService->updateUser($id, $data);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }

    if ($method === 'DELETE') {
        if ($id <= 0) {
            sendJson(['status' => 'error', 'message' => 'ID người dùng không hợp lệ'], 400);
        }
        $result = $userService->deleteUser($id);
        sendJson($result, $result['status'] === 'success' ? 200 : 400);
    }
}

sendJson(['status' => 'error', 'message' => 'Endpoint không tồn tại'], 404);
