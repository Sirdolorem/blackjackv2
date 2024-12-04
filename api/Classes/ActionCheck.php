<?php
namespace blackjack;

use blackjack\Helpers\ActionCheckDatabaseHelper;

class ActionCheck extends ActionCheckDatabaseHelper
{
    private Player $player;
    private Bet $bet;
    private Deck $deck;
    private Game $game;

    public function __construct(Player $player, Bet $bet, Deck $deck, Game $game)
    {
        $this->player = $player;
        $this->bet = $bet;
        $this->deck = $deck;
        $this->game = $game;
        parent::__construct();
    }

    /**
     * Common Game Status Check.
     *
     * @param string $gameId The ID of the game being checked.
     *
     * @return bool Returns true if the game is active, false otherwise.
     */
    private function isGameActive(string $gameId): bool
    {
        return $this->game->checkGameStatus($gameId) == "active";
    }

    /**
     * Common Player Turn Check.
     *
     * @param string $userId The ID of the player whose turn is being checked.
     * @param string $gameId The ID of the game where the turn is being checked.
     *
     * @return bool Returns true if it's the given user's turn in the specified game, false otherwise.
     */
    public function isPlayerTurn(string $userId, string $gameId): bool
    {
        return $userId === $this->getActivePlayerId($gameId);
    }

    /**
     * Validates common conditions for all actions.
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     * @param string $actionType The type of action being validated (e.g., 'hit', 'stand').
     *
     * @return bool Returns true if all common conditions are satisfied
     * (e.g., game is active, player’s turn, valid bet, non-empty deck).
     * Returns false if any of the conditions fail.
     */
    private function validateCommonConditions(string $userId, string $gameId, string $actionType): bool
    {
        if (!$this->isGameActive($gameId)) {
            return false;
        }

        if (!$this->isPlayerTurn($userId, $gameId)) {
            return false;
        }

        if (!$this->bet->getCurrentBet($userId, $gameId)) {
            return false;
        }

        if ($this->deck->checkIfDeckEmpty($gameId)) {
            return false;
        }

        if ($actionType == 'hit' || $actionType == 'double') {
            if (!$this->isHandAbove21($userId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns whether the hand total is greater than or equal to 21.
     *
     * @param string $userId The ID of the player whose hand total is being checked.
     *
     * @return bool Returns true if the player's hand total is >= 21, false otherwise.
     */
    private function isHandAbove21(string $userId): bool
    {
        return $this->getHandTotal($userId) >= 21;
    }

    /**
     * Determines if the player can "hit".
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     *
     * @return bool Returns true if the player can hit (all conditions met), false otherwise.
     */
    public function canHit(string $userId, string $gameId): bool
    {
        if (!$this->validateCommonConditions($userId, $gameId, 'hit')) {
            return false;
        }

        if ($this->lastPlayerAction($userId) == "stand" || $this->lastPlayerAction($userId) == "split") {
            return false;
        }

        return true;
    }

    /**
     * Determines if the player can "stand".
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     *
     * @return bool Returns true if the player can stand (all conditions met), false otherwise.
     */
    public function canStand(string $userId, string $gameId): bool
    {
        return $this->validateCommonConditions($userId, $gameId, 'stand');
    }

    /**
     * Determines if the player can "double".
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     *
     * @return bool Returns true if the player can double (all conditions met), false otherwise.
     */
    public function canDouble(string $userId, string $gameId): bool
    {
        if (!$this->validateCommonConditions($userId, $gameId, 'double')) {
            return false;
        }

        $playerChips = $this->player->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);
        if ($playerChips < $currentBet * 2) {
            return false;
        }

        return true;
    }

    /**
     * Determines if the player can "split".
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     *
     * @return bool Returns true if the player can split (all conditions met), false otherwise.
     */
    public function canSplit(string $userId, string $gameId): bool
    {
        if (!$this->validateCommonConditions($userId, $gameId, 'split')) {
            return false;
        }

        $hand = $this->player->getPlayerHand($userId);
        $playerChips = $this->player->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);

        return count($hand) === 2 && $hand[0]['rank'] === $hand[1]['rank'] && $playerChips >= $currentBet * 2;
    }

    /**
     * Determines if the player can "surrender".
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     *
     * @return bool Returns true if the player can surrender (all conditions met), false otherwise.
     */
    public function canSurrender(string $userId, string $gameId): bool
    {
        if (!$this->validateCommonConditions($userId, $gameId, 'surrender')) {
            return false;
        }

        $playerChips = $this->player->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);
        return $this->getHandTotal($userId) < 21 && $playerChips >= $currentBet / 2;
    }

    /**
     * Determines if the player can take "insurance".
     *
     * @param string $userId The ID of the player performing the action.
     * @param string $gameId The ID of the game where the action is being performed.
     *
     * @return bool Returns true if the player can take insurance (all conditions met), false otherwise.
     */
    public function canTakeInsurance(string $userId, string $gameId): bool
    {
        if (!$this->validateCommonConditions($userId, $gameId, 'insurance')) {
            return false;
        }

        $playerChips = $this->player->getPlayerChips($userId);
        $currentBet = $this->bet->getCurrentBet($userId, $gameId);
        return $playerChips >= $currentBet / 2;
    }

    /**
     * Gets the total value of the player's hand.
     *
     * @param string $userId The ID of the player whose hand total is being calculated.
     *
     * @return int The total value of the player's hand.
     */
    private function getHandTotal(string $userId): int
    {
        $hand = $this->player->calculateHandStatus($userId);
        return $hand["total"];
    }

    /**
     * Fetches the last action performed by the player.
     *
     * @param string $userId The ID of the player whose last action is being fetched.
     *
     * @return string The last action performed by the player (e.g., "hit", "stand").
     */
    public function lastPlayerAction(string $userId): string
    {
        return $this->fetchLastPlayerAction($userId);
    }

    /**
     * Fetches the active player ID for the given game.
     *
     * @param string $gameId The ID of the game whose active player ID is being fetched.
     *
     * @return string The ID of the active player in the game.
     */
    protected function getActivePlayerId(string $gameId): string
    {
        return $this->fetchActivePlayerId($gameId);
    }
}
