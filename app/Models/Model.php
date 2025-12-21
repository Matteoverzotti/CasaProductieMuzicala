<?php

require_once __DIR__ . '/../Database.php';

class Model {
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
