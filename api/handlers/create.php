<?php

use blackjack\DependencyManager;
use blackjack\Game;
use blackjack\Player;
use blackjack\Response;

$data = json_decode(file_get_contents('php://input'), true);
$game = DependencyManager::get(Game::class);

$gameId = $game->createGame();

Response::success("Game created successfully", ['game_id' => $gameId]);
