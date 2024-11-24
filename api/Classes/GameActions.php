<?php
namespace blackjack;

use blackjack\Helpers\GameActionsDatabaseHelper;

class GameActions extends GameActionsDatabaseHelper
{
    private Deck $deck;
    private Player $player;

    public function __construct(\Mysqli $conn, Deck $deck, Player $player)
    {
        $this->deck = $deck;
        $this->player = $player;
        parent::__construct($conn);
    }

    public function hit($game_id, $user_id): array
    {
        $deck = $this->deck->getDeck($game_id);
        if ($this->deck->checkIfDeckEmpty($deck)) {
            Response::error("Deck is empty");
        }
        $card = array_shift($deck);

        $this->deck->updateDeck($game_id, $deck);
        $this->player->updatePlayerHand($game_id, $user_id, $card);
        $this->logAction($game_id, $user_id, $card, 'Hit');

        return ['card' => $card];
    }

    public function stand($game_id, $user_id): array
    {
        $this->logAction($game_id, $user_id, 'stand');
        return ['status' => 'Player has chosen to stand'];
    }

    public function split($game_id, $user_id): array
    {
        $cards = $this->player->getPlayerHand($user_id);
        if (count($cards) !== 2 || substr($cards[0]['card'], 0, -4) !== substr($cards[1]['card'], 0, -4)) {
            return ['error' => 'Split is not allowed'];
        }
        foreach ($cards as $card) {
            $this->updateHand($game_id, $user_id, $card['card']);
        }

        return ['status' => 'Player has split their hand'];
    }

    public function double($game_id, $user_id)
    {
        $deck = $this->deck->getDeck($game_id);
        if (empty($deck)) {
            return ['error' => 'No more cards left in the deck'];
        }
        $card = array_shift($deck);
        $this->deck->updateDeck($game_id, $deck);
        $this->player->updatePlayerHand($game_id, $user_id, $card);
        $this->logAction($game_id, $user_id, $card, 'double');

        return ['card' => $card, 'status' => 'Bet doubled and one card dealt'];
    }
}
