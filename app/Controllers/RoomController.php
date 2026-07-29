<?php
namespace App\Controllers;

use App\Services\RoomService;

class RoomController {
    private $service;

    public function __construct() {
        $this->service = new RoomService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'theatre_id' => (int)($_POST['theatre_id'] ?? 0),
                    'name' => trim($_POST['name'] ?? ''),
                    'total_seats' => (int)($_POST['total_seats'] ?? 0),
                    'is_active' => isset($_POST['is_active']),
                ];

                if ($action === 'add') {
                    return $this->service->addRoom($data);
                }

                $id = (int)($_POST['id'] ?? 0);
                return $this->service->updateRoom($id, $data);
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteRoom($id);
            }
        }
        return null;
    }

    public function getAllRooms() {
        return $this->service->getAllRooms();
    }

    public function getAllTheatres() {
        return $this->service->getAllTheatres();
    }
}
