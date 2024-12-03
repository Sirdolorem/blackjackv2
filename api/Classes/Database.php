<?php
namespace blackjack;

use mysqli;
use Exception;

class Database
{
    private static $instance;
    private $connection;

    /**
     * Database constructor.
     * Initializes the database connection.
     */
    private function __construct()
    {
        $this->connection = $this->connect();
    }

    /**
     * Get the instance of the Database class.
     *
     * @return Database The database instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Establish a database connection.
     *
     * @return mysqli The database connection
     * @throws exception If the connection fails
     */
    private function connect(): mysqli
    {
        $host = Env::get("DB_HOST") ?? "localhost";
        $database = Env::get("MYSQL_DATABASE") ?? "blackjack";
        $username = Env::get("MYSQL_USER") ?? "blackjack";
        $password = Env::get("MYSQL_PASSWORD") ?? "";


        try {
            $this->connection = new mysqli($host, $username, $password, $database);
        } catch (Exception $e) {
            Response::error("Connection failed: " . $e->getMessage());
        }


        return $this->connection;
    }

    /**
     * Get the current database connection.
     *
     * @return mysqli The database connection
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Close the current database connection.
     */
    public function closeConnection(): void
    {
        $this->connection->close();
    }

    /**
     * Destructor that ensures the database connection is closed when the object is destroyed.
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * Prevent cloning of the Database instance.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the Database instance.
     */
    public function __wakeup()
    {
    }
}
