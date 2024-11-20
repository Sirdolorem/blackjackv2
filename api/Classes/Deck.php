<?php
namespace blackjack;

use blackjack\Helpers\DeckDatabaseHelper;
use blackjack\Helpers\PlayerDatabaseHelper;
use Exception;

class Deck extends DeckDatabaseHelper
{
    private \mysqli $conn;
    private PlayerDatabaseHelper $PlayerDbHelper;


    public function __construct()
    {
        parent::__construct();
        $this->conn = Database::getInstance()->getConnection();
        $this->PlayerDbHelper = new PlayerDatabaseHelper();
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

    private function getCardValue(string $rank): int
    {
        return match ($rank) {
            'Jack', 'Queen', 'King' => 10,
            'Ace' => 11,
            default => (int) $rank,
        };
    }

    public function dealCards(string $gameId, string $playerId, int $numCards)
    {
// Start a transaction to ensure data integrity
        $this->conn->begin_transaction();

        try {
    // Fetch the current deck for the game using the DatabaseHelper
            $deck = $this->fetchDeck($gameId);

    // Check if there are enough cards to deal
            if (count($deck) < $numCards) {
                throw new Exception("Not enough cards in the deck");
            }

    // Deal the cards
            $dealtCards = array_splice($deck, 0, $numCards);

    // Update the deck in the database using the DatabaseHelper
            $this->updateDeck($gameId, $deck);

    // Insert dealt cards into the player's hand using the DatabaseHelper
            $this->PlayerDbHelper->updatePlayerHand($gameId, $playerId, $dealtCards);

    // Commit the transaction
            $this->conn->commit();

            return $dealtCards;
        } catch (Exception $e) {
    // Roll back the transaction on error
            $this->conn->rollback();
            throw $e;
        }
    }

    public function checkBlackjack($userId): bool
    {
        $hand = $this->PlayerDbHelper->getPlayerHand($userId);
        if (count($hand) === 2) {
            $handStatus = $this->PlayerDbHelper->calculateHandStatus($hand);

            if ($handStatus["total"] === 21 && $handStatus["aceCount"] === 1) {
                return true;
            }
        }

        return false;
    }

//    public function checkBust($userId): bool
//    {
//        $status = $this->PlayerDbHelper->calculateHandStatus($userId);
//        return $status['isBust'];
//    }
}
