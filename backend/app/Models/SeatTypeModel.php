<?php
namespace App\Models;

use App\Config\Database;

class SeatTypeModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM seat_types ORDER BY price ASC, name ASC";
        $result = mysqli_query($this->conn, $query);
        $types = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $types[] = $row;
            }
        }
        return $types;
    }

    public function findById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM seat_types WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
}
