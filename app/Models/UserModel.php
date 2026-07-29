<?php
namespace App\Models;

use App\Config\Database;

class UserModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findByEmail($email, $excludeId = null) {
        if ($excludeId) {
            $stmt = mysqli_prepare($this->conn, "SELECT * FROM users WHERE email = ? AND id != ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "si", $email, $excludeId);
        } else {
            $stmt = mysqli_prepare($this->conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function findByPhone($phone, $excludeId = null) {
        if ($excludeId) {
            $stmt = mysqli_prepare($this->conn, "SELECT * FROM users WHERE phone = ? AND id != ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "si", $phone, $excludeId);
        } else {
            $stmt = mysqli_prepare($this->conn, "SELECT * FROM users WHERE phone = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $phone);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function getById($id) {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function insert($data) {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO users (first_name, last_name, email, password, phone, birth_date, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssss", 
            $data['first_name'], $data['last_name'], $data['email'], 
            $data['password'], $data['phone'], $data['birth_date'], $data['role']
        );
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $data) {
        if (!empty($data['password'])) {
            $stmt = mysqli_prepare($this->conn, "UPDATE users SET first_name=?, last_name=?, email=?, password=?, phone=?, birth_date=?, role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssssssi", 
                $data['first_name'], $data['last_name'], $data['email'], 
                $data['password'], $data['phone'], $data['birth_date'], $data['role'], $id
            );
        } else {
            $stmt = mysqli_prepare($this->conn, "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, birth_date=?, role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssssi", 
                $data['first_name'], $data['last_name'], $data['email'], 
                $data['phone'], $data['birth_date'], $data['role'], $id
            );
        }
        return mysqli_stmt_execute($stmt);
    }

    public function updateProfile($id, $data) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, birth_date = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "sssssi",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['birth_date'],
            $id
        );
        return mysqli_stmt_execute($stmt);
    }

    public function updatePassword($id, $password) {
        $stmt = mysqli_prepare($this->conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $password, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    public function getAll() {
        $query = "
            SELECT u.*,
                   COUNT(b.id) as total_bookings,
                   COALESCE(SUM(CASE WHEN b.status='paid' THEN b.total_price ELSE 0 END), 0) as total_spent
            FROM users u
            LEFT JOIN bookings b ON u.id = b.user_id
            GROUP BY u.id
            ORDER BY u.id DESC
        ";

        $result = mysqli_query($this->conn, $query);
        return $this->fetchUsers($result);
    }

    public function getByFilter($keyword) {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return $this->getAll();
        }

        $search = "%" . $keyword . "%";
        $query = "
            SELECT u.*, 
                   COUNT(b.id) as total_bookings,
                   COALESCE(SUM(CASE WHEN b.status='paid' THEN b.total_price ELSE 0 END), 0) as total_spent
            FROM users u
            LEFT JOIN bookings b ON u.id = b.user_id
            WHERE u.first_name LIKE ?
               OR u.last_name LIKE ?
               OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
               OR u.email LIKE ?
               OR u.phone LIKE ?
            GROUP BY u.id
            ORDER BY u.id DESC
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $search, $search, $search, $search, $search);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return $this->fetchUsers($result);
    }

    public function searchUsers($keyword = '') {
        return trim($keyword) === '' ? $this->getAll() : $this->getByFilter($keyword);
    }

    private function fetchUsers($result) {
        $users = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
        }
        return $users;
    }

    public function getStats() {
        $stats = [
            'total_users' => 0,
            'new_this_month' => 0,
            'active_users' => 0
        ];

        // Total users (excluding admin)
        $res1 = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        if ($res1) $stats['total_users'] = mysqli_fetch_assoc($res1)['total'];

        // New this month
        $res2 = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
        if ($res2) $stats['new_this_month'] = mysqli_fetch_assoc($res2)['total'];

        // Active users (users who have at least one paid booking)
        $res3 = mysqli_query($this->conn, "SELECT COUNT(DISTINCT user_id) as total FROM bookings WHERE status='paid'");
        if ($res3) $stats['active_users'] = mysqli_fetch_assoc($res3)['total'];

        return $stats;
    }

    public function getError() {
        return mysqli_error($this->conn);
    }
}
