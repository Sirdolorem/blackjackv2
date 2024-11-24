<?php
namespace blackjack;

use blackjack\Helpers\BetDatabaseHelper;
use blackjack\Response;

class Bet extends BetDatabaseHelper
{
    public function __construct(\mysqli $connection)
    {
        parent::__construct($connection);
    }

    public function getCurrentBet(string $userId, string $gameId): array|null
    {
        return $this->fetchCurrentBet($userId, $gameId);
    }

// Add a bet for the user in a specific game
    public function placeBet(string $userId, string $gameId, int $betAmount): bool
    {
        if ($betAmount <= 0) {
            Response::error("Bet amount must be greater than zero.");
            return false;
        }

    // Call the method from BetDatabaseHelper to add the bet
        return $this->insertBet($userId, $gameId, $betAmount);
    }

// Update the user's bet in the game
    public function modifyBet(string $userId, string $gameId, int $newBetAmount): bool
    {
        if ($newBetAmount <= 0) {
            Response::error("New bet amount must be greater than zero.");
            return false;
        }

    // Call the method from BetDatabaseHelper to update the bet
        return $this->updateBetAmount($userId, $gameId, $newBetAmount);
    }

// Remove the bet for the user in a specific game
    public function deleteBet(string $userId, string $gameId): bool
    {
    // Call the method from BetDatabaseHelper to remove the bet
        return $this->deleteBetForUser($userId, $gameId);
    }

// Double the user's bet in a specific game
    public function doubleBet(string $userId, string $gameId): bool
    {
    // Get the current bet data to double it
        $currentBetData = $this->getCurrentBet($userId, $gameId);

        if ($currentBetData === null) {
            Response::error("No bet found to double.");
            return false;
        }

    // Check if the bet is already doubled
        if ($currentBetData["is_double"]) {
            Response::error("The bet is already doubled.");
            return false;
        }

    // Call the method from BetDatabaseHelper to update the bet as doubled
        return $this->setDoubleBetFlag($userId, $gameId);
    }
}
