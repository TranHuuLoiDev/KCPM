<?php
namespace App\Models;

use App\Config\Database;

class TicketModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll() {
        $query = "
            SELECT t.id AS ticket_id, t.booking_id, t.showtime_id, t.seat_id,
                   t.price, t.status AS ticket_status, t.created_at,
                   b.booking_code, b.status AS booking_status,
                   u.first_name, u.last_name, u.email,
                   m.title AS movie_title,
                   st.show_date, st.start_time,
                   th.name AS theatre_name, r.name AS room_name,
                   s.seat_row, s.seat_number
            FROM tickets t
            INNER JOIN bookings b ON b.id = t.booking_id
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN showtimes st ON st.id = t.showtime_id
            INNER JOIN movies m ON m.id = st.movie_id
            INNER JOIN rooms r ON r.id = st.room_id
            INNER JOIN theatres th ON th.id = r.theatre_id
            INNER JOIN seats s ON s.id = t.seat_id
            ORDER BY t.created_at DESC, t.id DESC
        ";
        $result = mysqli_query($this->conn, $query);
        return $this->fetchAll($result);
    }

    public function getById($id) {
        $query = "
            SELECT t.id AS ticket_id, t.booking_id, t.showtime_id, t.seat_id,
                   t.price, t.status AS ticket_status, t.created_at,
                   b.booking_code, b.status AS booking_status,
                   u.first_name, u.last_name, u.email,
                   m.title AS movie_title,
                   st.show_date, st.start_time,
                   th.name AS theatre_name, r.name AS room_name,
                   s.seat_row, s.seat_number
            FROM tickets t
            INNER JOIN bookings b ON b.id = t.booking_id
            INNER JOIN users u ON u.id = b.user_id
            INNER JOIN showtimes st ON st.id = t.showtime_id
            INNER JOIN movies m ON m.id = st.movie_id
            INNER JOIN rooms r ON r.id = st.room_id
            INNER JOIN theatres th ON th.id = r.theatre_id
            INNER JOIN seats s ON s.id = t.seat_id
            WHERE t.id = ?
            LIMIT 1
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function create($data) {
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO tickets (booking_id, showtime_id, seat_id, price, status) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "iiids",
            $data['booking_id'],
            $data['showtime_id'],
            $data['seat_id'],
            $data['price'],
            $data['status']
        );
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }

    public function update($id, $data) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE tickets SET booking_id = ?, showtime_id = ?, seat_id = ?, price = ?, status = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "iiidsi",
            $data['booking_id'],
            $data['showtime_id'],
            $data['seat_id'],
            $data['price'],
            $data['status'],
            $id
        );
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM tickets WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function getBookedSeatIdsByShowtimeId($showtimeId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT seat_id FROM tickets WHERE showtime_id = ? AND status != 'canceled'"
        );
        mysqli_stmt_bind_param($stmt, "i", $showtimeId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $seatIds = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $seatIds[] = (int)$row['seat_id'];
            }
        }
        return $seatIds;
    }

    public function isSeatBooked($showtimeId, $seatId, $excludeTicketId = null) {
        if ($excludeTicketId) {
            $stmt = mysqli_prepare(
                $this->conn,
                "SELECT id FROM tickets WHERE showtime_id = ? AND seat_id = ? AND status != 'canceled' AND id != ? LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, "iii", $showtimeId, $seatId, $excludeTicketId);
        } else {
            $stmt = mysqli_prepare(
                $this->conn,
                "SELECT id FROM tickets WHERE showtime_id = ? AND seat_id = ? AND status != 'canceled' LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, "ii", $showtimeId, $seatId);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return (bool)mysqli_fetch_assoc($result);
    }

    public function createMany($bookingId, $showtimeId, $seatPrices) {
        foreach ($seatPrices as $seatPrice) {
            $created = $this->create([
                'booking_id' => $bookingId,
                'showtime_id' => $showtimeId,
                'seat_id' => $seatPrice['seat_id'],
                'price' => $seatPrice['price'],
                'status' => 'booked'
            ]);
            if (!$created) {
                return false;
            }
        }
        return true;
    }

    public function getByBookingId($bookingId) {
        $query = "
            SELECT t.id AS ticket_id, t.booking_id, t.showtime_id, t.seat_id,
                   t.price, t.status AS ticket_status, t.created_at,
                   m.title AS movie_title,
                   st.show_date, st.start_time,
                   th.name AS theatre_name, r.name AS room_name,
                   s.seat_row, s.seat_number
            FROM tickets t
            INNER JOIN showtimes st ON st.id = t.showtime_id
            INNER JOIN movies m ON m.id = st.movie_id
            INNER JOIN rooms r ON r.id = st.room_id
            INNER JOIN theatres th ON th.id = r.theatre_id
            INNER JOIN seats s ON s.id = t.seat_id
            WHERE t.booking_id = ?
            ORDER BY s.seat_row ASC, s.seat_number ASC
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $this->fetchAll($result);
    }

    public function getTotalTicketsByUser($userId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT COUNT(t.id) AS total_tickets
             FROM tickets t
             INNER JOIN bookings b ON b.id = t.booking_id
             WHERE b.user_id = ?
               AND b.status = 'paid'
               AND t.status != 'canceled'"
        );
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return $row ? (int)$row['total_tickets'] : 0;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }

    private function fetchAll($result) {
        $items = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
        }
        return $items;
    }
}