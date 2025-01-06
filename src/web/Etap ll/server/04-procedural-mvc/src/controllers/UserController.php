<?php

class UserController {
    private $userService;

    public function __construct($db) {
        $this->userService = new UserService($db);
    }

    public function register(&$model) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $model['error'] = 'Passwords do not match';
                return 'register_view';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $model['error'] = 'Invalid email format';
                return 'register_view';
            }

            $result = $this->userService->saveUser($name, $email, $password);
            if ($result === 'success') {
                return REDIRECT_PREFIX . 'login';
            } else {
                $model['error'] = $result;
                return 'register_view';
            }
        }

        return 'register_view';
    }

    public function login(&$model) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userService->authenticateUser($email, $password);
            if ($user) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = (string) $user['_id'];
                $_SESSION['user_name'] = $user['name'];
                return REDIRECT_PREFIX . 'upload';
            } else {
                $model['error'] = 'Invalid credentials';
            }
        }

        return 'login_view';
    }

    public function logout() {
        session_destroy();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        return REDIRECT_PREFIX . 'register';
    }
}
