<?php
namespace blackjack;

use blackjack\Helpers\UserDatabaseHelper;
use blackjack\JWTAuth;
use blackjack\Response;

class User extends UserDatabaseHelper
{
    private JWTAuth $jwt;


    public function __construct()
    {
        parent::__construct();
        $this->jwt = new JWTAuth();
    }

    private function generateUserId(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $userId = 'U_'; // Start with 'U_'

    // Generate the next 4 characters
        for ($i = 0; $i < 4; $i++) {
            $userId .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $userId;
    }

/**
* Create a new user if username is unique
*
* @param string $username
* @param string $password
*/
    public function create(string $username, string $password)
    {
        if ($this->getUserByUsername($username)) {
            Response::error(['error' => 'Username already exists']);
            return;
        }

        $hashedPassword = $this->hashPassword($password);
        $userId = $this->generateUserId();

    // Call the method to create the user
        if ($this->createUser($userId, $username, $hashedPassword)) {
            $user = $this->getUserByUsername($username);
            if ($user) {
                Response::success(['user_id' => $user["user_id"]]);
            } else {
                Response::error(['error' => 'Failed to retrieve the user after creation']);
            }
        } else {
            Response::error(['error' => 'Error creating user']);
        }
    }

/**
* Login a user and generate a JWTAuth token
*
* @param string $username
* @param string $password
*/
    public function login(string $username, string $password)
    {
        $user = $this->getUserByUsername($username);

        if (!$user) {
            Response::error(['error' => 'User not found']);
            return;
        }

        if (!$this->verifyPassword($password, $user['password'])) {
            Response::error(['error' => 'Invalid password']);
            return;
        }

        $token = $this->generateUserToken($user['user_id'], $user['username']);
        Response::success(['token' => $token]);
    }

/**
* Hashes a password for storage
*
* @param string $password
* @return string The hashed password
*/
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

/**
* Verify a hashed password
*
* @param string $password
* @param string $hashedPassword
* @return bool True if password matches, false otherwise
*/
    private function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

/**
* Generate JWT token for the user
*
* @param string $userId
* @param string $username
* @return string The JWT token
*/
    private function generateUserToken(string $userId, string $username): string
    {
        $payload = [
        'iat' => time(),
        'exp' => time() + 3600,
        'user_id' => $userId,
        'username' => $username,
        ];

        return $this->jwt->generateToken($payload);
    }
}
