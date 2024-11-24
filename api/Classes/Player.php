<?php
namespace blackjack;

use blackjack\Helpers\PlayerDatabaseHelper;

class Player extends PlayerDatabaseHelper
{
    private Deck $deck;
    private Game $game;

    public function __construct(\Mysqli $conn, Deck $deck, Game $game)
    {
        $this->deck = $deck;
        $this->game = $game;
        parent::__construct($conn);
    }

    public function getPlayerHand(string $userId): array
    {
        return $this->fetchPlayerHand($userId);  // Calls the parent method
    }

    public function clearPlayerHand(string $userId, string $gameId): bool
    {
            return $this->deletePlayerHand($userId, $gameId);
    }

    // Update a player's hand in the 'users' table
    public function updatePlayerHand(string $playerId, $hand, bool $overwrite = false): bool
    {
        return $this->setPlayerHand($playerId, $hand, $overwrite);  // Calls the parent method
    }

    // Check if a player is already in a game by their userId and gameId.
    public function isPlayerInGame(string $userId, string $gameId): bool
    {
        return $this->checkIfPlayerAlreadyInGame($userId, $gameId);  // Calls the parent method
    }

    // Get the list of player IDs for a specific game
    public function getGamePlayers(string $gameId): array
    {
        return $this->fetchGamePlayersId($gameId);  // Calls the parent method
    }

    // Assign a player to a game in the 'players' table
    public function assignPlayerToNewGame(string $gameId, string $userId): int
    {
        return $this->assignPlayerToGame($gameId, $userId);  // Calls the parent method
    }

    // Add a player to a specific slot in a game
    public function placePlayerInSlot(array $players, int $slot, string $userId, string $gameId): bool
    {
        return $this->addPlayerToSlot($players, $slot, $userId, $gameId);  // Calls the parent method
    }

    // Update all players in a game
    public function updateAllPlayersInGame(array $players, string $gameId): bool
    {
        return $this->updatePlayersInGame($players, $gameId);  // Calls the parent method
    }

    // Get a player's chips from the 'users' table
    public function getPlayerChips(string $userId): int
    {
        return $this->fetchPlayerChips($userId);  // Calls the parent method
    }

    public function clearPlayerChips(string $userId, string $gameId): bool
    {
        return $this->deletePlayerChips($userId, $gameId);
    }

    public function getAvailablePlayerSlot(array $players): ?int
    {
        // Find the first available slot (if any)
        foreach ($players as $index => $player) {
            if (empty($player)) {
                return $index + 1;
            }
        }
        return null; // No available slot
    }


    public function calculateHandStatus(string $userId): array
    {
        $hand = $this->getPlayerHand($userId);

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

    public function joinGame(string $userId, string $gameId): bool
    {
        if (!$this->game->checkIfGameExists($gameId)) {
            Response::error("Game of id $gameId doesn't exist");
        }

        if ($this->checkIfPlayerAlreadyInGame($userId, $gameId)) {
            Response::error("User is already in a game");
        }

        $playersTable = $this->fetchGamePlayersId($gameId);
        $availableSlot = $this->getAvailablePlayerSlot($playersTable["players_id"]);

        if (!$availableSlot) {
            return false;
        }
        $this->clearPlayerHand($userId, $gameId);
        $this->deck->dealCards($gameId, $userId, 2);
        $success = $this->addPlayerToSlot($playersTable["players_id"], $availableSlot, $userId, $gameId);

        return $success;
    }

    public function leaveGame(string $userId, string $gameId): bool
    {
        // Check if the game exists
        if (!$this->game->checkIfGameExists($gameId)) {
            Response::error("Game with id $gameId doesn't exist");
            return false;
        }

        // Check if the user is in the game
        if (!$this->checkIfPlayerAlreadyInGame($userId, $gameId)) {
            Response::error("User is not in the game");
            return false;
        }

        // Get the current list of players for the game
        $playersTable = $this->fetchGamePlayersId($gameId);

        // Check if the user is part of the game and determine the slot
        $slot = $this->findPlayerSlot($playersTable["players_id"], $userId);
        if ($slot === false) {
            Response::error("User not found in any player slot");
            return false;
        }

        // Remove the player from the game slot
        $playersTable["players_id"][$slot - 1] = null; // Clear the player from their slot

        // Update the players in the database
        $success = $this->updatePlayersInGame($playersTable["players_id"], $gameId);

        if (!$success) {
            Response::error("Failed to update players in game");
            return false;
        }


        //tbd

        //reset player hand etc

        Response::success("User has left the game successfully");
        return true;
    }



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
