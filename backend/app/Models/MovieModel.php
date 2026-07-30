<?php
namespace App\Models;

use App\Config\Database;

class MovieModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAllMovies() {
        $query = "SELECT id, title, duration, status FROM movies ORDER BY title ASC";
        $result = mysqli_query($this->conn, $query);
        $movies = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $movies[] = $row;
            }
        }
        return $movies;
    }

    public function insertMovie($data) {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO movies (title, description, director, cast, age_restriction, country, duration, screening_date, poster, trailer_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssissssss", 
            $data['title'], $data['description'], $data['director'], $data['cast'], 
            $data['age_restriction'], $data['country'], $data['duration'], 
            $data['screening_date'], $data['poster'], $data['trailer_url'], $data['status']
        );
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }

    public function updateMovie($id, $data) {
        $stmt = mysqli_prepare($this->conn, "UPDATE movies SET title=?, description=?, director=?, cast=?, age_restriction=?, country=?, duration=?, screening_date=?, poster=?, trailer_url=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssissssssi", 
            $data['title'], $data['description'], $data['director'], $data['cast'], 
            $data['age_restriction'], $data['country'], $data['duration'], 
            $data['screening_date'], $data['poster'], $data['trailer_url'], $data['status'], $id
        );
        return mysqli_stmt_execute($stmt);
    }

    public function deleteMovie($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM movies WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function insertMovieGenres($movieId, $genreIds) {
        if (empty($genreIds)) return;
        $genre_stmt = mysqli_prepare($this->conn, "INSERT INTO movie_genre (movie_id, genre_id) VALUES (?, ?)");
        if ($genre_stmt) {
            foreach ($genreIds as $g_id) {
                mysqli_stmt_bind_param($genre_stmt, "ii", $movieId, $g_id);
                mysqli_stmt_execute($genre_stmt);
            }
        }
    }

    public function deleteMovieGenres($movieId) {
        $del_genre = mysqli_prepare($this->conn, "DELETE FROM movie_genre WHERE movie_id = ?");
        mysqli_stmt_bind_param($del_genre, "i", $movieId);
        return mysqli_stmt_execute($del_genre);
    }

    public function getAllMoviesWithGenres() {
        $query_movies = "
            SELECT m.*, GROUP_CONCAT(g.id) as genre_ids, GROUP_CONCAT(g.name SEPARATOR ', ') as genre_names
            FROM movies m
            LEFT JOIN movie_genre mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            GROUP BY m.id
            ORDER BY m.created_at DESC
        ";
        $result = mysqli_query($this->conn, $query_movies);
        $movies = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $movies[] = $row;
            }
        }
        return $movies;
    }

    public function getMovieByIdWithGenres($id) {
        $query = "
            SELECT m.*, 
                   GROUP_CONCAT(g.id) as genre_ids,
                   GROUP_CONCAT(g.name SEPARATOR ', ') as genre_names
            FROM movies m
            LEFT JOIN movie_genre mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            WHERE m.id = ?
            GROUP BY m.id
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }

    public function getNowShowingMovies($limit = null) {
        return $this->getMoviesByStatus('now_showing', 'DESC', $limit);
    }

    public function getComingMovies($limit = null) {
        return $this->getMoviesByStatus('coming', 'ASC', $limit);
    }

    private function getMoviesByStatus($status, $sortDirection, $limit = null) {
        $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';
        $limit = $limit !== null ? (int)$limit : null;

        $query = "
            SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') AS genre_names
            FROM movies m
            LEFT JOIN movie_genre mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            WHERE m.status = ?
              AND m.is_active = 1
            GROUP BY m.id
            ORDER BY m.screening_date {$sortDirection}, m.id DESC
        ";

        if ($limit !== null && $limit > 0) {
            $query .= " LIMIT ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $status, $limit);
        } else {
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $status);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $movies = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $movies[] = $row;
            }
        }
        return $movies;
    }

    public function getTotalMovies() {
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as count FROM movies");
        if ($result) {
            return mysqli_fetch_assoc($result)['count'];
        }
        return 0;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }
}
