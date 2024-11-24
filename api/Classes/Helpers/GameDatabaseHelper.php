<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;

abstract class GameDatabaseHelper extends DbHelper
{

    abstract public function checkIfGameExists(string $gameId): bool;

    protected function initGame(string $gameId, string $deck): void
    {
        $query = "INSERT INTO games (game_id, deck) VALUES (?, ?)";
        $params = [$gameId, $deck];

        if (!$this->executeStatement($query, $params)) {
            Response::error("Failed to initialize game");
        }
    }



    protected function getGame(string $gameId): bool
    {
        $query = "SELECT COUNT(*) FROM games WHERE game_id = ?";
        $params = [$gameId];

        $result = $this->executeStatement($query, $params, true);

        return $result && $result[0]['COUNT(*)'] > 0;
    }




}
