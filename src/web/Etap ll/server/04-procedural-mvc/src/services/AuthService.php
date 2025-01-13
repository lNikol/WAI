<?php


class AuthService {
    private $db;
    private $users;

    public function __construct($db) {
        $this->db = $db;
        $this->users = $this->db->users;
    }

    public function register(User $user) {
        $existing_user = $this->users->findOne(['email' => $user->getEmail()]);
        if ($existing_user) {
            return 'Email already exists';
        }

        $hashed_password = password_hash($user->getPassword(), PASSWORD_DEFAULT);

        $this->users->insertOne([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $hashed_password,
        ]);

        return 'success';
    }

    public function login($email, $password) {
        $user_data = $this->users->findOne(['email' => $email]);
        if ($user_data && password_verify($password, $user_data['password'])) {
            return new User($user_data['name'], $user_data['email'], $user_data['password']);
        }
        return false;
    }

    public function getUserId(User $user) { 
        return $this->users->findOne(['email' => $user->getEmail()])['_id'];
    }
}
