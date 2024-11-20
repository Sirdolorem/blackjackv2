<?php

namespace blackjack\Helpers;

use blackjack\Database;
use blackjack\Response;

class DeckDatabaseHelper
{
    private \mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function fetchDeck(string $gameId): array
    {
        $stmt = $this->conn->prepare("SELECT deck FROM games WHERE game_id = ?");
        if (!$stmt) {
            Response::error("Failed to prepare query: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("s", $gameId);
        if (!$stmt->execute()) {
            Response::error("Failed to execute query: " . $stmt->error);
            return [];
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        Response::debug(var_export($res, true));
        if ($res) {
            Response::error("Game not found");
            return [];
        }

        $deck = json_decode($res, true);
        if ($deck === null) {
            Response::error("Invalid deck data");
            return [];
        }

        return $deck;
    }


    public function updateDeck(string $gameId, array $deck): void
    {
        $stmt = $this->conn->prepare("UPDATE games SET deck = ? WHERE game_id = ?");
        $updatedDeck = json_encode($deck);
        $stmt->bind_param("ss", $updatedDeck, $gameId);
        $stmt->execute();
    }
}
