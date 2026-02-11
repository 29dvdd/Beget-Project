<?php
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    public function login()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                $error = "Заполните все поля";
            } else {

                $userModel = new User();
                $user = $userModel->findByEmail($email);

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $error = "Неверный email или пароль";
                } else {

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    header("Location: ?page=profile");
                    exit;
                }
            }
        }

        require __DIR__ . '/../../templates/login.php';
    }

    public function register()
    {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $passConfirm = $_POST['password_confirm'] ?? '';

            if (empty($email) || empty($pass)) {
                $error = "Заполните все поля!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Некорректный email";
            } elseif ($pass !== $passConfirm) {
                $error = "Пароли не совпадают";
            } else {

                $userModel = new User();

                if ($userModel->findByEmail($email)) {
                    $error = "Email уже зарегистрирован";
                } else {
                    $userModel->create($email, $pass);
                    $success = "Регистрация успешна!";
                }
            }
        }

        require __DIR__ . '/../../templates/register.php';
    }
}
