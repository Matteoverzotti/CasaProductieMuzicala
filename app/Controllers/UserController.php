<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';

class UserController extends Controller {

    public function showUserView(int $id) : void {
        $user = User::getUserById($id);

        if ($user) {
            $this->render('User/show', ['user' => $user]);
        } else {
            http_response_code(404);
            echo "<h1>User Not Found</h1>";
        }
    }
}
