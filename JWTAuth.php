<?php
require_once __DIR__ . '/../vendor/autoload.php';


use Dotenv\Dotenv;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWTAuth
{
    private $secretKey;
    private $algorithm = 'HS256';

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->secretKey = $_ENV['JWT_SECRET_KEY'];
    }

    /**
     * Generates a JWTAuth for the given payload.
     * @param array $payload - The payload data to encode in the token.
     * @return string - Encoded JWTAuth.
     */
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expiration = $issuedAt + 3600;
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $expiration;

        return FirebaseJWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Decodes and validates a JWTAuth.
     * @param string $token - The JWTAuth to validate.
     * @return object|bool - The decoded token payload if valid, false otherwise.
     */
    public function validateToken(string $token)
    {
        try {
            return FirebaseJWT::decode($token, new Key($this->secretKey, $this->algorithm));
        } catch (\Exception $e) {

            return false;
        }
    }
}
