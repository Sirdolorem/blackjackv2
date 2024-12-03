<?php
namespace blackjack;

require_once "autoload.php";


//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//memprof_enable();

Middleware::validate();
DependencyManager::init();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];



$parts = explode("/", $requestUri);
$handler = end($parts);

$handlerFilePath = "handlers/$handler.php";

if ($method == 'POST') {
    if (file_exists($handlerFilePath)) {
        require $handlerFilePath;
    } else {
        Response::error((['error' => 'Invalid API endpoint', 'route' => $requestUri]));
    }
} else {
    Response::error(['error' => 'Invalid request method. Only POST is allowed']);
}
