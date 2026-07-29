<?php
namespace App\Controllers;

use App\Services\ReviewService;

class ReviewController {
    private $service;

    public function __construct() {
        $this->service = new ReviewService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add_review') {
                $userId = (int)($_SESSION['user']['id'] ?? 0);
                $movieId = (int)($_POST['movie_id'] ?? 0);
                $rating = (int)($_POST['rating'] ?? 0);
                $comment = trim($_POST['comment'] ?? '');

                return $this->service->addReview($userId, $movieId, $rating, $comment);
            }
        }
        return null;
    }

    public function addReview($userId, $movieId, $rating, $comment) {
        return $this->service->addReview($userId, $movieId, $rating, $comment);
    }

    public function getReviewsByMovieId($movieId) {
        return $this->service->getReviewsByMovieId($movieId);
    }

    public function getRatingSummary($movieId) {
        return $this->service->getRatingSummary($movieId);
    }
}
