<?php

use blackjack\Game;
use blackjack\JWTAuth;

$data = json_decode(file_get_contents('php://input'), true);
$game = new Game();

$game->createGame();
