<?php

namespace blackjack\Helpers;

use blackjack\Database;
use blackjack\Response;
use Exception;

class PlayerDatabaseHelper
{

    private \mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }
    public function getPlayerHand(string $userId): array
    {
        $stmt = $this->conn->prepare("SELECT hand FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->bind_result($hand);
        $stmt->fetch();
        return json_decode($hand, true);
    }



    public function updatePlayerHand(string $gameId, string $playerId, array $hand): bool
    {
        try {
            // Convert the hand array to a JSON string
            $handJson = json_encode($hand);

            // Prepare the SQL query to insert the player's hand
            $stmt = $this->conn->prepare("UPDATE users SET hand = ? WHERE user_id = ?");
            $stmt->bind_param("ss", $handJson, $playerId);

            // Execute the statement
            if (!$stmt->execute()) {
                Response::error("Failed to insert the player's hand.");
            }

            return true;
        } catch (Exception $e) {
            // Handle any errors
            Response::error($e->getMessage());
            return false;
        }
    }

    public function checkIfPlayerAlreadyInGame(string $userId, string $gameId): bool
    {
        // Prepare the query to check if the user is already assigned to any of the player slots
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM players WHERE game_id = ? AND (player_1 = ? OR player_2 = ? OR player_3 = ? OR player_4 = ?)"
        );

        if (!$stmt) {
            // Error preparing the statement
            $errorMessage = "Error preparing query: " . $this->conn->error;
            Response::error($errorMessage);
            return false; // Return false after handling the error
        }

        // Bind parameters (gameId and userId)
        $stmt->bind_param("sssss", $gameId, $userId, $userId, $userId, $userId);

        if (!$stmt->execute()) {
            // Error executing the query
            $errorMessage = "Error executing query: " . $stmt->error;
            Response::error($errorMessage);
            return false; // Return false after handling the error
        }

        // Bind result to check count
        $stmt->bind_result($count);
        $stmt->fetch();

        // If count is greater than 0, the user is already in the game
        return $count > 0;
    }

    public function getGamePlayersId(string $gameId): array
    {
        // Query to get all player IDs in the game
        $stmt = $this->conn->prepare("SELECT player_1, player_2, player_3, player_4 FROM players WHERE game_id = ?");
        $stmt->bind_param("s", $gameId);
        $stmt->execute();
        $stmt->bind_result($player1, $player2, $player3, $player4);
        $stmt->fetch();

        return [
            "players_id" => [
                $player1,
                $player2,
                $player3,
                $player4
            ]
        ];
    }

    public function assignPlayersToGame(string $gameId): int
    {
        $stmt = $this->conn->prepare("INSERT INTO players (game_id) VALUES (?)");
        $stmt->bind_param("s", $gameId);
        $stmt->execute();
        return $stmt->insert_id ?? false;
    }



    public function addPlayerToSlot(array $players, int $slot, string $userId, $gameId): bool
    {
        // Add the user to the available slot
        $players[$slot - 1] = $userId;
        // Update the players in the database
        $stmt = $this->conn->prepare("
            UPDATE players SET player_1 = ?, player_2 = ?, player_3 = ?, player_4 = ? WHERE game_id = ?
        ");
        $stmt->bind_param("sssss", $players[0], $players[1], $players[2], $players[3], $gameId);
        return $stmt->execute();
    }

    protected function clearPlayerHand(string $userId, string $gameId): bool
    {
        // Clear the player's hand in the database
        $stmt = $this->conn->prepare("UPDATE users SET hand = ? WHERE user_id = ?");
        $emptyHand = json_encode([]); // Empty hand represented as an empty array
        $stmt->bind_param("ss", $emptyHand, $userId);

        // Execute the statement and check if successful
        if ($stmt->execute()) {
            return true;
        } else {
            // Log an error or handle failure if needed
            Response::error("Failed to clear player hand.");
            return false;
        }
    }


    public function updatePlayersInGame(array $players, string $gameId): bool
    {
        // Prepare the SQL query to update the player slots in the game
        $stmt = $this->conn->prepare("
        UPDATE players
        SET player_1 = ?, player_2 = ?, player_3 = ?, player_4 = ?
        WHERE game_id = ?
    ");

        // Bind the parameters: player slots and gameId
        $stmt->bind_param("sssss", $players[0], $players[1], $players[2], $players[3], $gameId);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        } else {
            Response::error("Error updating players in game: " . $stmt->error);
            return false;
        }
    }

}
