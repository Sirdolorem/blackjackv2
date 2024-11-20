<?php
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

if (isset($data['game_id'])) {
    $gameId = $data['game_id'];

    $result = $game->dealCards($gameId);

    if ($result) {
        // Use the success method from the Response class
        Response::success('Cards dealt successfully');
    } else {
        // Use the error method from the Response class
        Response::error('Unable to deal cards');
    }
} else {
    // Use the error method from the Response class for missing game_id
    Response::error('Missing game_id');
}
