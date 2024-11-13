<?php

class Database
{
    private static $instance;
    private $connection;


    private function __construct()
    {
        $this->connection = $this->connect();
    }


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    private function connect()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $database = $_ENV['DB_DATABASE'] ?? 'blackjack';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        $connection = new mysqli($host, $username, $password, $database);

        if ($connection->connect_error) {
            throw new mysqli_sql_exception("Connection failed: " . $connection->connect_error);
        }

        return $connection;
    }


    public function getConnection()
    {
        return $this->connection;
    }

    public function closeConnection()
    {
        $this->connection->close();
    }


    public function __destruct()
    {
        $this->connection->close();
    }


    
    private function __clone() {}
    public function __wakeup() {}
}
