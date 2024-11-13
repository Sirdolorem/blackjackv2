<?php
$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

if (isset($data['game_id']) && isset($data['user_id']) && isset($data['action'])) {
    $gameId = $data['game_id'];
    $userId = $data['user_id'];
    $action = $data['action'];


    $result = $game->playerAction($gameId, $userId, $action);

    if ($result) {
        echo json_encode(['message' => 'Action processed successfully']);
    } else {
        echo json_encode(['error' => 'Unable to process action']);
    }
} else {
    echo json_encode(['error' => 'Missing game_id, user_id, or action']);
}
