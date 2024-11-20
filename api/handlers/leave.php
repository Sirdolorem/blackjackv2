<?php

use blackjack\Game;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

if (isset($data['game_id']) && isset($data['user_id'])) {
    $gameId = $data['game_id'];
    $userId = $data['user_id'];

    $game->leaveGame($userId, $gameId);
} else {
    // Error message when the necessary parameters are missing
    Response::error('Missing required parameters: game_id or user_id');
}
