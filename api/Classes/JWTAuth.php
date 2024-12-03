<?php

namespace blackjack;

use blackjack\Env;
use Exception;

class JWTAuth
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private const ALGORITHMS = [
        'HS256' => 'sha256',
    ];

    public function __construct()
    {
        $this->secretKey = Env::get("SECRET_KEY");
    }

    /**
     * Generates a JWT token.
     *
     * @param array $payload The payload to encode.
     * @return string The encoded JWT token.
     * @throws Exception If token generation fails.
     */
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expiration = $issuedAt + 3600;
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $expiration;

        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm,
        ];


        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));


        $signature = $this->createSignature($base64UrlHeader, $base64UrlPayload);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $signature;
    }

    /**
     * Validates and decodes a JWT token.
     *
     * @param string $token The JWT token.
     * @return object|null The decoded payload if valid, null otherwise.
     * @throws Exception If the token is invalid or expired.
     */
    protected function validateToken(string $token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($base64UrlHeader, $base64UrlPayload, $signature) = $parts;


        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);
        if (!$payload) {
            throw new Exception('Invalid token payload');
        }


        if (time() >= $payload['exp']) {
            throw new Exception('Token has expired');
        }


        $expectedSignature = $this->createSignature($base64UrlHeader, $base64UrlPayload);
        if ($signature !== $expectedSignature) {
            throw new Exception('Invalid token signature');
        }

        return (object)$payload;
    }

    /**
     * Creates the signature for the JWT.
     *
     * @param string $base64UrlHeader The base64 encoded header.
     * @param string $base64UrlPayload The base64 encoded payload.
     * @return string The signature.
     * @throws Exception If signature creation fails.
     */
    private function createSignature(string $base64UrlHeader, string $base64UrlPayload): string
    {
        $data = $base64UrlHeader . '.' . $base64UrlPayload;
        return $this->base64UrlEncode(hash_hmac(self::ALGORITHMS[$this->algorithm], $data, $this->secretKey, true));
    }

    /**
     * Base64 URL-encodes the given data.
     *
     * @param string $data The data to encode.
     * @return string The base64 URL-safe encoded string.
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-decodes the given data.
     *
     * @param string $data The data to decode.
     * @return string The decoded string.
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
