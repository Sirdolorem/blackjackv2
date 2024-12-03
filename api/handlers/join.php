<?php

use blackjack\DependencyManager;
use blackjack\Player;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$player = DependencyManager::get(Player::class);

if (isset($data['game_id']) && isset($data['user_id'])) {
    $gameId = $data['game_id'];
    $userId = $data['user_id'];
    $result = $player->joinGame($userId, $gameId);

    if ($result) {
        Response::success('Successfully joined the game');
    } else {
        Response::error('Unable to join the game');
    }
} else {
    Response::error('Missing game_id or user_id');
}
