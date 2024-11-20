<?php

use blackjack\Response;
use blackjack\User;

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username']) && isset($data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $user = new User();

    $userId = $user->create($username, $password);

    if ($userId) {
        // Use the success method from the Response class
        Response::success('Registration successful', ['user_id' => $userId]);
    } else {
        // Use the error method from the Response class
        Response::error('Registration failed');
    }
} else {
    // Use the error method from the Response class
    Response::error('Missing required fields (username, password)');
}
