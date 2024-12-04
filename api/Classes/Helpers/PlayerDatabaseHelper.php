<?php

namespace blackjack\Helpers;

use blackjack\Helpers\DbHelper\DbHelper;
use blackjack\Response;
use PHP_CodeSniffer\Tests\Core\Tokenizers\PHP\ResolveSimpleTokenTest;

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
     * @param array $hand The new hand to set.
     * @param bool $overwrite Whether to overwrite the hand or merge it.
     * @return bool Returns true if the update was successful, false otherwise.
     */
    abstract protected function updatePlayerHand(string $playerId, array $hand, string $gameId, bool $overwrite = false): bool;

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

    /**
     * Abstract method to check if an active user is set for a given game.
     *
     * @param string $gameId The game ID to check.
     * @return bool Returns true if an active user is set, false otherwise.
     */
    abstract public function isActiveUserSet(string $gameId): bool;

    /**
     * Abstract method to set the active user for a given game.
     *
     * @param string $gameId The game ID to update.
     * @param string $userId The user ID to set as active.
     * @return bool Returns true if the active user was successfully set, false otherwise.
     */
    abstract public function setActiveUser(string $gameId, string $userId): bool;

    protected function fetchPlayerHand(string $userId): array
    {
        $result = $this->executeStatement("SELECT hand FROM hands WHERE user_id = ?", [$userId], true);
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
     * Check if the active user is set for a given game.
     *
     * @param string $gameId The game ID to check.
     * @return bool Returns true if an active user is set, false otherwise.
     */
    protected function selectActiveUser(string $gameId): bool
    {
        $result = $this->executeStatement("SELECT active_user FROM games WHERE game_id = ?", [$gameId]);

        return !empty($result[0]['active_user']);
    }

    /**
     * Set the active user for a given game.
     *
     * @param string $gameId The game ID to update.
     * @param string $userId The user ID to set as active.
     * @return bool Returns true if the active user was successfully set, false otherwise.
     */
    protected function updateActiveUser(string $gameId, string $userId): bool
    {
        return $this->executeStatement("UPDATE games SET active_user = ? WHERE game_id = ?", [$userId, $gameId]);
    }

    /**
     * Checks if the player has a hand and either updates or inserts the hand.
     *
     * @param string $playerId The ID of the player.
     * @param array $hand The hand data (can be an array [[]] or a single card []).
     * @param bool $overwrite Whether to overwrite the existing hand or not (default is false).
     * @return bool Returns true if the hand is successfully updated or inserted, false otherwise.
     */
    protected function setOrUpdatePlayerHand(string $playerId, array $hand, string $gameId, bool $overwrite = false): bool
    {
        try {
            $currentHand = $this->getPlayerHand($playerId);

            (!$currentHand) ? $isCurrentHandEmpty = true : $isCurrentHandEmpty = false;
            if ($overwrite) {
                $currentHand = [$hand];
            } else {
                if (empty($currentHand)) {
                    $currentHand = [$hand];
                } else {
                    array_push($currentHand, $hand);
                }
            }

            $updatedHandJson = json_encode($currentHand, true);

            // Update if the hand exists, otherwise insert
            if (!$isCurrentHandEmpty) {
                return $this->executeStatement(
                    "UPDATE hands SET hand = ? WHERE user_id = ?",
                    [$updatedHandJson, $playerId]
                );
            } else {
                return $this->executeStatement(
                    "INSERT INTO hands (user_id, hand, game_id) VALUES (?, ?, ?)",
                    [$playerId, $updatedHandJson, $gameId]
                );
            }
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
        $sql = "DELETE FROM hands WHERE game_id = ? AND user_id = ?";
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
    protected function addPlayerToSlot(int $slot, string $userId, string $gameId): bool
    {
        return $this->executeStatement("INSERT INTO players (user_id, game_id, slot) VALUES (?, ?, ?)", [$userId, $gameId, $slot]);
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
