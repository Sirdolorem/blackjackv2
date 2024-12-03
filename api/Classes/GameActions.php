<?php
namespace blackjack;

use blackjack\Helpers\GameActionsDatabaseHelper;

class GameActions extends GameActionsDatabaseHelper
{
    private Deck $deck;
    private Player $player;

    private ActionCheck $actionCheck;

    /**
     * GameActions constructor.
     * Initializes the deck and player objects.
     *
     * @param Deck $deck The Deck object
     * @param Player $player The Player object
     */
    public function __construct(Deck $deck, Player $player, ActionCheck $actionCheck)
    {
        $this->deck = $deck;
        $this->player = $player;
        $this->actionCheck = $actionCheck;
        parent::__construct();
    }

    /**
     * Handles the action of hitting (drawing a card) for the player.
     * It draws a card from the deck and updates the player's hand.
     *
     * @param string $game_id The game ID
     * @param string $user_id The user ID
     * @return array The card drawn and any related status
     */
    public function hit($game_id, $user_id): array
    {
        if (!$this->actionCheck->canHit($user_id, $game_id)) {
            Response::error("Player can't hit");
        }
        $deck = $this->deck->getDeck($game_id);
        if ($this->deck->checkIfDeckEmpty($deck)) {
            Response::error("Deck is empty");
        }
        $card = array_shift($deck);

        // Update the deck and player hand
        $this->deck->updateDeck($game_id, $deck);
        $this->player->updatePlayerHand($game_id, $user_id, $card);
        $this->logAction($game_id, $user_id, $card, 'Hit');

        return ['card' => $card];
    }

    /**
     * Handles the action of standing (ending the player's turn).
     * It logs the action and returns the status.
     *
     * @param string $game_id The game ID
     * @param string $user_id The user ID
     * @return array Status of the player's action
     */
    public function stand($game_id, $user_id): array
    {
        if (!$this->actionCheck->canStand($user_id, $game_id)) {
            Response::error("Player can't stand");
        }
        $this->logAction($game_id, $user_id, 'stand');
        return ['status' => 'Player has chosen to stand'];
    }

    /**
     * Handles the action of splitting the player's hand into two hands.
     * This feature is not yet fully implemented.
     *
     * @param string $game_id The game ID
     * @param string $user_id The user ID
     * @return array Status or error message related to the split action
     */
    public function split($game_id, $user_id): array
    {
        //TODO complete split

        if (!$this->actionCheck->canSplit($user_id, $game_id)) {
            Response::error("Player can't split");
        }
        Response::debug("split todo");

        $cards = $this->player->getPlayerHand($user_id);
        if (count($cards) !== 2 || substr($cards[0]['card'], 0, -4) !== substr($cards[1]['card'], 0, -4)) {
            return ['error' => 'Split is not allowed'];
        }

        // Update player's hands after split
        foreach ($cards as $card) {
            $this->player->updatePlayerHand($game_id, $user_id, $card['card']);
        }

        return ['status' => 'Player has split their hand'];
    }

    /**
     * Handles the action of doubling the player's bet and drawing one additional card.
     * It updates the player's hand and logs the action.
     *
     * @param string $game_id The game ID
     * @param string $user_id The user ID
     * @return array The card drawn and the status of the bet doubling
     */
    public function double($game_id, $user_id)
    {
        if (!$this->actionCheck->canDouble($user_id, $game_id)) {
            Response::error("Player can't double");
        }
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
