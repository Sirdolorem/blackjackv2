<?php
namespace blackjack;

require_once "autoload.php";

use blackjack\JWTAuth;
use blackjack\Response;

Middleware::validateToken();


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];



$parts = explode("/", $requestUri);
$handler = end($parts);

$handlerFilePath = "handlers/$handler.php";

if ($method == 'POST') {
        // Check if the handler file exists
    if (file_exists($handlerFilePath)) {
        require $handlerFilePath;
    } else {
        Response::error((['error' => 'Invalid API endpoint', 'route' => $requestUri]));
    }
} else {
    Response::error(['error' => 'Invalid request method. Only POST is allowed']);
}
