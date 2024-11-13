<?php

require_once __DIR__ . '/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username']) && isset($data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $user = new User();

    $response = $user->login($username, $password);

    if (isset($response['token'])) {
        echo json_encode(['token' => $response['token']]);
    } else {
        echo json_encode(['error' => $response['error']]);
    }
} else {
    echo json_encode(['error' => 'Missing required fields (username, password)']);
}

