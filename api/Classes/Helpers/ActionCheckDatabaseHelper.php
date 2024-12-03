<?php

namespace blackjack\Helpers;

use blackjack\ActionCheck;
use blackjack\Helpers\DbHelper\DbHelper;

abstract class ActionCheckDatabaseHelper extends DbHelper
{

    /**
     * Abstract method to be implemented by subclasses to get the active player ID for a given game.
     *
     * @param string $gameId The ID of the game.
     * @return mixed The active player ID.
     */
    abstract protected function getActivePlayerId(string $gameId);


    /**
     * Fetches the active player ID for a given game.
     *
     * @param string $gameId The ID of the game.
     * @return array The decoded active player ID, or an empty array if not found.
     */
    protected function fetchActivePlayerId(string $gameId):string
    {
        $result = $this->executeStatement("SELECT active_user FROM games WHERE game_id = ?", [$gameId], true);
        return json_decode($result[0]['active_user'] ?? '', true);
    }


}
