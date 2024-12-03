<?php

use blackjack\DependencyManager;
use blackjack\Game;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$game = DependencyManager::get(Game::class);

if (isset($data['game_id']) && isset($data['user_id'])) {
    $gameId = $data['game_id'];
    $userId = $data['user_id'];

    $game->leaveGame($userId, $gameId);
} else {
    Response::error('Missing required parameters: game_id or user_id');
}
