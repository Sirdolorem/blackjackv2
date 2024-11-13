<?php

require_once __DIR__ . '/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username']) && isset($data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $user = new User();

    $userId = $user->create($username, $password);
    var_dump($userId);
    if ($userId) {
        echo json_encode(['message' => 'Registration successful', 'user_id' => $userId]);
    } else {
        echo json_encode(['error' => 'Registration failed']);
    }
} else {
    echo json_encode(['error' => 'Missing required fields (username, password)']);
}
