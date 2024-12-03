<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;

abstract class DeckDatabaseHelper extends DbHelper
{
    /**
     * Abstract method to fetch the deck for a specific game.
     *
     * @param string $gameId The ID of the game.
     * @return array The deck for the game.
     */
    abstract public function getDeck(string $gameId): array;

    /**
     * Abstract method to update the deck for a specific game.
     *
     * @param string $gameId The ID of the game.
     * @param array $deck The deck to update.
     */
    abstract public function updateDeck(string $gameId, array $deck): void;

    /**
     * Fetches the deck from the database for a specific game.
     *
     * @param string $gameId The ID of the game.
     * @return array The deck for the game.
     */
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

    /**
     * Updates the deck in the database for a specific game.
     *
     * @param string $gameId The ID of the game.
     * @param array $deck The deck to store in the database.
     */
    protected function updateDeckInDatabase(string $gameId, array $deck): void
    {
        $query = "UPDATE games SET deck = ? WHERE game_id = ?";
        $params = [json_encode($deck), $gameId];
        $this->executeStatement($query, $params);
    }
}
