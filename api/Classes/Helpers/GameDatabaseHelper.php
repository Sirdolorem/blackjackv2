<?php

namespace blackjack\Helpers;

use blackjack\Database;
use blackjack\Response;

class GameDatabaseHelper
{
    private \mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function initGame(string $gameId, array $deck): void
    {
        $stmt = $this->conn->prepare("INSERT INTO games (game_id, deck) VALUES (?, ?)");
        $jsonDeck = json_encode($deck);
        $stmt->bind_param("ss", $gameId, $jsonDeck);

        if (!$stmt->execute()) {
            Response::error("Failed to save game");
        }
    }
    public function updatePlayerId(string $gameId, int $playerId)
    {
        $stmt = $this->conn->prepare("UPDATE games SET players_id = ? WHERE game_id = ?");
        $stmt->bind_param("is", $playerId, $gameId);

        if (!$stmt->execute()) {
            Response::error("Failed update player ID");
        }
    }

    public function checkIfGameExists($gameId): bool
    {
        // Prepare the query to check if the game exists
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM games WHERE game_id = ?");

        if (!$stmt) {
            // Error preparing the statement
            $errorMessage = "Error preparing query: " . $this->conn->error;
            Response::error($errorMessage);
            return false; // Return false after handling the error
        }
        return true;
    }
}
