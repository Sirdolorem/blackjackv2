<?php
use blackjack\User;

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username'], $data['password'])) {
    $username = $data['username'];
    $password = $data['password'];

    $user = new User();

    // Store the login result in a different variable
    $user->login($username, $password);
}
