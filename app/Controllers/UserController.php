<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../middleware/Auth.php';

class UserController extends Controller {

    public function showCurrentUserProfile() : void {
        $currentUser = Auth::user();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        $this->render('User/show', ['user' => $currentUser]);
    }

    // TODO: Maybe refactor this and createAccount because they share a lot of code
    public function editProfile() : void {
        $currentUser = Auth::user();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $current_password = $_POST['current_password'] ?? '';

            if (empty($current_password) || !$currentUser->verifyPassword($current_password)) {
                $error = 'Parola curentă este incorectă.';
            } elseif (empty($username) || empty($full_name) || empty($email)) {
                $error = 'Username-ul, numele complet și email-ul sunt obligatorii.';
            } elseif (!empty($password) && $password !== $confirm_password) {
                $error = 'Parolele noi nu se potrivesc.';
            } else {
                $existingUserByUsername = User::getByUsername($username);
                if ($existingUserByUsername && $existingUserByUsername->id !== $currentUser->id) {
                    $error = 'Numele de utilizator este deja folosit de alt cont.';
                } else {
                    $existingUserByEmail = User::getByEmail($email);
                    if ($existingUserByEmail && $existingUserByEmail->id !== $currentUser->id) {
                        $error = 'Email-ul este deja folosit de alt cont.';
                    } else {
                        $updatePassword = !empty($password) ? $password : null;
                        if (User::updateUser($currentUser->id, $username, $full_name, $email, $updatePassword)) {
                            $success = 'Profilul a fost actualizat cu succes!';
                            $currentUser = User::getUserById($currentUser->id);
                        } else {
                            $error = 'A apărut o eroare la actualizarea profilului.';
                        }
                    }
                }
            }
        }

        $this->render('User/edit', [
            'user' => $currentUser,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function deleteAccount() : void {
        $currentUser = Auth::user();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (User::deleteUser($currentUser->id)) {
                Auth::logout();
                header('Location: /?deleted=1');
                exit;
            } else {
                $error = 'A apărut o eroare la ștergerea contului.';
            }
        } else {
            $error = null;
        }

        $this->render('User/delete', [
            'user' => $currentUser,
            'error' => $error ?? null
        ]);
    }
}
