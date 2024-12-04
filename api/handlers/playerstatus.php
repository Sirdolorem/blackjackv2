<?php
use blackjack\ActionCheck;
use blackjack\Deck;
use blackjack\Game;
use blackjack\Player;
use blackjack\Bet;
use blackjack\Response;
use blackjack\DependencyManager;

$data = json_decode(file_get_contents('php://input'), true);

$player = DependencyManager::get(Player::class);
$deck = DependencyManager::get(Deck::class);
$bet = DependencyManager::get(Bet::class);
$game = DependencyManager::get(Game::class);
$action = DependencyManager::get(ActionCheck::class);


if (!isset($data['game_id']) || !isset($data['user_id'])) {
    Response::error('Missing game_id or user_id');
    return;
}

$gameId = $data['game_id'];
$userId = $data['user_id'];

$hand = $player->calculateHandTotal($userId, $gameId);

$status = [
    'gamePhase' => $game->checkGameStatus($gameId),
    'canHit' => $action->canHit($userId, $gameId),
    'canStand' => $action->canStand($userId, $gameId),
    'canDouble' => $action->canDouble($userId, $gameId),
    'canSplit' => $action->canSplit($userId, $gameId),
    'canSurrender' => $action->canSurrender($userId, $gameId),
    'canTakeInsurance' => $action->canTakeInsurance($userId, $gameId),
    'handTotal' => $hand["total"],
    'handAbove21' => $hand["total"] >= 21,
    'lastAction' => $player->lastAction($userId, $gameId),
    'isPlayerTurn' => $player->isPlayerTurn($userId, $gameId)
];

Response::success(json_encode($status));
