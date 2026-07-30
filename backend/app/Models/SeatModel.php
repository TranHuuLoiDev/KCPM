<?php
namespace App\Models;

use App\Config\Database;

class SeatModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM seats WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function findByPosition($roomId, $seatRow, $seatNumber, $excludeId = null) {
        if ($excludeId) {
            $stmt = mysqli_prepare(
                $this->conn,
                "SELECT id FROM seats WHERE room_id = ? AND seat_row = ? AND seat_number = ? AND id != ?"
            );
            mysqli_stmt_bind_param($stmt, "isii", $roomId, $seatRow, $seatNumber, $excludeId);
        } else {
            $stmt = mysqli_prepare(
                $this->conn,
                "SELECT id FROM seats WHERE room_id = ? AND seat_row = ? AND seat_number = ?"
            );
            mysqli_stmt_bind_param($stmt, "isi", $roomId, $seatRow, $seatNumber);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function insert($data) {
        $isActive = $data['is_active'] ? 1 : 0;
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO seats (room_id, seat_row, seat_number, seat_type_id, is_active) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "isiii",
            $data['room_id'],
            $data['seat_row'],
            $data['seat_number'],
            $data['seat_type_id'],
            $isActive
        );
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $data) {
        $isActive = $data['is_active'] ? 1 : 0;
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE seats SET room_id = ?, seat_row = ?, seat_number = ?, seat_type_id = ?, is_active = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "isiiii",
            $data['room_id'],
            $data['seat_row'],
            $data['seat_number'],
            $data['seat_type_id'],
            $isActive,
            $id
        );
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM seats WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function countByRange($roomId, $startRow, $endRow, $startNumber, $endNumber) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT COUNT(*) AS total FROM seats
             WHERE room_id = ?
             AND seat_row BETWEEN ? AND ?
             AND seat_number BETWEEN ? AND ?"
        );
        mysqli_stmt_bind_param($stmt, "issii", $roomId, $startRow, $endRow, $startNumber, $endNumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    public function deleteByRange($roomId, $startRow, $endRow, $startNumber, $endNumber) {
        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE FROM seats
             WHERE room_id = ?
             AND seat_row BETWEEN ? AND ?
             AND seat_number BETWEEN ? AND ?"
        );
        mysqli_stmt_bind_param($stmt, "issii", $roomId, $startRow, $endRow, $startNumber, $endNumber);
        if (!mysqli_stmt_execute($stmt)) {
            return false;
        }
        return mysqli_stmt_affected_rows($stmt);
    }

    public function getNextSeatNumber($roomId, $seatRow) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT MAX(seat_number) AS max_num FROM seats WHERE room_id = ? AND seat_row = ?"
        );
        mysqli_stmt_bind_param($stmt, "is", $roomId, $seatRow);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return (int) ($row['max_num'] ?? 0) + 1;
    }

    public function getRowLettersByRoom($roomId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT DISTINCT seat_row FROM seats WHERE room_id = ? ORDER BY seat_row ASC"
        );
        mysqli_stmt_bind_param($stmt, "i", $roomId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row['seat_row'];
        }
        return $rows;
    }

    public function countByRoomId($roomId) {
        $stmt = mysqli_prepare($this->conn, "SELECT COUNT(*) AS total FROM seats WHERE room_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $roomId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    public function getAllWithDetails($roomId = null) {
        $query = "
            SELECT s.*, r.name AS room_name, t.name AS theatre_name,
                   st.name AS seat_type_name, st.price AS seat_type_price
            FROM seats s
            INNER JOIN rooms r ON r.id = s.room_id
            INNER JOIN theatres t ON t.id = r.theatre_id
            INNER JOIN seat_types st ON st.id = s.seat_type_id
        ";
        if ($roomId) {
            $query .= " WHERE s.room_id = " . (int)$roomId;
        }
        $query .= " ORDER BY t.name ASC, r.name ASC, s.seat_row ASC, s.seat_number ASC";

        $result = mysqli_query($this->conn, $query);
        $seats = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $seats[] = $row;
            }
        }
        return $seats;
    }

    public function getByRoomIdWithType($roomId) {
        $query = "
            SELECT s.id AS seat_id, s.room_id, s.seat_row, s.seat_number,
                   s.seat_type_id, s.is_active,
                   st.name AS seat_type_name, st.price AS seat_type_price
            FROM seats s
            INNER JOIN seat_types st ON st.id = s.seat_type_id
            WHERE s.room_id = ?
            ORDER BY s.seat_row ASC, s.seat_number ASC
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $roomId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $seats = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $seats[] = $row;
            }
        }
        return $seats;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }


    /**
     * Lấy ghế theo phòng
     */
    public function getByRoom($room_id) {
        $sql = "SELECT s.*, st.name as seat_type_name, st.price as seat_type_price
                FROM seats s
                LEFT JOIN seat_types st ON s.seat_type_id = st.id
                WHERE s.room_id = ?
                ORDER BY s.seat_row, s.seat_number";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $seats = [];
        while ($row = $result->fetch_assoc()) {
            $seats[] = $row;
        }
        return $seats;
    }

    /**
     * Lấy ghế đã đặt theo suất chiếu
     */
    public function getBookedSeats($showtime_id) {
        $sql = "SELECT seat_id FROM tickets WHERE showtime_id = ? AND status != 'canceled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $showtime_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booked = [];
        while ($row = $result->fetch_assoc()) {
            $booked[] = $row['seat_id'];
        }
        return $booked;
    }




    public function getByIds($seatIds) {
        if (empty($seatIds)) return [];

        $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
        $sql = "SELECT s.*, st.name as seat_type_name, st.price as seat_type_price
                FROM seats s
                LEFT JOIN seat_types st ON s.seat_type_id = st.id
                WHERE s.id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        $types = str_repeat('i', count($seatIds));
        $stmt->bind_param($types, ...$seatIds);
        $stmt->execute();
        $result = $stmt->get_result();
        $seats = [];
        while ($row = $result->fetch_assoc()) {
            $seats[] = $row;
        }
        return $seats;
    }
}
