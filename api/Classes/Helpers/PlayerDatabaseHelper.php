<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;

abstract class PlayerDatabaseHelper extends DbHelper
{
    abstract protected function getPlayerHand(string $userId): array;
    abstract protected function updatePlayerHand(string $playerId, $hand, bool $overwrite = false): bool;
    abstract protected function isPlayerInGame(string $userId, string $gameId): bool;
    abstract protected function getGamePlayers(string $gameId): array;
    abstract protected function assignPlayerToNewGame(string $gameId, string $userId): int;
    abstract protected function placePlayerInSlot(array $players, int $slot, string $userId, string $gameId): bool;
    abstract protected function updateAllPlayersInGame(array $players, string $gameId): bool;
    abstract protected function getPlayerChips(string $userId): int;
    abstract protected function clearPlayerHand(string $userId, string $gameId): bool;
    abstract protected function clearPlayerChips(string $userId, string $gameId): bool;

    /**
     * Get a player's hand from the 'users' table
     */
    protected function fetchPlayerHand(string $userId): array
    {
        $result = $this->executeStatement("SELECT hand FROM users WHERE user_id = ?", [$userId], true);
        return isset($result[0]['hand']) ? json_decode($result[0]['hand'], true) : [];
    }

    protected function deletePlayerChips(string $userId, string $gameId): bool
    {
        $sql = "DELETE FROM bets WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];

        // Execute the statement using the existing method
        if ($this->executeStatement($sql, $params)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update a player's hand in the 'users' table
     */
    protected function setPlayerHand(string $playerId, $hand, bool $overwrite = false): bool
    {
        try {
            $updatedHand = $overwrite ? (is_array($hand) ? $hand : [$hand]) : array_merge($this->getPlayerHand($playerId), $hand);
            $updatedHandJson = json_encode($updatedHand);
            return $this->executeStatement("UPDATE users SET hand = ? WHERE user_id = ?", [$updatedHandJson, $playerId]);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
            return false;
        }
    }

    /**
     * Check if a player is already in a game by their userId and gameId.
     */
    protected function checkIfPlayerAlreadyInGame(string $userId, string $gameId): bool
    {
        $result = $this->executeStatement("SELECT COUNT(*) AS count FROM players WHERE game_id = ? AND user_id = ?", [$gameId, $userId], true);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }

    /**
     * Get the list of player IDs for a specific game from the 'players' table
     */
    protected function fetchGamePlayersId(string $gameId): array
    {
        $result = $this->executeStatement("SELECT user_id FROM players WHERE game_id = ?", [$gameId], true);
        return array_map(fn($row) => $row['user_id'], $result);
    }

    /**
     * Assign a player to a game in the 'players' table
     */
    protected function assignPlayerToGame(string $gameId, string $userId): int
    {
        if ($this->executeStatement("INSERT INTO players (game_id, user_id) VALUES (?, ?)", [$gameId, $userId])) {
            return $this->conn->insert_id;
        }
        return false;
    }

    protected function deletePlayerHand($gameId, $userId): bool
    {

        $sql = "DELETE FROM hand WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];

        // Execute the statement using the existing method
        if ($this->executeStatement($sql, $params)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Add a player to a specific slot in a game
     */
    protected function addPlayerToSlot(array $players, int $slot, string $userId, string $gameId): bool
    {
        $players[$slot - 1] = $userId;
        return $this->executeStatement("UPDATE players SET user_id = ? WHERE game_id = ? AND slot = ?", [$userId, $gameId, $slot]);
    }

    /**
     * Update all players in a game
     */
    protected function updatePlayersInGame(array $players, string $gameId): bool
    {
        // Assuming you want to update all players in the game, this might need to be done with multiple queries.
        foreach ($players as $slot => $userId) {
            if (!$this->executeStatement("UPDATE players SET user_id = ? WHERE game_id = ? AND slot = ?", [$userId, $gameId, $slot + 1])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get a player's chips from the 'users' table
     */
    protected function fetchPlayerChips(string $userId): int
    {
        $result = $this->executeStatement("SELECT chips FROM users WHERE user_id = ?", [$userId], true);
        return isset($result[0]['chips']) ? $result[0]['chips'] : 0;
    }
}
