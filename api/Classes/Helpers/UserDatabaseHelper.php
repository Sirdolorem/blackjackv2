<?php
namespace blackjack\Helpers;

use blackjack\Response;
use blackjack\Helpers\DbHelper\DbHelper;
use Exception;

abstract class UserDatabaseHelper extends DbHelper
{
    /**
     * Fetches a user from the database by username.
     *
     * @param string $username The username to search for.
     * @return array|null The user data or null if not found.
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

            return null;
        } catch (Exception $e) {
            Response::error("Database error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Creates a new user in the database.
     *
     * @param string $userId The unique ID of the user.
     * @param string $username The username of the user.
     * @param string $hashedPassword The hashed password of the user.
     * @return bool True if the user was successfully created, false otherwise.
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

    /**
     * Fetch the current chips for a user.
     *
     * @param string $userId The user ID
     * @return int The current chip balance for the user
     */
    public function fetchUserChips(string $userId): int
    {
        $query = "SELECT chips FROM users WHERE user_id = ?";
        $result = $this->executeStatement($query, [$userId], true);

        return $result[0]['chips'] ?? 0;
    }

    /**
     * Update the user's chip balance.
     *
     * @param string $userId The user ID
     * @param int $newChipBalance The new chip balance
     * @return bool True if the balance is updated successfully, false otherwise
     */
    public function updateUserChips(string $userId, int $newChipBalance): bool
    {
        $query = "UPDATE users SET chips = ? WHERE user_id = ?";
        return $this->executeStatement($query, [$newChipBalance, $userId]);
    }
}
