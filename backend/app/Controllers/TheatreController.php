<?php
namespace App\Controllers;

use App\Services\TheatreService;

class TheatreController {
    private $service;

    public function __construct() {
        $this->service = new TheatreService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'city' => trim($_POST['city'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'total_screens' => (int)($_POST['total_screens'] ?? 1),
                ];

                if ($action === 'add') {
                    return $this->service->addTheatre($data);
                }

                $id = (int)($_POST['id'] ?? 0);
                return $this->service->updateTheatre($id, $data);
            }

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteTheatre($id);
            }
        }
        return null;
    }

    public function getAllTheatres() {
        return $this->service->getAllTheatres();
    }
}
