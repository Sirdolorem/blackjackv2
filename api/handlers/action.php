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
$game = DependencyManager::get(GameActions::class);

$result = match ($action) {
    'hit' => $game->hit($gameId, $userId),
    'stand' => $game->stand($gameId, $userId),
    'split' => $game->split($gameId, $userId),
    'double' => $game->double($gameId, $userId),
    default => ['error' => 'Invalid action'],
};

// Check if result contains an error or success message
if (isset($result['error'])) {
    // Use the error method from the Response class
    Response::error($result['error']);
} else {
    // Use the success method from the Response class
    Response::success('Action performed successfully', $result);
}
