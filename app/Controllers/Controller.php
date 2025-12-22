<?php

class Controller {
    /**
     * Render a view template with provided data.
     *
     * @param string $view The name of the view file (without .php extension).
     * @param array $data An associative array of data to be extracted for the view.
     * @return void
     */
    protected function render(string $view, array $data = []) : void {
        if (isset($_SESSION['flash'])) {
            $data['flash'] = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        $data['csrf_token'] = $_SESSION['csrf_token'] ?? '';
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }
}
