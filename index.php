<?php

require_once __DIR__ . '/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($method == 'POST') {
    switch ($requestUri) {
        case '/register':
            require 'handlers/register.php';
            break;

        case '/login':
            require 'handlers/login.php';
            break;


        default:
            echo json_encode(['error' => 'Invalid API endpoint']);
            break;
    }
} else {
    echo json_encode(['error' => 'Invalid request method. Only POST is allowed']);
}
