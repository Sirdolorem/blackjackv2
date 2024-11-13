<?php

require_once __DIR__ . '/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['game_id'], $data['user_id'], $data['action'])) {
    $gameId = $data['game_id'];
    $userId = $data['user_id'];
    $action = strtolower($data['action']);
    $game = new GameActions();

    switch ($action) {
        case 'hit':
            $result = $game->hit($gameId, $userId);
            break;

        case 'stand':
            $result = $game->stand($gameId, $userId);
            break;

        case 'split':
            $result = $game->split($gameId, $userId);
            break;

        case 'double':
            $result = $game->double($gameId, $userId);
            break;

        default:
            $result = ['error' => 'Invalid action'];
            break;
    }

    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Missing required parameters (game_id, user_id, action)']);
}
