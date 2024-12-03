<?php

use blackjack\DependencyManager;
use blackjack\Game;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$game = DependencyManager::get(Game::class);

if (isset($data['game_id'])) {
    $gameId = $data['game_id'];

    $result = $game->dealCards($gameId);

    if ($result) {
        Response::success('Cards dealt successfully');
    } else {
        Response::error('Unable to deal cards');
    }
} else {
    Response::error('Missing game_id');
}
