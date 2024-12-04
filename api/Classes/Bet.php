<?php
namespace blackjack;

use blackjack\Helpers\BetDatabaseHelper;
use blackjack\Response;

class Bet extends BetDatabaseHelper
{
    private User $user;
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Get the current bet for a user in a specific game.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return array|null The current bet data or null if no bet exists
     */
    public function getCurrentBet(string $userId, string $gameId): ?array
    {
        return $this->fetchCurrentBet($userId, $gameId);
    }

    /**
     * Add chips to the user's balance after a win.
     *
     * @param string $userId The user ID
     * @param int $winAmount The amount of chips to add
     * @return int|bool New chip balance if the chips are successfully added, false otherwise
     */
    public function addChipsOnWin(string $userId, string $gameId, int $winAmount): int|bool
    {
        if ($winAmount <= 0) {
            Response::error("Invalid win amount.");
            return false;
        }

        $userChips = $this->user->getUserChips($userId, $gameId);

        $newChipBalance = $userChips + $winAmount;

        if ($this->user->updateUserChips($userId, $newChipBalance)) {
            return $newChipBalance;
        }

        Response::error("Failed to add chips.");
        return false;
    }


    /**
     * Place a bet for the user in a specific game.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @param int $betAmount The bet amount to be placed
     * @return bool True if the bet is successfully placed, false otherwise
     */
    public function placeBet(string $userId, string $gameId, int $betAmount): bool
    {
        if ($betAmount <= 0) {
            return false;
        }

        return $this->insertBet($userId, $gameId, $betAmount);
    }

    /**
     * Modify the user's bet in a specific game.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @param int $newBetAmount The new bet amount
     * @return bool True if the bet is successfully updated, false otherwise
     */
    public function modifyBet(string $userId, string $gameId, int $newBetAmount): bool
    {
        if ($newBetAmount <= 0) {
            return false;
        }

        return $this->updateBetAmount($userId, $gameId, $newBetAmount);
    }

    /**
     * Delete the user's bet for a specific game.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the bet is successfully deleted, false otherwise
     */
    public function deleteBet(string $userId, string $gameId): bool
    {
        return $this->deleteBetForUser($userId, $gameId);
    }

    /**
     * Double the user's bet in a specific game.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the bet is successfully doubled, false otherwise
     */
    public function doubleBet(string $userId, string $gameId): bool
    {
        $currentBetData = $this->getCurrentBet($userId, $gameId);
        if ($currentBetData === null) {
            Response::error("No bet found to double.");
            return false;
        }

        if ($currentBetData["is_double"]) {
            Response::error("The bet is already doubled.");
            return false;
        }



        return $this->setDoubleBetFlag($userId, $gameId);
    }
}
