<?php

require_once __DIR__ . '/Model.php';

class User extends Model {
    private string $table = 'user';

    /**
     * Retrieve a user by their ID.
     *
     * @param int $id The ID of the user to retrieve.
     * @return array|null The user record as an associative array, or null if not found.
     */
    public function getUserById(int $id) : ?array {
        return $this->getById($this->table, $id);
    }
}
