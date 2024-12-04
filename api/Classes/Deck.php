<?php
namespace blackjack;

use blackjack\Helpers\DeckDatabaseHelper;

class Deck extends DeckDatabaseHelper
{
    /**
     * Deck constructor.
     * Initializes the deck database helper.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the deck of cards for a specific game.
     *
     * @param string $gameId The ID of the game
     * @return array The deck of cards
     */
    public function getDeck(string $gameId): array
    {
        return $this->fetchDeckFromDatabase($gameId);
    }

    /**
     * Update the deck in the database for a specific game.
     *
     * @param string $gameId The ID of the game
     * @param array $deck The new deck to be saved
     */
    public function updateDeck(string $gameId, array $deck): void
    {
        $this->updateDeckInDatabase($gameId, $deck);
    }

    /**
     * Create a new deck of cards.
     *
     * @return array The shuffled deck of cards
     */
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

    /**
     * Check if the deck is empty for a given game.
     *
     * @param string $gameId The game ID for which the deck should be checked
     * @return bool True if the deck is empty, false otherwise
     */
    public function checkIfDeckEmpty(string $gameId): bool
    {
        return empty($this->fetchDeckFromDatabase($gameId));
    }


    /**
     * Get the value of a card based on its rank.
     *
     * @param string $rank The rank of the card (e.g., 'Ace', 'King')
     * @return int The value of the card
     */
    private function getCardValue(string $rank): int
    {
        return match ($rank) {
            'Jack', 'Queen', 'King' => 10,
            'Ace' => 11,
            default => (int) $rank,
        };
    }

    /**
     * Deals cards to a player in a game.
     *
     * @param string $gameId The game ID.
     * @param string $playerId The player ID.
     * @param int $numCards The number of cards to deal.
     * @return array Returns dealt cards from deck
     */
    public function dealCards(string $gameId, string $playerId, int $numCards): array
    {
        $deck = $this->getDeck($gameId);

        if (count($deck) < $numCards) {
            Response::error("Not enough cards in the deck");
        }

        $dealtCards = array_splice($deck, 0, $numCards);

        $this->updateDeck($gameId, $deck);

        return $dealtCards;
    }
}
