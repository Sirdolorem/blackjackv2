<?php

use blackjack\Deck;
use blackjack\DependencyManager;
use blackjack\Player;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$player = DependencyManager::get(Player::class);
$deck = DependencyManager::get(Deck::class);

if (!isset($data['game_id']) || !isset($data['user_id'])) {
    Response::error('Missing game_id or user_id');
    return;
}

$gameId = $data['game_id'];
$userId = $data['user_id'];
$result = $player->joinGame($userId, $gameId);

if (!$result) {
    Response::error('Unable to join the game');
    return;
}

$deck->dealCards($gameId, $userId, 2);
$setActivePlayerResult = null;
if (!$player->isActiveUserSet($userId)) {
    $setActivePlayerResult = $player->setActiveUser($gameId, $userId);
}
if (!$setActivePlayerResult) {
    Response::error('Unable to set active player');
}

Response::success('Successfully joined the game');
