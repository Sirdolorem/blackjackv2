<?php
namespace blackjack;

class Middleware extends JWTAuth
{
    protected static $routesWithoutAuth = [
        '/login',
        '/register'
    ];

    /**
     * Validates the token for the current request.
     * Skips validation for routes that do not require authentication.
     *
     * @throws \Exception If the token is invalid.
     */
    public static function validate(): void
    {

        $requestUri = $_SERVER['REQUEST_URI'];

        if (self::isRouteWithoutAuth($requestUri)) {
            return;
        }


        $jwt = new JWTAuth();
        try {
            $jwt->validateToken(Middleware::extractTokenFromHeader());
        } catch (\Exception $e) {
            Response::error(['error' => 'Unauthorized', 'message' => $e->getMessage()], 401);
            exit();
        }
    }

    /**
     * Checks if the current route is in the list of routes that should skip authentication.
     *
     * @param string $route The current route being accessed.
     * @return bool Returns true if the route does not require authentication, false otherwise.
     */
    private static function isRouteWithoutAuth($route)
    {
        foreach (self::$routesWithoutAuth as $noAuthRoute) {
            if (str_contains($route, $noAuthRoute)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extracts the Bearer token from the request headers.
     *
     * This function checks the Authorization header and extracts the token from a Bearer scheme.
     * If the token is not found, it returns an empty string.
     *
     * @return string The JWT token extracted from the Authorization header.
     */

    private static function extractTokenFromHeader(): string
    {
        $headers = getallheaders();

        $token = $headers['Authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
