<?php

namespace App\Models;

use App\Config\Database;


class ReviewModel {

    private $conn;


    public function __construct() {
        $this->conn = Database::getConnection();
    }



    public function getByMovieId($movieId) {
        $query = "
            SELECT r.id, r.user_id, r.movie_id, r.rating, r.comment, r.created_at,
                   u.first_name, u.last_name
            FROM reviews r
            INNER JOIN users u ON u.id = r.user_id
            WHERE r.movie_id = ?
            ORDER BY r.created_at DESC
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $movieId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $reviews = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $reviews[] = $row;
            }
        }
        return $reviews;
    }

    public function getRatingSummary($movieId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ROUND(AVG(rating), 1) AS average_rating, COUNT(id) AS total_reviews FROM reviews WHERE movie_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $movieId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return [
            'average_rating' => $row && $row['average_rating'] !== null ? (float)$row['average_rating'] : 0,
            'total_reviews' => $row ? (int)$row['total_reviews'] : 0
        ];
    }

    public function create($data) {
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO reviews (user_id, movie_id, rating, comment) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "iiis",
            $data['user_id'],
            $data['movie_id'],
            $data['rating'],
            $data['comment']
        );
        return mysqli_stmt_execute($stmt);
    }

    public function getError() {
        return mysqli_error($this->conn);
    }
}
