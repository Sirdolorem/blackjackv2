<?php

namespace blackjack\Helpers;

use blackjack\ActionCheck;
use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;
use PHP_CodeSniffer\Tests\Core\Tokenizers\PHP\ResolveSimpleTokenTest;
use PHP_CodeSniffer\Tests\Core\Tokenizers\Tokenizer\RecurseScopeMapWithNamespaceOperatorTest;

abstract class ActionCheckDatabaseHelper extends DbHelper
{

    /**
     * Abstract method to be implemented by subclasses to get the active player ID for a given game.
     *
     * @param string $gameId The ID of the game.
     * @return mixed The active player ID.
     */
    abstract protected function getActivePlayerId(string $gameId);

    abstract protected function lastPlayerAction(string $userId): string;


    /**
     * Fetches the active player ID for a given game.
     *
     * @param string $gameId The ID of the game.
     * @return array The decoded active player ID, or an empty array if not found.
     */
    protected function fetchActivePlayerId(string $gameId):string
    {
        $result = $this->executeStatement("SELECT active_user FROM games WHERE game_id = ?", [$gameId], true);
        return $result[0]['active_user'] ?? '';
    }

    /**
     * Fetch the last action performed by a player in the game.
     *
     * @param string $userId The user ID of the player.
     * @return string The last action performed by the player, or an empty string if no action found.
     */
    protected function fetchLastPlayerAction(string $userId): string
    {
        // Fetch the last action performed by the player
        $result = $this->executeStatement(
            "SELECT action FROM actions WHERE user_id = ? ORDER BY timestamp DESC LIMIT 1",
            [$userId],
            true
        );

        // Return the last action or an empty string if no action found
        return $result[0]['action'] ?? '';
    }
}
