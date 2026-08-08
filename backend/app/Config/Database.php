<?php
namespace App\Config;

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            $host     = getenv('DB_HOST') ?: 'localhost';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
            $database = getenv('DB_NAME') ?: 'movie_ticket_booking';
            $port     = getenv('DB_PORT') ?: 3306;

            $conn = mysqli_connect($host, $username, $password, $database, (int)$port);
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
            mysqli_set_charset($conn, "utf8mb4");
            self::$connection = $conn;
        }
        return self::$connection;
    }
}
