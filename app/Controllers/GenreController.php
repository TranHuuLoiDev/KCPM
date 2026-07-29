<?php
namespace App\Controllers;

use App\Services\GenreService;

class GenreController {
    private $service;

    public function __construct() {
        $this->service = new GenreService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add') {
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                return $this->service->addGenre($name, $description);
            } 
            elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                return $this->service->updateGenre($id, $name, $description);
            } 
            elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->service->deleteGenre($id);
            }
        }
        return null;
    }

    public function getAllGenres() {
        return $this->service->getAllGenres();
    }

    public function getGenreById($id) {
        return $this->service->getGenreById($id);
    }
}
