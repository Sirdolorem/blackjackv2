<?php
namespace blackjack;

class Response
{
/**
* Array of preprogrammed responses.
*/
    private static $predefinedResponses = [
    'game_created' => [
    'message' => 'Game has been successfully created.',
    'code' => 201,
    'action' => 'navigate_to_game'
    ],
    'player_joined' => [
    'message' => 'Player has successfully joined the game.',
    'code' => 200,
    'action' => 'update_ui'
    ],
    'game_not_found' => [
    'message' => 'The requested game does not exist.',
    'code' => 404,
    'action' => 'show_error_message'
    ],
    'invalid_action' => [
    'message' => 'The action you performed is invalid.',
    'code' => 400,
    'action' => 'show_error_message'
    ],
    'server_error' => [
    'message' => 'An unexpected server error occurred.',
    'code' => 500,
    'action' => 'show_error_message'
    ]
    ];

/**
* Send a preprogrammed response based on a key.
*
* @param string $key The key of the preprogrammed response.
* @param mixed|null $data Additional data to include in the response.
* @param string|null $action Override or supplement the action in the response.
*/
    public static function staticResponse($key, $data = null, $action = null)
    {
        if (!array_key_exists($key, self::$predefinedResponses)) {
            self::error('Predefined response not found.', 404, 'show_error_message');
        }

        $responseTemplate = self::$predefinedResponses[$key];
        $message = $responseTemplate['message'];
        $code = $responseTemplate['code'];
        $responseAction = $action ?? $responseTemplate['action'];

        if ($code >= 400) {
            self::error($message, $code, $responseAction);
        } else {
            self::success($message, $data, $responseAction);
        }
    }

/**
* Send a success response.
*
* @param mixed $message Success message (string, array, or object).
* @param mixed|null $data Additional data to include in the response.
* @param string|null $action Action keyword for the frontend.
*/
    public static function success($message, $data = null, $action = null)
    {
        $response = [
        'status' => 'success',
        'message' => is_array($message) || is_object($message) ? $message : ['text' => $message],
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($action !== null) {
            $response['action'] = $action;
        }


        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

/**
* Send an error response and log the error.
*
* @param mixed $message Error message (string, array, or object).
* @param int $code HTTP status code for the error.
* @param string|null $action Action keyword for the frontend.
*/
    public static function error($message, $code = 400, $action = null)
    {
        http_response_code($code);

        $response = [
        'status' => 'error',
        'message' => is_array($message) || is_object($message) ? $message : ['text' => $message],
        ];

        if ($action !== null) {
            $response['action'] = $action;
        }


        self::logError($message, $code);

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

/**
* Log error details for debugging purposes.
*
* @param mixed $message The error message to log.
* @param int $code HTTP status code associated with the error.
*/
    private static function logError($message, $code)
    {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Error $code: ";
        $logMessage .= is_array($message) || is_object($message)
        ? json_encode($message)
        : $message;
        $logMessage .= PHP_EOL;

        file_put_contents(__DIR__ . '/../logs/error.log', $logMessage, FILE_APPEND);
    }

/**
* Output debug information for the given variables.
*
* @param mixed $data The data to be debugged.
*/
    public static function debug($data)
    {
    // Output the debug information
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit();
    }
}
