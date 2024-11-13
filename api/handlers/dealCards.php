<?php

$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

if (isset($data['game_id'])) {
    $gameId = $data['game_id'];

    $result = $game->dealCards($gameId);

    if ($result) {
        echo json_encode(['message' => 'Cards dealt successfully']);
    } else {
        echo json_encode(['error' => 'Unable to deal cards']);
    }
} else {
    echo json_encode(['error' => 'Missing game_id']);
}
