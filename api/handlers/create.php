<?php

use blackjack\DependencyManager;
use blackjack\Game;

$data = json_decode(file_get_contents('php://input'), true);
$game = DependencyManager::get(Game::class);

$game->createGame();
