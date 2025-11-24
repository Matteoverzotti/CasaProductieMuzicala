<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../middleware/Auth.php';

class AuthController extends Controller {

    public function login() : void {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Username-ul și parola sunt obligatorii.';
            } else {
                $user = User::getByUsername($username);
                if ($user && password_verify($password, $user->password_hash)) {
                    Auth::loginSetCookies($user->id);
                    header('Location: /');
                    exit;
                } else {
                    $error = 'Credențiale invalide.';
                }
            }
        }

        $this->render('Auth/login', ['error' => $error]);
    }

    public function logout() : void {
        Auth::logout();
        header('Location: /');
        exit;
    }

    public function register() : void {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = 'Toate câmpurile sunt obligatorii.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Parolele nu se potrivesc.';
            } else {
                if (User::getByUsername($username)) {
                    $error = 'Numele de utilizator este deja folosit.';
                } elseif (User::getByEmail($email)) {
                    $error = 'Email-ul este deja folosit.';
                } else {
                    $id = User::createUser($username, $full_name, $email, $password);
                    Auth::loginSetCookies((int)$id);
                    header('Location: /');
                    exit;
                }
            }
        }

        $this->render('Auth/register', ['error' => $error]);
    }
}
