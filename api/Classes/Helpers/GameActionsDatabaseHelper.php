<?php

namespace blackjack\Helpers;


use blackjack\GameActions;
use blackjack\Helpers\DbHelper\DbHelper;

abstract class GameActionsDatabaseHelper extends DbHelper
{

    protected function logAction($game_id, $user_id, $action, $card = null)
    {
        // Build the query based on whether a card is provided
        $query = ($card === null)
            ? "INSERT INTO actions (game_id, user_id, action) VALUES (?, ?, ?)"
            : "INSERT INTO actions (game_id, user_id, card, action) VALUES (?, ?, ?, ?)";

        // Set parameters dynamically based on the query
        $params = ($card === null)
            ? [$game_id, $user_id, $action]
            : [$game_id, $user_id, $card, $action];

        // Use the executeStatement method to run the query
        $result = $this->executeStatement($query, $params);

        // Return true if the statement executes successfully
        return $result;
    }
}
