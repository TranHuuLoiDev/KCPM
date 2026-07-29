<?php
namespace App\Models;

use App\Config\Database;

class GenreModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findByName($name, $excludeId = null) {
        if ($excludeId) {
            $stmt = mysqli_prepare($this->conn, "SELECT id FROM genres WHERE name = ? AND id != ?");
            mysqli_stmt_bind_param($stmt, "si", $name, $excludeId);
        } else {
            $stmt = mysqli_prepare($this->conn, "SELECT id FROM genres WHERE name = ?");
            mysqli_stmt_bind_param($stmt, "s", $name);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function insert($name, $description) {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO genres (name, description) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $name, $description);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $name, $description) {
        $stmt = mysqli_prepare($this->conn, "UPDATE genres SET name = ?, description = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM genres WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function getAll() {
        $query = "SELECT * FROM genres ORDER BY id DESC";
        $result = mysqli_query($this->conn, $query);
        $genres = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $genres[] = $row;
            }
        }
        return $genres;
    }

    public function getById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM genres WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    public function getError() {
        return mysqli_error($this->conn);
    }
}
