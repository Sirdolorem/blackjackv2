<?php

require_once __DIR__ . '/../autoload.php';

class User
{
    private $conn;
    private $jwt;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->jwt = new JWTAuth();    }

    /**
     * Create a new user
     *
     * @param string $username
     * @param string $password
     * @return int|false The new user ID or false on failure
     */
    public function create(string $username, string $password): string|bool
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);
        $stmt->execute();
        $user = $this->getUser($username);
        return $user["user_id"] ?: false;
    }

    /**
     * Login a user and generate a JWTAuth
     *
     * @param string $username
     * @param string $password
     * @return array The JWTAuth or error message
     */
    public function login(string $username, string $password): array
    {
        $stmt = $this->conn->prepare("SELECT user_id, password, username FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($userId, $hashedPassword, $usernameResult);
        if ($stmt->fetch()) {
            if (password_verify($password, $hashedPassword)) {
                $payload = [
                    'iat' => time(),
                    'exp' => time() + 3600,
                    'user_id' => $userId,
                    'username' => $usernameResult,
                ];
                $token = $this->jwt->generateToken($payload);
                return ['token' => $token];
            } else {
                return ['error' => 'Invalid password'];
            }
        } else {
            return ['error' => 'User not found'];
        }

    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return array|null The user data or null if not found
     */
    public function getUser(string $username): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Verify user password
     *
     * @param string $username
     * @param string $password
     * @return bool True if password is correct, false otherwise
     */
    public function verifyPassword(string $username, string $password): bool
    {
        $user = $this->getUser($username);
        return $user && password_verify($password, $user['password']);
    }
}
