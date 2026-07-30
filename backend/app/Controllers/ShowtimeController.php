<?php
namespace App\Controllers;

use App\Services\ShowtimeService;

class ShowtimeController {
    private $service;

    public function __construct() {
        $this->service = new ShowtimeService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'movie_id' => (int)($_POST['movie_id'] ?? 0),
                    'room_id' => (int)($_POST['room_id'] ?? 0),
                    'show_date' => trim($_POST['show_date'] ?? ''),
                    'start_time' => trim($_POST['start_time'] ?? ''),
                    'base_price' => (float)($_POST['base_price'] ?? 0),
                    'status' => $_POST['status'] ?? 'active',
                ];

                if ($action === 'add') {
                    return $this->service->addShowtime($data);
                }

                $id = (int)($_POST['id'] ?? 0);
                return $this->service->updateShowtime($id, $data);
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteShowtime($id);
            }
        }
        return null;
    }

    public function getAllShowtimes() {
        return $this->service->getAllShowtimes();
    }

    public function getShowtimeDetail($showtimeId) {
        return $this->service->getShowtimeDetail($showtimeId);
    }

    public function getShowtimesByMovieId($movieId) {
        return $this->service->getShowtimesByMovieId($movieId);
    }

    public function getAllMovies() {
        return $this->service->getAllMovies();
    }

    public function getAllRooms() {
        return $this->service->getAllRooms();
    }
}
