<?php
namespace blackjack;

use blackjack\Helpers\GameDatabaseHelper;

class Game extends GameDatabaseHelper
{


    private Deck $deck;

    public function __construct(\mysqli $conn, Deck $deck)
    {
        parent::__construct($conn);
        $this->deck = $deck;
    }

    public function checkIfGameExists(string $gameId): bool
    {
        return $this->getGame($gameId);
    }

    public function createGame(): void
    {
            $gameId = $this->generateGameId();
            $deck = json_encode($this->deck->createDeck());
            $this->initGame($gameId, $deck);

            $this->conn->commit();
            Response::success("Game created successfully", ['game_id' => $gameId]);
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
}
