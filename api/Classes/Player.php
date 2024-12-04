<?php
namespace blackjack;

use blackjack\Helpers\PlayerDatabaseHelper;

class Player extends PlayerDatabaseHelper
{

    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
        parent::__construct();
    }


    /**
     * Fetches the player's hand from the database.
     *
     * @param string $userId The user ID of the player.
     * @return array The player's hand.
     */
    public function getPlayerHand(string $userId): array
    {
        return $this->fetchPlayerHand($userId);
    }

    /**
     * Clears the player's hand from the database.
     *
     * @param string $userId The user ID of the player.
     * @param string $gameId The game ID the player is in.
     * @return bool True if successful, false otherwise.
     */
    public function clearPlayerHand(string $userId, string $gameId): bool
    {
        return $this->deletePlayerHand($userId, $gameId);
    }

    /**
     * Updates a player's hand in the database.
     *
     * @param string $playerId The player ID.
     * @param mixed $hand The player's hand to update.
     * @param bool $overwrite Whether to overwrite the existing hand.
     * @param string $gameId Game ID.
     * @return bool True if successful, false otherwise.
     */
    public function updatePlayerHand(string $playerId, array $hand, string $gameId, bool $overwrite = false): bool
    {
        return $this->setOrUpdatePlayerHand($playerId, $hand, $gameId, $overwrite);
    }

    /**
     * Checks if a player is already in a game.
     *
     * @param string $userId The user ID.
     * @param string $gameId The game ID.
     * @return bool True if the player is in the game, false otherwise.
     */
    public function isPlayerInGame(string $userId, string $gameId): bool
    {
        return $this->checkIfPlayerAlreadyInGame($userId, $gameId);
    }

    /**
     * Fetches the list of player IDs for a specific game.
     *
     * @param string $gameId The game ID.
     * @return array The list of player IDs.
     */
    public function getGamePlayers(string $gameId): array
    {
        return $this->fetchGamePlayersId($gameId);
    }

    /**
     * Assigns a player to a new game.
     *
     * @param string $gameId The game ID.
     * @param string $userId The user ID.
     * @return int The result of the assignment.
     */
    public function assignPlayerToNewGame(string $gameId, string $userId): int
    {
        return $this->assignPlayerToGame($gameId, $userId);
    }

    /**
     * Places a player in a specific slot in a game.
     *
     * @param array $players The list of players.
     * @param int $slot The slot number.
     * @param string $userId The user ID.
     * @param string $gameId The game ID.
     * @return bool True if successful, false otherwise.
     */
    public function placePlayerInSlot(array $players, int $slot, string $userId, string $gameId): bool
    {
        return $this->addPlayerToSlot($players, $slot, $userId, $gameId);
    }



    /**
     * Updates all players in a specific game.
     *
     * @param array $players The list of players.
     * @param string $gameId The game ID.
     * @return bool True if successful, false otherwise.
     */
    public function updateAllPlayersInGame(array $players, string $gameId): bool
    {
        return $this->updatePlayersInGame($players, $gameId);
    }

    /**
     * Check if the active user is set for a given game.
     *
     * @param string $gameId The game ID to check.
     * @return bool Returns true if an active user is set, false otherwise.
     */
    public function isActiveUserSet(string $gameId): bool
    {
        return $this->selectActiveUser($gameId);
    }

    /**
     * Set the active user for a given game.
     *
     * @param string $gameId The game ID to update.
     * @param string $userId The user ID to set as active.
     * @return bool Returns true if the active user was successfully set, false otherwise.
     */
    public function setActiveUser(string $gameId, string $userId): bool
    {
        return $this->updateActiveUser($gameId, $userId);
    }

    /**
     * Gets a player's chips from the database.
     *
     * @param string $userId The user ID of the player.
     * @return int The number of chips the player has.
     */
    public function getPlayerChips(string $userId): int
    {
        return $this->fetchPlayerChips($userId);
    }

    /**
     * Clears a player's chips from the database.
     *
     * @param string $userId The user ID of the player.
     * @param string $gameId The game ID the player is in.
     * @return bool True if successful, false otherwise.
     */
    public function clearPlayerChips(string $userId, string $gameId): bool
    {
        return $this->deletePlayerChips($userId, $gameId);
    }



    /**
     * Finds an available player slot in the game.
     *
     * @param array $players The list of players.
     * @return int|null The available slot, or null if none are available.
     */
    public function getAvailablePlayerSlot(array $players): ?int
    {
        if (empty($players)) {
            return 1;
        }
        foreach ($players as $index => $player) {
            if (empty($player)) {
                return $index + 1;
            }
        }
        return null;
    }



    /**
     * Calculates the status of a player's hand (e.g., total value, blackjack, bust).
     *
     * @param string $userId The user ID of the player.
     * @return array The calculated hand status.
     */
    public function calculateHandStatus(string $userId): array
    {
        $hand = $this->getPlayerHand($userId);

        if (isset($hand['rank'], $hand['value'])) {
            $hand = [$hand];
        }


        $totalValue = 0;
        $aceCount = 0;
        foreach ($hand as $card) {
            $totalValue += $card['value'];
            if ($card['rank'] == 'Ace') {
                $aceCount++;
            }
        }
        while ($totalValue > 21 && $aceCount > 0) {
            $totalValue -= 10;
            $aceCount--;
        }

        return [
            'hand' => $hand,
            'total' => $totalValue,
            'aceCount' => $aceCount,
            'isBlackjack' => $totalValue === 21,
            'isBust' => $totalValue > 21
        ];
    }

    /**
     * Check if a player's hand is a blackjack (21 with an Ace).
     *
     * @param string $userId The user ID
     * @return bool True if the player has a blackjack, false otherwise
     */
    public function checkBlackjack(string $userId): bool
    {
        $handStatus = $this->calculateHandStatus($userId);

        return $handStatus["total"] === 21 && $handStatus["aceCount"] === 1;
    }

    /**
     * Makes the player join a specific game.
     *
     * @param string $userId The user ID of the player.
     * @param string $gameId The game ID the player is joining.
     * @return bool True if successful, false otherwise.
     */
    public function joinGame(string $userId, string $gameId): bool
    {
        if (!$this->game->checkIfGameExists($gameId)) {
            Response::error("Game of id $gameId doesn't exist");
        }

        if ($this->checkIfPlayerAlreadyInGame($userId, $gameId)) {
            Response::error("User is already in a game");
        }

        $playersTable = $this->fetchGamePlayersId($gameId);
        $availableSlot = $this->getAvailablePlayerSlot($playersTable);
        if (!$availableSlot) {
            return false;
        }
        $this->clearPlayerHand($userId, $gameId);
        return $this->addPlayerToSlot($availableSlot, $userId, $gameId);
    }

    /**
     * Makes the player leave a specific game.
     *
     * @param string $userId The user ID of the player.
     * @param string $gameId The game ID the player is leaving.
     * @return bool True if successful, false otherwise.
     */
    public function leaveGame(string $userId, string $gameId): bool
    {

        if (!$this->game->checkIfGameExists($gameId)) {
            Response::error("Game with id $gameId doesn't exist");
            return false;
        }

        if (!$this->checkIfPlayerAlreadyInGame($userId, $gameId)) {
            Response::error("User is not in the game");
            return false;
        }

        $playersTable = $this->fetchGamePlayersId($gameId);

        $slot = $this->findPlayerSlot($playersTable["players_id"], $userId);
        if ($slot === false) {
            Response::error("User not found in any player slot");
            return false;
        }

        $playersTable["players_id"][$slot - 1] = null;

        $success = $this->updatePlayersInGame($playersTable["players_id"], $gameId);

        if (!$success) {
            Response::error("Failed to update players in game");
            return false;
        }

        //TODO reset player hand etc


        Response::success("User has left the game successfully");
        return true;
    }

    /**
     * Finds the slot of a player in the game.
     *
     * @param array $players The list of players.
     * @param string $userId The user ID of the player.
     * @return int|null The player's slot, or null if not found.
     */
    private function findPlayerSlot(array $players, string $userId): ?int
    {
        foreach ($players as $index => $player) {
            if ($player === $userId) {
                return $index + 1; // Return 1-based index
            }
        }
        return false;
    }
}
