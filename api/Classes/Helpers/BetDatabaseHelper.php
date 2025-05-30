<?php
namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;

abstract class BetDatabaseHelper extends DbHelper
{
    /**
     * Abstract method to get the current bet for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @return array|null The current bet and any relevant data, or null if not found.
     */
    abstract protected function getCurrentBet(string $userId, string $gameId): array|null;

    /**
     * Abstract method to place a bet for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @param int $betAmount The amount of the bet.
     * @return bool Whether the bet was successfully placed.
     */
    abstract protected function placeBet(string $userId, string $gameId, int $betAmount): bool;

    /**
     * Abstract method to modify the bet for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @param int $newBetAmount The new bet amount.
     * @return bool Whether the bet was successfully modified.
     */
    abstract protected function modifyBet(string $userId, string $gameId, int $newBetAmount): bool;

    /**
     * Abstract method to remove a bet from a specific user in a game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @return bool Whether the bet was successfully deleted.
     */
    abstract protected function deleteBet(string $userId, string $gameId): bool;

    /**
     * Abstract method to activate double bet for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @return bool Whether the double bet was successfully activated.
     */
    abstract protected function doubleBet(string $userId, string $gameId): bool;

    /**
     * Fetches the current bet and is_double flag for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @return array|false The current [bet] and [is_double] flag, or null if not found.
     */
    protected function fetchCurrentBet(string $userId, string $gameId): array|false
    {
        $query = "SELECT bet, is_double FROM game_bets WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];
        $result = $this->executeStatement($query, $params, true);

        if (empty($result)) {
            return false;
        }

        return $result[0];
    }


    /**
     * Adds a bet for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @param int $betAmount The amount of the bet.
     * @return bool Whether the bet was successfully added.
     */
    protected function insertBet(string $userId, string $gameId, int $betAmount): bool
    {
        $query = "INSERT INTO game_bets (game_id, user_id, bet, is_double) VALUES (?, ?, ?, ?)";
        $params = [$gameId, $userId, $betAmount, 0]; // 0 for not double
        return $this->executeStatement($query, $params);
    }

    /**
     * Updates the bet amount for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @param int $newBetAmount The new bet amount.
     * @return bool Whether the bet was successfully updated.
     */
    protected function updateBetAmount(string $userId, string $gameId, int $newBetAmount): bool
    {
        $query = "UPDATE game_bets SET bet = ? WHERE game_id = ? AND user_id = ?";
        $params = [$newBetAmount, $gameId, $userId];
        return $this->executeStatement($query, $params);
    }

    /**
     * Removes a user's bet from a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @return bool Whether the bet was successfully deleted.
     */
    protected function deleteBetForUser(string $userId, string $gameId): bool
    {
        $query = "DELETE FROM game_bets WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];
        return $this->executeStatement($query, $params);
    }

    /**
     * Sets the double bet flag for a user in a specific game.
     *
     * @param string $userId The ID of the user.
     * @param string $gameId The ID of the game.
     * @return bool Whether the double bet flag was successfully set.
     */
    protected function setDoubleBetFlag(string $userId, string $gameId): bool
    {
        $query = "UPDATE game_bets SET is_double = 1 WHERE game_id = ? AND user_id = ? AND is_double = 0";
        $params = [$gameId, $userId];
        return $this->executeStatement($query, $params);
    }
}
