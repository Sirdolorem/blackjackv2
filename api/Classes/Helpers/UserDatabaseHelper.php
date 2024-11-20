<?php
namespace blackjack\Helpers;

use blackjack\Database;
use mysqli;
use mysqli_sql_exception;

class UserDatabaseHelper
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

/**
* Get a user by username
*
* @param string $username
* @return array|null The user data or null if not found
*/
    public function getUserByUsername(string $username): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    return $result->fetch_assoc();
                }
            }
            return null;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

/**
* Insert a new user into the database
*
* @param string $userId
* @param string $username
* @param string $hashedPassword
* @return bool True if the insertion is successful, false otherwise
*/
    public function createUser(string $userId, string $username, string $hashedPassword): bool
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (user_id, username, password) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $userId, $username, $hashedPassword);
                return $stmt->execute();
            }
            return false;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
