<?php
require_once __DIR__ . '/autoload.php'; // Autoloader for classes

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET'); // Allow POST and GET methods

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

switch ($method) {
    case 'POST':
        switch ($requestUri) {
            case '/create-game':
                require '/handlers/create_game.php';
                break;
            case '/join-game':
                require '/handlers/join_game.php';
                break;
            case '/deal-cards':
                require '/handlers/deal_cards.php';
                break;
            case '/player-action':
                require '/handlers/player_action.php';
                break;
            default:
                echo json_encode(['error' => 'Invalid API endpoint']);
                break;
        }
        break;

    case 'GET':
        if ($requestUri == '/game-status') {
            require 'game_status.php';
        } else {
            echo json_encode(['error' => 'Invalid request method']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method. Only POST and GET are allowed']);
}
