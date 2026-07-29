<?php
namespace App\Models;

use App\Config\Database;

class BookingModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function beginTransaction() {
        mysqli_begin_transaction($this->conn);
    }

    public function commit() {
        mysqli_commit($this->conn);
    }

    public function rollback() {
        mysqli_rollback($this->conn);
    }

    //

    public function getById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM bookings WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function getByIdAndUser($id, $userId) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM bookings WHERE id = ? AND user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ii", $id, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function getAdminBookingById($bookingId) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM bookings WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function getAdminBookingStats() {
        $stats = [
            'total' => 0,
            'paid' => 0,
            'canceled' => 0,
            'today' => 0
        ];

        $query = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid,
                SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) AS canceled,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today
            FROM bookings
        ";

        $result = mysqli_query($this->conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['total'] = (int)($row['total'] ?? 0);
            $stats['paid'] = (int)($row['paid'] ?? 0);
            $stats['canceled'] = (int)($row['canceled'] ?? 0);
            $stats['today'] = (int)($row['today'] ?? 0);
        }

        return $stats;
    }

    public function getAdminBookings($filters = []) {
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'b.status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = 'DATE(b.created_at) >= ?';
            $types .= 's';
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = 'DATE(b.created_at) <= ?';
            $types .= 's';
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(
                CAST(b.id AS CHAR) LIKE ?
                OR u.first_name LIKE ?
                OR u.last_name LIKE ?
                OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
                OR u.email LIKE ?
                OR u.phone LIKE ?
                OR m.title LIKE ?
                OR th.name LIKE ?
            )";
            $types .= 'ssssssss';
            $search = '%' . $filters['search'] . '%';
            for ($i = 0; $i < 8; $i++) {
                $params[] = $search;
            }
        }

        $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $query = "
            SELECT
                b.id,
                b.user_id,
                b.total_price,
                b.payment_method,
                b.status,
                b.created_at,
                b.updated_at,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                GROUP_CONCAT(DISTINCT m.title ORDER BY m.title SEPARATOR ', ') AS movie_title,
                GROUP_CONCAT(DISTINCT th.name ORDER BY th.name SEPARATOR ', ') AS theatre_name,
                GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') AS room_name,
                MIN(st.show_date) AS show_date,
                MIN(st.start_time) AS start_time,
                GROUP_CONCAT(DISTINCT CONCAT(s.seat_row, s.seat_number) ORDER BY s.seat_row, s.seat_number SEPARATOR ', ') AS seats,
                COUNT(t.id) AS ticket_count
            FROM bookings b
            INNER JOIN users u ON u.id = b.user_id
            LEFT JOIN tickets t ON t.booking_id = b.id
            LEFT JOIN showtimes st ON st.id = t.showtime_id
            LEFT JOIN movies m ON m.id = st.movie_id
            LEFT JOIN rooms r ON r.id = st.room_id
            LEFT JOIN theatres th ON th.id = r.theatre_id
            LEFT JOIN seats s ON s.id = t.seat_id
            $whereSql
            GROUP BY
                b.id, b.user_id, b.total_price, b.payment_method, b.status,
                b.created_at, b.updated_at, u.first_name, u.last_name, u.email, u.phone
            ORDER BY b.created_at DESC, b.id DESC
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            return [];
        }

        if ($types !== '') {
            $this->bindParams($stmt, $types, $params);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $bookings = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $bookings[] = $row;
            }
        }

        return $bookings;
    }

    public function getAdminBookingDetail($bookingId) {
        $query = "
            SELECT
                b.id,
                b.user_id,
                b.total_price,
                b.payment_method,
                b.status,
                b.created_at,
                b.updated_at,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.birth_date,
                COUNT(t.id) AS ticket_count,
                GROUP_CONCAT(DISTINCT m.title ORDER BY m.title SEPARATOR ', ') AS movie_title,
                GROUP_CONCAT(DISTINCT th.name ORDER BY th.name SEPARATOR ', ') AS theatre_name,
                GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') AS room_name,
                MIN(st.show_date) AS show_date,
                MIN(st.start_time) AS start_time,
                MIN(st.end_time) AS end_time,
                GROUP_CONCAT(DISTINCT CONCAT(s.seat_row, s.seat_number) ORDER BY s.seat_row, s.seat_number SEPARATOR ', ') AS seats
            FROM bookings b
            INNER JOIN users u ON u.id = b.user_id
            LEFT JOIN tickets t ON t.booking_id = b.id
            LEFT JOIN showtimes st ON st.id = t.showtime_id
            LEFT JOIN movies m ON m.id = st.movie_id
            LEFT JOIN rooms r ON r.id = st.room_id
            LEFT JOIN theatres th ON th.id = r.theatre_id
            LEFT JOIN seats s ON s.id = t.seat_id
            WHERE b.id = ?
            GROUP BY
                b.id, b.user_id, b.total_price, b.payment_method, b.status,
                b.created_at, b.updated_at, u.first_name, u.last_name, u.email,
                u.phone, u.birth_date
            LIMIT 1
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function getAdminBookingTickets($bookingId) {
        $query = "
            SELECT
                t.id AS ticket_id,
                t.price,
                t.status AS ticket_status,
                t.created_at,
                st.show_date,
                st.start_time,
                st.end_time,
                m.title AS movie_title,
                r.name AS room_name,
                th.name AS theatre_name,
                th.address AS theatre_address,
                th.city AS theatre_city,
                s.seat_row,
                s.seat_number,
                seat_types.name AS seat_type_name
            FROM tickets t
            INNER JOIN showtimes st ON st.id = t.showtime_id
            INNER JOIN movies m ON m.id = st.movie_id
            INNER JOIN rooms r ON r.id = st.room_id
            INNER JOIN theatres th ON th.id = r.theatre_id
            INNER JOIN seats s ON s.id = t.seat_id
            LEFT JOIN seat_types ON seat_types.id = s.seat_type_id
            WHERE t.booking_id = ?
            ORDER BY st.show_date ASC, st.start_time ASC, s.seat_row ASC, s.seat_number ASC
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $tickets = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $tickets[] = $row;
            }
        }

        return $tickets;
    }

    public function updateBookingStatus($bookingId, $status) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE bookings SET status = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "si", $status, $bookingId);
        return mysqli_stmt_execute($stmt);
    }

    public function updateTicketsStatusByBooking($bookingId, $ticketStatus) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE tickets SET status = ? WHERE booking_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "si", $ticketStatus, $bookingId);
        return mysqli_stmt_execute($stmt);
    }

    public function hasSeatConflictWhenRestoring($bookingId) {
        $query = "
            SELECT COUNT(*) AS total
            FROM tickets current_ticket
            INNER JOIN tickets other_ticket
                ON other_ticket.showtime_id = current_ticket.showtime_id
               AND other_ticket.seat_id = current_ticket.seat_id
               AND other_ticket.booking_id != current_ticket.booking_id
               AND other_ticket.status != 'canceled'
            WHERE current_ticket.booking_id = ?
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return $row && (int)$row['total'] > 0;
    }

    public function deleteBooking($bookingId) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM bookings WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        return mysqli_stmt_execute($stmt);
    }

    //
    public function getBookingsByUser($userId) {
        $query = "
            SELECT
                b.*,
                GROUP_CONCAT(CONCAT(s.seat_row, s.seat_number) ORDER BY s.seat_row, s.seat_number SEPARATOR ', ') AS seat_names,
                GROUP_CONCAT(CONCAT(s.seat_row, s.seat_number) ORDER BY s.seat_row, s.seat_number SEPARATOR ', ') AS seats,
                st.show_date,
                st.start_time,
                st.end_time,
                m.title AS movie_title,
                m.poster AS movie_poster,
                r.name AS room_name,
                th.name AS theatre_name,
                th.address AS theatre_address,
                th.city AS theatre_city
            FROM bookings b
            INNER JOIN tickets t ON t.booking_id = b.id
            INNER JOIN seats s ON s.id = t.seat_id
            INNER JOIN showtimes st ON st.id = t.showtime_id
            INNER JOIN movies m ON m.id = st.movie_id
            INNER JOIN rooms r ON r.id = st.room_id
            INNER JOIN theatres th ON th.id = r.theatre_id
            WHERE b.user_id = ?
            GROUP BY b.id
            ORDER BY b.created_at DESC
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $bookings = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }

        return $bookings;
    }
    //

    //
    public function createBooking($userId, $totalPrice, $paymentMethod) {
        $status = 'paid';

        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO bookings (user_id, total_price, payment_method, status)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "idss", $userId, $totalPrice, $paymentMethod, $status);

        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }

        return false;
    }

    public function cancelBooking($bookingId, $userId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "
            UPDATE bookings
            SET status = 'canceled'
            WHERE id = ?
              AND user_id = ?
              AND status != 'canceled'
            "
        );

        mysqli_stmt_bind_param($stmt, "ii", $bookingId, $userId);
        if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) < 1) {
            return false;
        }

        $stmtTicket = mysqli_prepare(
            $this->conn,
            "
            UPDATE tickets
            SET status = 'canceled'
            WHERE booking_id = ?
              AND status != 'canceled'
            "
        );

        mysqli_stmt_bind_param($stmtTicket, "i", $bookingId);

        if (!mysqli_stmt_execute($stmtTicket)) {
            return false;
        }

        return true;
    }
    //

    public function getPrimaryShowtimeByBookingId($bookingId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT st.show_date, st.start_time
             FROM tickets t
             INNER JOIN showtimes st ON st.id = t.showtime_id
             WHERE t.booking_id = ?
             ORDER BY t.id ASC
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return $result ? mysqli_fetch_assoc($result) : null;
    }

    //
    public function getTotalBookings() {
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as count FROM bookings");
        if ($result) {
            return mysqli_fetch_assoc($result)['count'];
        }
        return 0;
    }
    //

    public function getTotalRevenue() {
        $result = mysqli_query($this->conn, "SELECT SUM(total_price) as total FROM bookings WHERE status='paid'");
        if ($result) {
            return mysqli_fetch_assoc($result)['total'] ?? 0;
        }
        return 0;
    }
    //

    public function getTotalSpentByUser($userId) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT SUM(t.price) AS total_spent
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

        return ($row && $row['total_spent'] !== null) ? (int)$row['total_spent'] : 0;
    }

    //
    public function getTodayBookingsCount() {
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()");
        if ($result) {
            return mysqli_fetch_assoc($result)['count'];
        }
        return 0;
    }
    //

    //
    public function getError() {
        return mysqli_error($this->conn);
    }
    //

    private function bindParams($stmt, $types, array $params) {
        $bindParams = [$types];
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
}
