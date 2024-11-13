<?php

require_once __DIR__ . '/../autoload.php';

class GameActions
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();    }
    public function hit($game_id, $user_id)
    {
        $deck = $this->getDeck($game_id);
        if (empty($deck)) {
            return ['error' => 'No more cards left in the deck'];
        }
        $card = array_shift($deck);
        $this->updateDeck($game_id, $deck);
        $this->insertCard($game_id, $user_id, $card, 'Hit');

        return ['card' => $card];
    }
    public function stand($game_id, $user_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO actions (action, game_id, user_id) VALUES ('hit', ?, ?);");
        $stmt->bind_param("ss", $game_id, $user_id);
        $stmt->execute();

        return ['status' => 'Player has chosen to stand'];
    }
    public function split($game_id, $user_id)
    {
        $cards = $this->getUserCards($game_id, $user_id);
        if (count($cards) !== 2 || substr($cards[0]['card'], 0, -4) !== substr($cards[1]['card'], 0, -4)) {
            return ['error' => 'Split is not allowed'];
        }
        foreach ($cards as $card) {
            $stmt = $this->conn->prepare("UPDATE game_state SET hand = IFNULL(hand, 1) WHERE game_id = ? AND user_id = ? AND card = ?");
            $stmt->bind_param("sss", $game_id, $user_id, $card['card']);
            $stmt->execute();
        }

        return ['status' => 'Player has split their hand'];
    }
    public function double($game_id, $user_id)
    {
        $deck = $this->getDeck($game_id);
        if (empty($deck)) {
            return ['error' => 'No more cards left in the deck'];
        }
        $card = array_shift($deck);
        $this->updateDeck($game_id, $deck);
        $this->insertCard($game_id, $user_id, $card, 'double');

        return ['card' => $card, 'status' => 'Bet doubled and one card dealt'];
    }
    private function getDeck($game_id)
    {
        $stmt = $this->conn->prepare("SELECT deck FROM games WHERE game_id = ?");
        $stmt->bind_param("s", $game_id);
        $stmt->execute();
        $stmt->bind_result($deck_json);
        $stmt->fetch();

        return json_decode($deck_json, true);
    }

    private function updateDeck($game_id, $deck)
    {
        $stmt = $this->conn->prepare("UPDATE games SET deck = ? WHERE game_id = ?");
        $deck_json = json_encode($deck);
        $stmt->bind_param("ss", $deck_json, $game_id);
        $stmt->execute();
    }

    private function insertCard($game_id, $user_id, $card, $action)
    {
        $stmt = $this->conn->prepare("INSERT INTO actions (game_id, user_id, card, action) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $game_id, $user_id, $card, $action);
        $stmt->execute();
    }

    private function getUserCards($game_id, $user_id)
    {
        $stmt = $this->conn->prepare("SELECT card FROM actions WHERE game_id = ? AND user_id = ?");
        $stmt->bind_param("ss", $game_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

