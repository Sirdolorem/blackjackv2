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

$gameStatus = $game->checkGameStatus($gameId);
$playerTurn = $action->isPlayerTurn($userId, $gameId);
$currentBet = $bet->getCurrentBet($userId, $gameId);
$deckEmpty = $deck->checkIfDeckEmpty($gameId);
$handTotal = $player->calculateHandStatus($userId);
$lastAction = $action->lastPlayerAction($userId);


if (!$gameStatus) {
    Response::error('Game is not active');
    return;
}

$status = [
    'gameStatus' => $gameStatus,
    'playerTurn' => $playerTurn,
    'currentBet' => $currentBet,
    'deckEmpty' => $deckEmpty,
    'handTotal' => $handTotal,
    'handAbove21' => $handTotal >= 21,
    'lastAction' => $lastAction,
];

if ($playerTurn) {
    $result = $player->joinGame($userId, $gameId);

    if (!$result) {
        Response::error('Unable to join the game');
        return;
    }

    // Deal cards to the player if joining for the first time
    $deck->dealCards($gameId, $userId, 2);

    // Set the player as the active player if necessary
    if (!$player->isActiveUserSet($userId)) {
        $setActivePlayerResult = $player->setActiveUser($gameId, $userId);
        if (!$setActivePlayerResult) {
            Response::error('Unable to set active player');
            return;
        }
    }

    Response::success('Game status retrieved successfully', $status);
} else {
    Response::error('It is not your turn to play');
}
