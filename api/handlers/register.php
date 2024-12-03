<?php

use blackjack\DependencyManager;
use blackjack\Response;
use blackjack\User;

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username']) && isset($data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $user = DependencyManager::get(User::class);

    $userId = $user->create($username, $password);

    if ($userId) {
        Response::success('Registration successful', ['user_id' => $userId]);
    } else {
        Response::error('Registration failed');
    }
} else {
    Response::error('Missing required fields (username, password)');
}
