<?php
namespace App\Services;

use App\Models\ReviewModel;
use App\Models\MovieModel;

class ReviewService {
    private $model;
    private $movieModel;

    public function __construct() {
        $this->model = new ReviewModel();
        $this->movieModel = new MovieModel();
    }

    public function addReview($userId, $movieId, $rating, $comment) {
        $userId = (int)$userId;
        $movieId = (int)$movieId;
        $rating = (int)$rating;
        $comment = trim($comment);

        if ($userId <= 0) {
            return ['status' => 'error', 'message' => 'Vui lòng đăng nhập để đánh giá phim!'];
        }

        if ($movieId <= 0 || !$this->movieModel->getMovieByIdWithGenres($movieId)) {
            return ['status' => 'error', 'message' => 'Phim không hợp lệ!'];
        }

        if ($rating < 1 || $rating > 5) {
            return ['status' => 'error', 'message' => 'Vui lòng chọn số sao từ 1 đến 5!'];
        }

        if ($comment === '') {
            return ['status' => 'error', 'message' => 'Vui lòng nhập nội dung đánh giá!'];
        }

        if ($this->model->create([
            'user_id' => $userId,
            'movie_id' => $movieId,
            'rating' => $rating,
            'comment' => $comment
        ])) {
            return ['status' => 'success', 'message' => 'Gửi đánh giá thành công!'];
        }

        return ['status' => 'error', 'message' => 'Lỗi khi gửi đánh giá: ' . $this->model->getError()];
    }

    public function getReviewsByMovieId($movieId) {
        $movieId = (int)$movieId;
        if ($movieId <= 0) {
            return [];
        }
        return $this->model->getByMovieId($movieId);
    }

    public function getRatingSummary($movieId) {
        $movieId = (int)$movieId;
        if ($movieId <= 0) {
            return [
                'average_rating' => 0,
                'total_reviews' => 0
            ];
        }
        return $this->model->getRatingSummary($movieId);
    }
}
