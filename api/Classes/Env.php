<?php

namespace blackjack;

class Env
{
/**
* Load environment variables from the .env file.
*
* @param string $filePath The path to the .env file. Default is '.env'.
* @return bool True if the environment variables were loaded successfully, false otherwise.
*/
    public static function load(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            Response::error("Env file not found");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);


            putenv("$key=$value");
            $_ENV[$key] = $value;
        }

        return true;
    }

/**
* Get the value of an environment variable.
*
* @param string $key The key of the environment variable.
* @param mixed|null $default The default value to return if the environment variable is not set.
* @return mixed The environment variable value or the default value.
*/
    public static function get(string $key, mixed $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

/**
* Set an environment variable.
*
* @param string $key The environment variable key.
* @param string $value The environment variable value.
*/
    public static function set(string $key, string $value): void
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }

/**
* Check if an environment variable is set.
*
* @param string $key The environment variable key.
* @return bool True if the environment variable is set, false otherwise.
*/
    public static function has(string $key): bool
    {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }
}
