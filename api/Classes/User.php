<?php
namespace blackjack;

use blackjack\Helpers\UserDatabaseHelper;

class User extends UserDatabaseHelper
{
    private JWTAuth $jwt;


    public function __construct(JwtAuth $jwt)
    {
        parent::__construct();
        $this->jwt = $jwt;
    }


/**
* Create a new user if username is unique
*
* @param string $username
* @param string $password
*/
    public function create(string $username, string $password): void
    {
        if ($this->getUserByUsername($username)) {
            Response::error(['error' => 'Username already exists']);
            return;
        }

        $hashedPassword = $this->hashPassword($password);
        $userId = $this->generateUserId();

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
    public function login(string $username, string $password): void
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
     * Fetch the current chips for a user.
     *
     * This method retrieves the current chip balance for the specified user from the database.
     *
     * @param string $userId The user ID
     * @return int The current chip balance for the user
     */
    public function getUserChips(string $userId): int
    {
        return $this->fetchUserChips($userId);
    }

    /**
     * Update the user's chip balance.
     *
     * This method updates the chip balance for the specified user. It is used when chips are added or subtracted,
     * such as after a game round or when a player wins or loses.
     *
     * @param string $userId The user ID
     * @param int $newChipBalance The new chip balance to set for the user
     * @return bool True if the balance was updated successfully, false otherwise
     */
    public function updateUserChips(string $userId, int $newChipBalance): bool
    {
        return $this->updateUserChips($userId, $newChipBalance);
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
        'user_id' => $userId,
        'username' => $username,
        ];

        return $this->jwt->generateToken($payload);
    }
    private function generateUserId(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $userId = 'U_';

        for ($i = 0; $i < 4; $i++) {
            $userId .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $userId;
    }
}
