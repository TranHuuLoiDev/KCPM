<?php
namespace App\Models;

use App\Config\Database;

class DashboardModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getTodayBookings() {
        $query = "
            SELECT b.id, b.total_price, b.status, b.created_at, u.first_name, u.last_name, 
                   m.title as movie_name, r.name as room_name, st.start_time,
                   GROUP_CONCAT(CONCAT(s.seat_row, s.seat_number) SEPARATOR ', ') as seats
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            LEFT JOIN tickets t ON t.booking_id = b.id
            LEFT JOIN showtimes st ON t.showtime_id = st.id
            LEFT JOIN movies m ON st.movie_id = m.id
            LEFT JOIN rooms r ON st.room_id = r.id
            LEFT JOIN seats s ON t.seat_id = s.id
            WHERE DATE(b.created_at) = CURDATE()
            GROUP BY b.id
            ORDER BY b.created_at DESC
            LIMIT 10
        ";
        
        $result = mysqli_query($this->conn, $query);
        $bookings = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $bookings[] = $row;
            }
        }
        return $bookings;
    }

    public function getPopularMovies() {
        $query = "
            SELECT m.title as movie_name, COUNT(t.id) as ticket_count, SUM(t.price) as revenue
            FROM tickets t
            JOIN showtimes st ON t.showtime_id = st.id
            JOIN movies m ON st.movie_id = m.id
            JOIN bookings b ON t.booking_id = b.id
            WHERE b.status = 'paid'
            GROUP BY m.id
            ORDER BY ticket_count DESC
            LIMIT 5
        ";
        
        $result = mysqli_query($this->conn, $query);
        $movies = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $movies[] = $row;
            }
        }
        return $movies;
    }

    public function getLoyalCustomers() {
        $query = "
            SELECT u.first_name, u.last_name, COUNT(t.id) as ticket_count, SUM(t.price) as total_spent
            FROM tickets t
            JOIN bookings b ON t.booking_id = b.id
            JOIN users u ON b.user_id = u.id
            WHERE b.status = 'paid'
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT 5
        ";
        
        $result = mysqli_query($this->conn, $query);
        $customers = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $customers[] = $row;
            }
        }
        return $customers;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }
}
