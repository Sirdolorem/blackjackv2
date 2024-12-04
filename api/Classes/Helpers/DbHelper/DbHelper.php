<?php

namespace blackjack\Helpers\DbHelper;

use blackjack\Database;
use blackjack\Response;

abstract class DbHelper
{
    protected \mysqli $conn;

    /**
     * Constructor to initialize the database connection.
     * Establishes a connection to the database using the Database singleton.
     */
    protected function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Executes an SQL statement with the given parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @param bool $getResult Whether to fetch and return the results of the query.
     * @param bool $returnStmt Return stmt for debugging purpose.
     * @return mixed The result of the query if $getResult is true, or a boolean indicating success/failure.
     */
    protected function executeStatement(string $query, array $params = [], bool $getResult = false, bool $returnStmt = false)
    {
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            Response::error("Failed to prepare query: " . $this->conn->error);
            return false;
        }

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
                    $types .= 's';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute() === false) {
            Response::error("Failed to execute query: " . $stmt->error);
            return false;
        }

        if ($getResult) {
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : null;
        }

        if ($returnStmt) {
            return $stmt;
        }

        return true;
    }
}
