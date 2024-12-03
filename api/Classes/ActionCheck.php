<?php
namespace blackjack;

use blackjack\Helpers\ActionCheckDatabaseHelper;

class ActionCheck extends ActionCheckDatabaseHelper
{
    private Player $player;
    private Bet $bet;

    /**
     * Constructor to initialize player and bet objects
     *
     * @param Player $player The player object
     * @param Bet $bet The bet object
     */
    public function __construct(Player $player, Bet $bet)
    {
        $this->player = $player;
        $this->bet = $bet;
        parent::__construct();
    }

    /**
     * Get the active player ID for a game.
     *
     * @param string $gameId The game ID
     * @return string The active player ID
     */
    public function getActivePlayerId(string $gameId):string
    {
        return $this->fetchActivePlayerId($gameId);
    }

    /**
     * Check if it's the player's turn to play.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if it's the player's turn, false otherwise
     */
    private function isPlayerTurn(string $userId, string $gameId): bool
    {
        $playerId = $this->getActivePlayerId($gameId);
        if ($userId !== $playerId) {
            return false;
        }
        return true;
    }

    /**
     * Get the total value of a player's hand.
     *
     * @param string $userId The user ID
     * @return int The total value of the player's hand
     */
    private function getHandTotal(string $userId): int
    {
        $hand = $this->player->calculateHandStatus($userId);
        return $hand["total"];
    }

    /**
     * Get the player's current chip count.
     *
     * @param string $userId The user ID
     * @return int The player's chip count
     */
    private function getPlayerChips(string $userId): int
    {
        return $this->player->getPlayerChips($userId);
    }

    /**
     * Check if a player can hit.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the player can hit, false otherwise
     */
    public function canHit($userId, $gameId): bool
    {
        if (!$this->isPlayerTurn($userId, $gameId)) {
            return false;
        }

        $handTotal = $this->getHandTotal($userId);

        if ($handTotal >= 21) {
            return false;
        }

        return true;
    }

    /**
     * Check if a player can stand.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the player can stand, false otherwise
     */
    public function canStand($userId, $gameId): bool
    {
        return $this->isPlayerTurn($userId, $gameId);
    }

    /**
     * Check if a player can double their bet.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the player can double, false otherwise
     */
    public function canDouble($userId, $gameId): bool
    {
        if (!$this->isPlayerTurn($userId, $gameId)) {
            return false;
        }

        $handTotal = $this->getHandTotal($userId);
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);

        if (in_array($handTotal, [9, 10, 11]) && $playerChips >= $currentBet * 2) {
            return true;
        }

        return false;
    }

    /**
     * Check if a player can split their hand.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the player can split, false otherwise
     */
    public function canSplit($userId, $gameId)
    {
        if (!$this->isPlayerTurn($userId, $gameId)) {
            return false;
        }

        $hand = $this->player->getPlayerHand($userId);
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);

        if (count($hand) === 2 && $hand[0]['rank'] === $hand[1]['rank'] && $playerChips >= $currentBet) {
            return true;
        }

        return false;
    }

    /**
     * Check if a player can surrender.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the player can surrender, false otherwise
     */
    public function canSurrender($userId, $gameId): bool
    {
        if (!$this->isPlayerTurn($userId, $gameId)) {
            return false;
        }

        $handTotal = $this->getHandTotal($userId);
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);

        if (in_array($handTotal, [15, 16, 17]) && $playerChips >= $currentBet / 2) {
            return true;
        }

        return false;
    }

    /**
     * Check if a player can take insurance.
     *
     * @param string $userId The user ID
     * @param string $gameId The game ID
     * @return bool True if the player can take insurance, false otherwise
     */
    public function canTakeInsurance($userId, $gameId): bool
    {
        if (!$this->isPlayerTurn($userId, $gameId)) {
            return false;
        }

        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);

        if ($playerChips >= $currentBet / 2) {
            return true;
        }

        return false;
    }
}
