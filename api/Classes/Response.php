<?php
namespace blackjack;

class Response
{
/**
* Send a success response.
*
* @param mixed $message Success message (string, array, or object).
* @param mixed|null $data Additional data to include in the response.
*/
    public static function success($message, $data = null)
    {
        $response = [
        'status' => 'success',
        'message' => is_array($message) || is_object($message) ? $message : ['text' => $message]
        ];

        if ($data !== null) {
            $response['data'] = $data;
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
*/
    public static function error($message, $code = 400)
    {
    // Set HTTP response code
        http_response_code($code);

    // Prepare the response
        $response = [
        'status' => 'error',
        'message' => is_array($message) || is_object($message) ? $message : ['text' => $message]
        ];

    // Log the error message
        self::logError($message, $code);

    // Send the JSON response
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

    // Append the log to an error log file
        file_put_contents(__DIR__ . '/../logs/error.log', $logMessage, FILE_APPEND);
    }

    /**
     * Return detailed debug information (e.g., for development purposes).
     *
     * @param mixed $data Data to be debugged (can be an object, array, or string).
     */
    public static function debug($data)
    {
        // Check if the data is an object or array and format it accordingly
        if (is_array($data) || is_object($data)) {
            $response = [
                'status' => 'debug',
                'data' => $data
            ];
        } else {
            // For non-array or object data, wrap it in a string
            $response = [
                'status' => 'debug',
                'data' => ['text' => $data]
            ];
        }

        // Send a JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
