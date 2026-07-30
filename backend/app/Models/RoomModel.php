<?php
namespace App\Models;

use App\Config\Database;

class RoomModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAllRooms() {
        $query = "
            SELECT r.id, r.name, r.is_active, t.name AS theatre_name
            FROM rooms r
            INNER JOIN theatres t ON t.id = r.theatre_id
            WHERE r.is_active = 1
            ORDER BY t.name ASC, r.name ASC
        ";
        $result = mysqli_query($this->conn, $query);
        $rooms = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rooms[] = $row;
            }
        }
        return $rooms;
    }
    public function findById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM rooms WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function findByName($name, $excludeId = null) {
        if ($excludeId) {
            $stmt = mysqli_prepare($this->conn, "SELECT id FROM rooms WHERE name = ? AND id != ?");
            mysqli_stmt_bind_param($stmt, "si", $name, $excludeId);
        } else {
            $stmt = mysqli_prepare($this->conn, "SELECT id FROM rooms WHERE name = ?");
            mysqli_stmt_bind_param($stmt, "s", $name);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function insert($data) {
        $isActive = $data['is_active'] ? 1 : 0;
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO rooms (theatre_id, name, total_seats, is_active) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "isii",
            $data['theatre_id'],
            $data['name'],
            $data['total_seats'],
            $isActive
        );
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }

    public function update($id, $data) {
        $isActive = $data['is_active'] ? 1 : 0;
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE rooms SET theatre_id = ?, name = ?, total_seats = ?, is_active = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "isiii",
            $data['theatre_id'],
            $data['name'],
            $data['total_seats'],
            $isActive,
            $id
        );
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM rooms WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function updateTotalSeats($id, $totalSeats) {
        $stmt = mysqli_prepare($this->conn, "UPDATE rooms SET total_seats = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $totalSeats, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function getAllWithTheatre() {
        $query = "
            SELECT r.*, t.name AS theatre_name, t.city AS theatre_city,
                   (SELECT COUNT(*) FROM seats s WHERE s.room_id = r.id) AS seat_count
            FROM rooms r
            INNER JOIN theatres t ON t.id = r.theatre_id
            ORDER BY t.name ASC, r.name ASC
        ";
        $result = mysqli_query($this->conn, $query);
        $rooms = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rooms[] = $row;
            }
        }
        return $rooms;
    }

    public function getByTheatreId($theatreId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT * FROM rooms WHERE theatre_id = ? ORDER BY name ASC"
        );
        mysqli_stmt_bind_param($stmt, "i", $theatreId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rooms = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rooms[] = $row;
        }
        return $rooms;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }
}
