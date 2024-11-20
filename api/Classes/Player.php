<?php
namespace blackjack;

use blackjack\Helpers\GameDatabaseHelper;
use blackjack\Helpers\PlayerDatabaseHelper;

class Player extends PlayerDatabaseHelper
{
    private GameDatabaseHelper $gameDbHelper;
    private Deck $deck;

    public function __construct()
    {
        $this->gameDbHelper = new GameDatabaseHelper();
        $this->deck = new Deck();
        parent::__construct();
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
        if (!$this->gameDbHelper->checkIfGameExists($gameId)) {
            Response::error("Game of id $gameId doesn't exist");
        }

        if ($this->checkIfPlayerAlreadyInGame($userId, $gameId)) {
            Response::error("User is already in a game");
        }

        $playersTable = $this->getGamePlayersId($gameId);
        $availableSlot = $this->getAvailablePlayerSlot($playersTable["players_id"]);

        if (!$availableSlot) {
            return false;
        }
        $this->clearPlayerHand($userId, $gameId);
        $this->deck->dealCards($userId, $gameId, 2);
        $success = $this->addPlayerToSlot($playersTable["players_id"], $availableSlot, $userId, $gameId);

        return $success;
    }

    public function leaveGame(string $userId, string $gameId): bool
    {
        // Check if the game exists
        if (!$this->gameDbHelper->checkIfGameExists($gameId)) {
            Response::error("Game with id $gameId doesn't exist");
            return false;
        }

        // Check if the user is in the game
        if (!$this->checkIfPlayerAlreadyInGame($userId, $gameId)) {
            Response::error("User is not in the game");
            return false;
        }

        // Get the current list of players for the game
        $playersTable = $this->getGamePlayersId($gameId);

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
