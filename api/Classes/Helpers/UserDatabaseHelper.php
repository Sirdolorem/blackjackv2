<?php
namespace blackjack\Helpers;

use blackjack\Response;
use blackjack\Helpers\DbHelper\DbHelper;
use mysqli_sql_exception;

abstract class UserDatabaseHelper extends DbHelper
{
    /**
     * Get a user by username
     *
     * @param string $username
     * @return array|null The user data or null if not found
     */
    protected function getUserByUsername(string $username): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            if (!$stmt) {
                Response::error("Failed to prepare statement: " . $this->conn->error);
                return null;
            }

            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                Response::error("Execution failed: " . $stmt->error);
                return null;
            }

            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }

            // No user found
            return null;
        } catch (mysqli_sql_exception $e) {
            Response::error("Database error: " . $e->getMessage());
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
    protected function createUser(string $userId, string $username, string $hashedPassword): bool
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (user_id, username, password) VALUES (?, ?, ?)");
            if (!$stmt) {
                Response::error("Failed to prepare statement: " . $this->conn->error);
                return false;
            }

            $stmt->bind_param("sss", $userId, $username, $hashedPassword);
            if (!$stmt->execute()) {
                Response::error("Failed to execute query: " . $stmt->error);
                return false;
            }

            return true;
        } catch (mysqli_sql_exception $e) {
            Response::error("Database error: " . $e->getMessage());
            return false;
        }
    }
}
