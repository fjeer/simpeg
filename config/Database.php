<?php
// config/Database.php
class Database
{
    private string $host = 'localhost';
    private string $username = 'root';
    private string $password = '';
    private string $dbname = 'simpeg';

    private PDO $conn;

    public function __construct()
    {
        $db = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        try {
            $this->conn = new PDO(
                $db,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Koneksi database gagal",
                500,
                $e
            );
        }
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
