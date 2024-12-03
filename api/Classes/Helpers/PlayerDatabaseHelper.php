<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;

abstract class PlayerDatabaseHelper extends DbHelper
{
    /**
     * Fetches the hand of a player from the database.
     *
     * @param string $userId The ID of the player.
     * @return array The player's hand, or an empty array if not found.
     */
    abstract protected function getPlayerHand(string $userId): array;

    /**
     * Updates the hand of a player in the database.
     *
     * @param string $playerId The ID of the player.
     * @param mixed $hand The new hand to set.
     * @param bool $overwrite Whether to overwrite the hand or merge it.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    abstract protected function updatePlayerHand(string $playerId, $hand, bool $overwrite = false): bool;

    /**
     * Checks if a player is part of a game.
     *
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the player is in the game, false otherwise.
     */
    abstract protected function isPlayerInGame(string $userId, string $gameId): bool;

    /**
     * Fetches all players in a game.
     *
     * @param string $gameId The ID of the game.
     * @return array An array of player IDs.
     */
    abstract protected function getGamePlayers(string $gameId): array;

    /**
     * Assigns a player to a new game.
     *
     * @param string $gameId The ID of the game.
     * @param string $userId The ID of the player.
     * @return int The ID of the inserted player record.
     */
    abstract protected function assignPlayerToNewGame(string $gameId, string $userId): int;

    /**
     * Places a player in a specific slot in the game.
     *
     * @param array $players An array of players.
     * @param int $slot The slot number.
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    abstract protected function placePlayerInSlot(array $players, int $slot, string $userId, string $gameId): bool;

    /**
     * Updates all players in a game.
     *
     * @param array $players An array of players.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    abstract protected function updateAllPlayersInGame(array $players, string $gameId): bool;

    /**
     * Fetches the chips of a player from the database.
     *
     * @param string $userId The ID of the player.
     * @return int The number of chips the player has.
     */
    abstract protected function getPlayerChips(string $userId): int;

    /**
     * Clears the hand of a player in the database.
     *
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    abstract protected function clearPlayerHand(string $userId, string $gameId): bool;

    /**
     * Clears the chips of a player in the database.
     *
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the operation was successful, false otherwise.
     */
    abstract protected function clearPlayerChips(string $userId, string $gameId): bool;

    /**
     * Fetches a player's hand from the 'users' table.
     *
     * @param string $userId The ID of the player.
     * @return array The player's hand, or an empty array if not found.
     */
    protected function fetchPlayerHand(string $userId): array
    {
        $result = $this->executeStatement("SELECT hand FROM users WHERE user_id = ?", [$userId], true);
        return isset($result[0]['hand']) ? json_decode($result[0]['hand'], true) : [];
    }

    /**
     * Deletes the chips of a player in a specific game.
     *
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    protected function deletePlayerChips(string $userId, string $gameId): bool
    {
        $sql = "DELETE FROM bets WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];
        return $this->executeStatement($sql, $params);
    }

    /**
     * Updates a player's hand in the 'users' table.
     *
     * @param string $playerId The ID of the player.
     * @param mixed $hand The new hand to set.
     * @param bool $overwrite Whether to overwrite the hand or merge it.
     * @return bool Returns true if the update was successful, false otherwise.
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
     * Checks if a player is already in a game.
     *
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the player is in the game, false otherwise.
     */
    protected function checkIfPlayerAlreadyInGame(string $userId, string $gameId): bool
    {
        $result = $this->executeStatement("SELECT COUNT(*) AS count FROM players WHERE game_id = ? AND user_id = ?", [$gameId, $userId], true);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }

    /**
     * Fetches the list of player IDs for a specific game from the 'players' table.
     *
     * @param string $gameId The ID of the game.
     * @return array An array of player IDs.
     */
    protected function fetchGamePlayersId(string $gameId): array
    {
        $result = $this->executeStatement("SELECT user_id FROM players WHERE game_id = ?", [$gameId], true);
        return array_map(fn($row) => $row['user_id'], $result);
    }

    /**
     * Assigns a player to a game in the 'players' table.
     *
     * @param string $gameId The ID of the game.
     * @param string $userId The ID of the player.
     * @return int The ID of the inserted player record.
     */
    protected function assignPlayerToGame(string $gameId, string $userId): int
    {
        if ($this->executeStatement("INSERT INTO players (game_id, user_id) VALUES (?, ?)", [$gameId, $userId])) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Deletes the hand of a player in a specific game.
     *
     * @param string $gameId The ID of the game.
     * @param string $userId The ID of the player.
     * @return bool Returns true if the deletion was successful, false otherwise.
     */
    protected function deletePlayerHand(string $gameId, string $userId): bool
    {
        $sql = "DELETE FROM hand WHERE game_id = ? AND user_id = ?";
        $params = [$gameId, $userId];
        return $this->executeStatement($sql, $params);
    }

    /**
     * Adds a player to a specific slot in a game.
     *
     * @param array $players An array of players.
     * @param int $slot The slot number.
     * @param string $userId The ID of the player.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the addition was successful, false otherwise.
     */
    protected function addPlayerToSlot(array $players, int $slot, string $userId, string $gameId): bool
    {
        $players[$slot - 1] = $userId;
        return $this->executeStatement("UPDATE players SET user_id = ? WHERE game_id = ? AND slot = ?", [$userId, $gameId, $slot]);
    }

    /**
     * Updates all players in a game.
     *
     * @param array $players An array of players.
     * @param string $gameId The ID of the game.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    protected function updatePlayersInGame(array $players, string $gameId): bool
    {
        foreach ($players as $slot => $userId) {
            if (!$this->executeStatement("UPDATE players SET user_id = ? WHERE game_id = ? AND slot = ?", [$userId, $gameId, $slot + 1])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fetches the chips of a player from the 'users' table.
     *
     * @param string $userId The ID of the player.
     * @return int The number of chips the player has.
     */
    protected function fetchPlayerChips(string $userId): int
    {
        $result = $this->executeStatement("SELECT chips FROM users WHERE user_id = ?", [$userId], true);
        return isset($result[0]['chips']) ? $result[0]['chips'] : 0;
    }
}
