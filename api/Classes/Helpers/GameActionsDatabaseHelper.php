<?php

namespace blackjack\Helpers;

use blackjack\GameActions;
use blackjack\Helpers\DbHelper\DbHelper;

abstract class GameActionsDatabaseHelper extends DbHelper
{
    /**
     * Logs an action performed by a player in a game.
     *
     * @param string $game_id The ID of the game.
     * @param string $user_id The ID of the user performing the action.
     * @param string $action The action the user performed (e.g., hit, stand).
     * @param string|null $card The card associated with the action (optional).
     * @return bool Returns true if the action is logged successfully, false otherwise.
     */
    protected function logAction($game_id, $user_id, $action, $card = null)
    {
        $query = ($card === null)
            ? "INSERT INTO actions (game_id, user_id, action) VALUES (?, ?, ?)"
            : "INSERT INTO actions (game_id, user_id, card, action) VALUES (?, ?, ?, ?)";

        $params = ($card === null)
            ? [$game_id, $user_id, $action]
            : [$game_id, $user_id, $card, $action];

        return $this->executeStatement($query, $params);
    }
}
