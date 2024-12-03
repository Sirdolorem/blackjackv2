<?php

use blackjack\DependencyManager;
use blackjack\User;

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username'], $data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $user = DependencyManager::get(User::class);

    $user->login($username, $password);
}
