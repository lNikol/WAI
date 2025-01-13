<?php
include_once '../models/User.php';


class AuthController {
    private $authService;

    public function __construct($db) {
        $this->authService = new AuthService($db);
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

            $user = new User($name, $email, $password);
            $result = $this->authService->register($user);
            if ($result === 'success') {
                $model['user_id'] = $this->authService->getUserId($user);
                return REDIRECT_PREFIX . 'login';
            } else {
                $model['error'] = $result;
                return 'register_view';
            }
        }

        if(isset($_SESSION['user_id'])){
            $model['user_id'] = $_SESSION['user_id'];
        }
        return 'register_view';
    }

    public function login(&$model) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->authService->login($email, $password);
            if ($user) {
                if (session_status() == PHP_SESSION_NONE || !isset($_COOKIE[session_name()]) || empty($_COOKIE[session_name()])) { 
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                    );
                    session_start(); 
                }
                $us_id = $this->authService->getUserId($user);
                $_SESSION['user_id'] = $us_id;
                $model['user_id'] = $us_id;
                $_SESSION['user_name'] = $user->getName();
                return REDIRECT_PREFIX . 'upload';
            } else {
                $model['error'] = 'Invalid credentials';
            }
        }
                if(isset($_SESSION['user_id'])){
            $model['user_id'] = $_SESSION['user_id'];
        }
        return 'login_view';
    }

    public function logout() {
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
            );
        }
        return REDIRECT_PREFIX . 'register';
    }

    public function getUserName($user_id){
        return $this->$authService->getUserName($user_id);
    }
}