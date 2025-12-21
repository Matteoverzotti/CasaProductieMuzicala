<?php

require_once __DIR__ . '/../Database.php';

class Model {

    /**
     * Retrieve all records from a given table.
     *
     * This method constructs and executes a simple "SELECT *" query for the
     * provided table name and returns at most 1000 resulting rows as an array of
     * associative arrays.
     *
     * In case we need more than 1000 users, pagination should be implemented.
     *
     * IMPORTANT: The `$table` parameter must be controlled internally (not
     * from user input) to avoid SQL injection risks since table names cannot
     * be parameterized in prepared statements.
     *
     * @param string $table The name of the table to query.
     * @return array An array of associative arrays representing all rows.
     */
    protected function all(string $table) : array {
        $sql = "SELECT * FROM {$table} LIMIT 1000";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a database query with optional parameters.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params Optional parameters for the SQL query.
     * @return PDOStatement|bool The resulting PDOStatement or false on failure.
     */
    protected function query(string $sql, array $params = []) : PDOStatement|bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Retrieve a record by its ID from a specified table.
     *
     * @param string $table The name of the table to query.
     * @param int $id The ID of the record to retrieve.
     * @return array|null The record as an associative array, or null if not found.
     */
    protected function getById(string $table, int $id) : ?array {
        $sql = "SELECT * FROM {$table} WHERE id = :id"; // table is controlled internally so no SQL injection risk
        $stmt = $this->query($sql, [ 'id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
