<?php

require_once __DIR__ . '/../autoload.php';;

class Game
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    private function generateGameId(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $gameId = '';
        for ($i = 0; $i < 6; $i++) {
            $gameId .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $gameId;
    }

    private function createDeck(): array
    {
        $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
        $ranks = [
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            'Jack' => 10,
            'Queen' => 10,
            'King' => 10,
            'Ace' => 11
        ];

        $deck = []; 
        foreach ($suits as $suit) {
            foreach ($ranks as $rank => $value) {
                $deck[] = [
                    'rank' => $rank,
                    'suit' => $suit,
                    'value' => $value
                ];
            }
        }

        shuffle($deck);
        return $deck;    
    
    }


    private function getPlayerHand($userId): array
    {
        $stmt = $this->conn->prepare("SELECT hand FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->bind_result($hand);
        $stmt->fetch();
        $stmt->close();
        return json_decode($hand, true);
    }


    private function calculateHandStatus($userId): array
    {
        $hand = $this->getPlayerHand($userId);

        $totalValue = 0;
        $aceCount = 0;
        foreach ($hand as $card) {
            $totalValue += $card['value'];
            if ($card['rank'] == 'Ace') {
                $aceCount++;
            }
        }
        while ($totalValue > 21 && $aceCount > 0) {
            $totalValue -= 10;
            $aceCount--;
        }

        return [
            'hand' => $hand,
            'total' => $totalValue,
            'aceCount' => $aceCount,
            'isBlackjack' => $totalValue === 21,
            'isBust' => $totalValue > 21
        ];
    }


    public function checkBlackjack($userId): bool
    {
        $hand = $this->getPlayerHand($userId);
        if (count($hand) === 2) {
            $handStatus = $this->calculateHandStatus($hand);

            if ($handStatus["total"] === 21 && $handStatus["aceCount"] === 1) {
                return true;
            }
        }

        return false;
    }


    public function checkBust($userId): bool
    {
        $status = $this->calculateHandStatus($userId);
        return $status['isBust'];
    }


    public function joinGame(string $userId, string $gameId): bool
    {
        $playersId = $this->getGamePlayersId($gameId);

        if (!$playersId) {
            return false;
        }
        $availableSlot = $this->getAvailablePlayerSlot($playersId);

        if (!$availableSlot) {
            return false;
        }
        $success = $this->addPlayerToSlot($playersId, $availableSlot, $userId);

        return $success;
    }


    private function getGamePlayersId(string $gameId)
    {
        $stmt = $this->conn->prepare("SELECT players_id FROM games WHERE game_id = ?");
        $stmt->bind_param("s", $gameId);
        $stmt->execute();
        $stmt->bind_result($playersId);
        $stmt->fetch();

        return $playersId;
    }


    private function getAvailablePlayerSlot(int $playersId): ?string
    {
        $stmt = $this->conn->prepare("SELECT player_1, player_2, player_3, player_4 FROM players WHERE players_id = ?");
        $stmt->bind_param("i", $playersId);
        $stmt->execute();
        $stmt->bind_result($player1, $player2, $player3, $player4);
        $stmt->fetch();
        if (!$player1) return 'player_1';
        if (!$player2) return 'player_2';
        if (!$player3) return 'player_3';
        if (!$player4) return 'player_4';

        return null;
    }

    
    private function addPlayerToSlot(int $playersId, string $slot, string $userId): bool
    {
        $stmt = $this->conn->prepare("UPDATE players SET $slot = ? WHERE players_id = ?");
        $stmt->bind_param("si", $userId, $playersId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }



    public function createGame()
    {
        $stmt = $this->conn->prepare("INSERT INTO games (game_id, deck) VALUES (?, ?)");
        $game_id = $this->generateGameId();
        $json_deck = json_encode($this->createDeck());
        $stmt->bind_param("ss", $game_id, $json_deck);
        $stmt->execute();
        return $stmt->insert_id;
    }


}
