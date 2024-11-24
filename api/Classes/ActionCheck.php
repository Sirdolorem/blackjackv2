<?php

namespace blackjack;

use blackjack\Helpers\ActionCheckDatabaseHelper;

class ActionCheck extends ActionCheckDatabaseHelper
{
    private Player $player;

    public function __construct(\mysqli $connection ,Player $player)
    {
        $this->player = $player;
        parent::__construct($connection);
    }

    public function getActivePlayerId(string $gameId)
    {
        return $this->fetchActivePlayerId($gameId);
    }


    // Check if a player can hit
    public function canHit($userId, $gameId)
    {
        $playerId = $this->getActivePlayerId($gameId);
        $hand = $this->player->calculateHandStatus($userId);

        // Check if it's the player's turn
        if ($userId !== $playerId) {
            Response::error("It's not your turn.");
            return false;
        }

        // Cannot hit if total is 21 or more
        if ($hand["total"] >= 21) {
            return false; // Player can't hit if their total is 21 or higher
        }

        return true; // Player can hit
    }

    // Check if a player can stand
    public function canStand($userId, $gameId)
    {
        $playerId = $this->getActivePlayerId($gameId);

        // Check if it's the player's turn
        if ($userId !== $playerId) {
            Response::error("It's not your turn.");
            return false;
        }

        return true; // Player can stand
    }

    // Check if a player can double their bet
    public function canDouble($userId, $gameId)
    {
        $playerId = $this->getActivePlayerId($gameId);
        $hand = $this->player->calculateHandStatus($userId);
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->getCurrentBet($userId, $gameId);

        // Check if it's the player's turn
        if ($userId !== $playerId) {
            Response::error("It's not your turn.");
            return false;
        }

        // Player must have a hand total of 9, 10, or 11 to double
        // Player must have enough chips to double the bet
        if (in_array($hand["total"], [9, 10, 11]) && $playerChips >= $currentBet * 2) {
            return true;
        }

        return false; // Player cannot double
    }

    // Check if a player can split their hand
    public function canSplit($userId, $gameId)
    {
        $playerId = $this->getActivePlayerId($gameId);
        $hand = $this->player->getPlayerHand($userId); // Get the player's hand cards
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->getCurrentBet($userId, $gameId);

        // Check if it's the player's turn
        if ($userId !== $playerId) {
            Response::error("It's not your turn.");
            return false;
        }

        // Conditions for splitting:
        // 1. The hand must contain exactly two cards
        // 2. The two cards must have the same rank (e.g., two 8s)
        // 3. Player must have enough chips to place an additional bet
        if (count($hand) === 2 && $hand[0]['rank'] === $hand[1]['rank'] && $playerChips >= $currentBet) {
            return true; // Player can split
        }

        return false; // Player cannot split
    }

    // Check if a player can surrender
    public function canSurrender($userId, $gameId)
    {
        $playerId = $this->getActivePlayerId($gameId);
        $hand = $this->player->calculateHandStatus($userId);
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->getCurrentBet($userId, $gameId);

        // Check if it's the player's turn
        if ($userId !== $playerId) {
            Response::error("It's not your turn.");
            return false;
        }

        // Player can surrender if their hand total is 15, 16, or 17
        // Player must have enough chips to place a surrender bet
        if (in_array($hand["total"], [15, 16, 17]) && $playerChips >= $currentBet / 2) {
            return true;
        }

        return false; // Player cannot surrender
    }

    // Check if a player can take insurance
    public function canTakeInsurance($userId, $gameId)
    {
        $playerId = $this->getActivePlayerId($gameId);
        $hand = $this->player->calculateHandStatus($userId);
        $playerChips = $this->getPlayerChips($userId);
        $currentBet = $this->getCurrentBet($userId, $gameId);

        // Check if it's the player's turn
        if ($userId !== $playerId) {
            Response::error("It's not your turn.");
            return false;
        }

        // Player can take insurance only if the dealer's upcard is an Ace
        // Player must have enough chips to place an insurance bet
        if ($playerChips >= $currentBet / 2) {
            return true;
        }

        return false; // Player cannot take insurance
    }
}
