<?php

require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/Controller.php';

class HomeController extends Controller {

    public function index() : void {
        $user = Auth::user();
        $this->render('home', ['user' => $user]);
    }
}
