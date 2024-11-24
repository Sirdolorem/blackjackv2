<?php

namespace blackjack\Helpers\DbHelper;

use blackjack\Response;

abstract class DbHelper
{
    protected \mysqli $conn;

    protected function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }
    protected function executeStatement(string $query, array $params = [], bool $getResult = false)
    {
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            Response::error("Failed to prepare query: " . $this->conn->error);
            return false;
        }

        // Dynamically generate the parameter types string
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } elseif (is_null($param)) {
                    $types .= 's'; // or 'b' if expecting a blob
                } else {
                    $types .= 's'; // fallback for unsupported types
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute() === false) {
            Response::error("Failed to execute query: " . $stmt->error);
            return false;
        }

        // Fetch results if needed
        if ($getResult) {
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : null; // return null instead of empty array
        }

        return true;
    }

}
