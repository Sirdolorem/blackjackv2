<?php

use blackjack\DependencyManager;
use blackjack\Game;
use blackjack\Player;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$game = DependencyManager::get(Game::class);
$player = DependencyManager::get(Player::class);

if (isset($data['game_id'], $data['player_id'], $data['cardAmount'])) {
    $gameId = $data['game_id'];
    $playerId = $data['player_id'];
    $cardAmount = $data['cardAmount'];

    $dealtCards = $game->dealCards($gameId, $playerId, $cardAmount);
    $result = $player->updatePlayerHand($playerId, $dealtCards, $gameId);

    if ($result) {
        Response::success('Cards dealt successfully');
    } else {
        Response::error('Unable to deal cards');
    }
} else {
    Response::error('Missing game_id');
}
