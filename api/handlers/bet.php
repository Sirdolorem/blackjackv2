<?php

use blackjack\Bet;
use blackjack\DependencyManager;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['game_id'], $data['user_id'], $data['action'], $data['bet_amount'])) {
    Response::error('Missing required parameters (game_id, user_id, action, bet_amount)');
    exit();
}

$gameId = $data['game_id'];
$userId = $data['user_id'];
$action = strtolower($data['action']);
$betAmount = (int) $data['bet_amount'];
$bet = DependencyManager::get(Bet::class);

$result = match ($action) {
    'add' => $bet->placeBet($userId, $gameId, $betAmount),
    'update' => $bet->modifyBet($userId, $gameId, $betAmount),
    'remove' => $bet->deleteBet($userId, $gameId),
    'double' => $bet->doubleBet($userId, $gameId),
    default => ['error' => 'Invalid action'],
};


if (isset($result['error'])) {
    Response::error($result['error']);
} else {
    Response::success('Bet action performed successfully', $result);
}
