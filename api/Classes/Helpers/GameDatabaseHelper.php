<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;

abstract class GameDatabaseHelper extends DbHelper
{
    /**
     * Checks if a game exists in the database.
     *
     * @param string $gameId The ID of the game to check.
     * @return bool Returns true if the game exists, false otherwise.
     */
    abstract public function checkIfGameExists(string $gameId): bool;

    /**
     * Initializes a new game in the database by inserting the game ID and deck.
     *
     * @param string $gameId The ID of the game.
     * @param string $deck The deck configuration for the game.
     * @return void
     */
    protected function initGame(string $gameId, string $deck): void
    {
        $query = "INSERT INTO games (game_id, deck) VALUES (?, ?)";
        $params = [$gameId, $deck];

        if (!$this->executeStatement($query, $params)) {
            Response::error("Failed to initialize game");
        }
    }

    /**
     * Checks if a specific game exists by its game ID.
     *
     * @param string $gameId The ID of the game to check.
     * @return bool Returns true if the game exists, false otherwise.
     */
    protected function getGame(string $gameId): bool
    {
        $query = "SELECT COUNT(*) FROM games WHERE game_id = ?";
        $params = [$gameId];

        $result = $this->executeStatement($query, $params, true);

        return $result && $result[0]['COUNT(*)'] > 0;
    }
}
