<?php
namespace App\Config;

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            $host = 'localhost';
            $username = 'root';
            $password = '';
            $database = 'movie_ticket_booking';
            $port = 3306;

            $conn = mysqli_connect($host, $username, $password, $database, $port);
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
            mysqli_set_charset($conn, "utf8mb4");
            self::$connection = $conn;
        }
        return self::$connection;
    }
}
