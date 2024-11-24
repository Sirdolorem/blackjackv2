<?php
namespace blackjack;

use blackjack\Helpers\DeckDatabaseHelper;


class Deck extends DeckDatabaseHelper
{
    private Player  $player;

    public function __construct(\Mysqli $conn, Player $player)
    {
        parent::__construct($conn);
        $this->player = $player;
    }


    public function getDeck(string $gameId): array
    {
        return $this->fetchDeckFromDatabase($gameId);
    }

    public function updateDeck(string $gameId, array $deck): void
    {
        $this->updateDeckInDatabase($gameId, $deck);
    }


    public function createDeck(): array
    {
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'Jack', 'Queen', 'King', 'Ace'];

        $deck = [];
        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $deck[] = [
                'rank' => $rank,
                'suit' => $suit,
                'value' => $this->getCardValue($rank),
                ];
            }
        }

        shuffle($deck);
        return $deck;
    }

    public function checkIfDeckEmpty($deck): bool
    {
        if (empty($deck)) {
            return true;
        } else {
            return false;
        }
    }

    private function getCardValue(string $rank): int
    {
        return match ($rank) {
            'Jack', 'Queen', 'King' => 10,
            'Ace' => 11,
            default => (int) $rank,
        };
    }

    public function dealCards(string $gameId, string $playerId, int $numCards): void
    {
        $deck = $this->getDeck($gameId);

        if (count($deck) < $numCards) {
            Response::error("Not enough cards in the deck");
        }

        $dealtCards = array_splice($deck, 0, $numCards);

        $this->updateDeck($gameId, $deck);

        $this->player->updatePlayerHand($playerId, $dealtCards);
    }

    public function checkBlackjack($userId): bool
    {
            $handStatus = $this->player->calculateHandStatus($userId);

        if ($handStatus["total"] === 21 && $handStatus["aceCount"] === 1) {
            return true;
        }

        return false;
    }
}
