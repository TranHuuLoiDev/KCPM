<?php
namespace App\Models;

use App\Config\Database;

class TheatreModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM theatres WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function insert($data) {
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO theatres (name, address, city, phone, total_screens) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $data['name'],
            $data['address'],
            $data['city'],
            $data['phone'],
            $data['total_screens']
        );
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $data) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE theatres SET name = ?, address = ?, city = ?, phone = ?, total_screens = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssssii",
            $data['name'],
            $data['address'],
            $data['city'],
            $data['phone'],
            $data['total_screens'],
            $id
        );
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM theatres WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function getAll() {
        $query = "
            SELECT t.*, COUNT(r.id) AS room_count
            FROM theatres t
            LEFT JOIN rooms r ON r.theatre_id = t.id
            GROUP BY t.id
            ORDER BY t.id DESC
        ";
        $result = mysqli_query($this->conn, $query);
        $theatres = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $theatres[] = $row;
            }
        }
        return $theatres;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }
}
