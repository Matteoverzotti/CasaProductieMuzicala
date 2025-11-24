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
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }
}
