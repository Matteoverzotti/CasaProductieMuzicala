<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../middleware/Auth.php';

class AuthController extends Controller {

    public function login() : void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $_SESSION['flash'] = ['message' => 'Username-ul și parola sunt obligatorii.', 'type' => 'error'];
            } elseif (!$this->validateRecaptcha()) {
                $_SESSION['flash'] = ['message' => 'Validarea reCaptcha a eșuat.', 'type' => 'error'];
            } else {
                $user = User::getByUsername($username);
                if ($user && password_verify($password, $user->password_hash)) {
                    Auth::loginSetCookies($user->id);
                    header('Location: /');
                    exit;
                } else {
                    $_SESSION['flash'] = ['message' => 'Credențiale invalide.', 'type' => 'error'];
                }
            }
        }

        $this->render('Auth/login');
    }

    public function logout() : void {
        Auth::logout();
        header('Location: /');
        exit;
    }

    public function register() : void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $_SESSION['flash'] = ['message' => 'Toate câmpurile sunt obligatorii.', 'type' => 'error'];
            } elseif ($password !== $confirmPassword) {
                $_SESSION['flash'] = ['message' => 'Parolele nu se potrivesc.', 'type' => 'error'];
            } elseif (!$this->validateRecaptcha()) {
                $_SESSION['flash'] = ['message' => 'Validarea reCaptcha a eșuat.', 'type' => 'error'];
            } else {
                if (User::getByUsername($username)) {
                    $_SESSION['flash'] = ['message' => 'Numele de utilizator este deja folosit.', 'type' => 'error'];
                } elseif (User::getByEmail($email)) {
                    $_SESSION['flash'] = ['message' => 'Email-ul este deja folosit.', 'type' => 'error'];
                } else {
                    $id = User::createUser($username, $full_name, $email, $password);
                    Auth::loginSetCookies((int)$id);
                    $_SESSION['flash'] = ['message' => 'Cont creat cu succes!', 'type' => 'success'];
                    header('Location: /');
                    exit;
                }
            }
        }

        $this->render('Auth/register');
    }
}
