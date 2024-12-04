<?php

use blackjack\DependencyManager;
use blackjack\GameActions;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['game_id'], $data['user_id'], $data['action'])) {
    Response::error('Missing required parameters (game_id, user_id, action)');
    exit();
}

$gameId = $data['game_id'];
$userId = $data['user_id'];
$action = strtolower($data['action']);
$gameActions = DependencyManager::get(GameActions::class);

$result = match ($action) {
    'hit' => $gameActions->hit($gameId, $userId),
    'stand' => $gameActions->stand($gameId, $userId),
    'split' => $gameActions->split($gameId, $userId),
    'double' => $gameActions->double($gameId, $userId),
    'surrender' => $gameActions->surrender($gameId, $userId),
    default => ['error' => 'Invalid action'],
};


if (isset($result['error'])) {
    Response::error($result['error']);
} else {
    Response::success('Action performed successfully', $result);
}
