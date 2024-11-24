<?php
namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;

abstract class BetDatabaseHelper extends DbHelper
{
// Abstract method to get the current bet for a user in a specific game
    abstract protected function getCurrentBet(string $userId, string $gameId): array|null;

// Abstract method to place a bet for a user in a specific game
    abstract protected function placeBet(string $userId, string $gameId, int $betAmount): bool;

// Abstract method to modify the bet for a user in a specific game
    abstract protected function modifyBet(string $userId, string $gameId, int $newBetAmount): bool;

// Abstract method to remove a bet from a specific user in a game
    abstract protected function deleteBet(string $userId, string $gameId): bool;

// Abstract method to activate double bet for a user in a specific game
    abstract protected function doubleBet(string $userId, string $gameId): bool;

// Concrete method to fetch the current bet and is_double flag for a user in a specific game
    protected function fetchCurrentBet(string $userId, string $gameId): array|null
    {
        $query = "SELECT bet, is_double FROM game_bets WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];
        $result = $this->executeStatement($query, $params, true);

        if (empty($result)) {
            return null;
        }

        return [$result[0]['bet'], $result[0]['is_double']];
    }

// Concrete method to add a bet for a user in a specific game
    protected function insertBet(string $userId, string $gameId, int $betAmount): bool
    {
        $query = "INSERT INTO game_bets (game_id, user_id, bet, is_double) VALUES (?, ?, ?, ?)";
        $params = [$gameId, $userId, $betAmount, 0]; // 0 for not double
        return $this->executeStatement($query, $params);
    }

// Concrete method to update the bet for a user in a specific game
    protected function updateBetAmount(string $userId, string $gameId, int $newBetAmount): bool
    {
        $query = "UPDATE game_bets SET bet = ? WHERE game_id = ? AND user_id = ?";
        $params = [$newBetAmount, $gameId, $userId];
        return $this->executeStatement($query, $params);
    }

// Concrete method to remove a user's bet from a specific game
    protected function deleteBetForUser(string $userId, string $gameId): bool
    {
        $query = "DELETE FROM game_bets WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];
        return $this->executeStatement($query, $params);
    }

// Concrete method to double the bet for a user in a specific game (only change is_double flag)
    protected function setDoubleBetFlag(string $userId, string $gameId): bool
    {
        $query = "UPDATE game_bets SET is_double = 1 WHERE game_id = ? AND user_id = ? AND is_double = 0";
        $params = [$gameId, $userId];
        return $this->executeStatement($query, $params);
    }
}
