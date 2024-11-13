<?php
$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

if (isset($data['game_id']) && isset($data['user_id'])) {
    $gameId = $data['game_id'];
    $userId = $data['user_id'];


    $result = $game->joinGame($gameId, $userId);

    if ($result) {
        echo json_encode(['message' => 'Successfully joined the game']);
    } else {
        echo json_encode(['error' => 'Unable to join the game']);
    }
} else {
    echo json_encode(['error' => 'Missing game_id or user_id']);
}
