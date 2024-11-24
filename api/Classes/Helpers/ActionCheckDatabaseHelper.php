<?php

namespace blackjack\Helpers;

use blackjack\ActionCheck;
use blackjack\Helpers\DbHelper\DbHelper;

abstract class ActionCheckDatabaseHelper extends DbHelper
{

    protected function fetchActivePlayerId(string $gameId)
    {
        $result = $this->executeStatement("SELECT active_user FROM games WHERE game_id = ?", [$gameId], true);
        return json_decode($result[0]['active_user'] ?? '', true);
    }

    abstract protected function getActivePlayerId(string $gameId);
}
