<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;

abstract class DeckDatabaseHelper extends DbHelper
{

    // Abstract method to fetch the deck for a specific game
    abstract public function getDeck(string $gameId): array;

    // Abstract method to update the deck for a specific game
    abstract public function updateDeck(string $gameId, array $deck): void;

    // Concrete method to fetch the deck from the database
    protected function fetchDeckFromDatabase(string $gameId): array
    {
        $query = "SELECT deck FROM games WHERE game_id = ?";
        $params = [$gameId];
        $result = $this->executeStatement($query, $params, true);

        if (empty($result)) {
            Response::error("Game not found");
            return [];
        }

        $deck = json_decode($result[0]['deck'], true);
        if ($deck === null) {
            Response::error("Invalid deck data: " . json_last_error_msg());
            return [];
        }

        return $deck;
    }

    // Concrete method to update the deck in the database
    protected function updateDeckInDatabase(string $gameId, array $deck): void
    {
        $query = "UPDATE games SET deck = ? WHERE game_id = ?";
        $params = [json_encode($deck), $gameId];
        $this->executeStatement($query, $params);
    }
}
