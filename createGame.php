<?php

$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

if (isset($data['user_id'])) {
    $userId = $data['user_id'];


    $gameId = $game->createGame($userId);

    if ($gameId) {
        echo json_encode(['game_id' => $gameId]);
    } else {
        echo json_encode(['error' => 'Unable to create game']);
    }
} else {
    echo json_encode(['error' => 'Missing user_id']);
}
