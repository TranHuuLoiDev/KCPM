<?php
namespace App\Controllers;

use App\Services\MovieService;
use App\Services\GenreService;

class MovieController {
    private $movieService;
    private $genreService;

    public function __construct() {
        $this->movieService = new MovieService();
        $this->genreService = new GenreService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add' || $action === 'edit') {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'director' => trim($_POST['director'] ?? ''),
                    'cast' => trim($_POST['cast'] ?? ''),
                    'age_restriction' => (int)($_POST['age_restriction'] ?? 0),
                    'country' => trim($_POST['country'] ?? ''),
                    'duration' => (int)($_POST['duration'] ?? 0),
                    'screening_date' => trim($_POST['screening_date'] ?? ''),
                    'trailer_url' => trim($_POST['trailer_url'] ?? ''),
                    'status' => $_POST['status'] ?? 'coming'
                ];
                $genres = $_POST['genres'] ?? [];
                $posterFile = $_FILES['poster'] ?? null;

                if ($action === 'add') {
                    return $this->movieService->addMovie($data, $genres, $posterFile);
                } else {
                    $id = (int)($_POST['id'] ?? 0);
                    return $this->movieService->updateMovie($id, $data, $genres, $posterFile);
                }
            } 
            elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                return $this->movieService->deleteMovie($id);
            }
        }
        return null;
    }

    public function getAllMovies() {
        return $this->movieService->getAllMovies();
    }

    public function getMovieById($id) {
        return $this->movieService->getMovieById($id);
    }

    public function getNowShowingMovies($limit = null) {
        return $this->movieService->getNowShowingMovies($limit);
    }

    public function getComingMovies($limit = null) {
        return $this->movieService->getComingMovies($limit);
    }

    public function getAllGenres() {
        return $this->genreService->getAllGenres();
    }
}
