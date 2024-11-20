<?php
namespace blackjack;

class Middleware
{
// List of routes that should skip the token validation
    protected static $routesWithoutAuth = [
    '/login',
    '/register'
    ];

    public static function validateToken()
    {
    // Get the current request URI
        $requestUri = $_SERVER['REQUEST_URI'];

    // If the current route is in the list of routes without authentication, skip validation
        if (self::isRouteWithoutAuth($requestUri)) {
            return;  // Skip token validation for this route
        }

    // Otherwise, validate the token
        $jwt = new JWTAuth();
        try {
            $jwt->validateToken();  // This will throw an exception if the token is invalid
        } catch (\Exception $e) {
            Response::error(['error' => 'Unauthorized', 'message' => $e->getMessage()], 401);
            exit();  // Stop further execution
        }
    }

// Check if the current route is in the list of routes that should skip authentication
    private static function isRouteWithoutAuth($route)
    {
        foreach (self::$routesWithoutAuth as $noAuthRoute) {
        // Match route exactly or with wildcard (adjust as necessary)
            if (strpos($route, $noAuthRoute) !== false) {
                return true;  // Skip token validation for this route
            }
        }
        return false;  // Token validation required for this route
    }
}
