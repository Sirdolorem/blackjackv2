<?php
namespace blackjack;

use blackjack\Helpers\GameDatabaseHelper;
use blackjack\Helpers\PlayerDatabaseHelper;
use Exception;

class Game extends GameDatabaseHelper
{
    private PlayerDatabaseHelper $playerDbHelper;
    private Player $player;
    private Deck $deck;
    private \mysqli $conn;

    public function __construct()
    {
        parent::__construct();
        $this->player = new Player();
        $this->conn = Database::getInstance()->getConnection();
        $this->playerDbHelper = new PlayerDatabaseHelper();
        $this->deck = new Deck();
    }

    public function createGame(): void
    {
        $this->conn->begin_transaction();

        try {
            $gameId = $this->generateGameId();
            $deck = $this->deck->createDeck();
            $this->initGame($gameId, $deck);
            $playersId = $this->playerDbHelper->assignPlayersToGame($gameId);
            $this->updatePlayerId($gameId, $playersId);
            if (!$playersId) {
                Response::error("Failed to assign players to game");
            }


            $this->conn->commit();
            Response::success("Game created successfully", ['game_id' => $gameId, 'players_id' => $playersId]);
        } catch (Exception $e) {
            $this->conn->rollback();
            Response::error($e->getMessage(), 500);
        }
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

