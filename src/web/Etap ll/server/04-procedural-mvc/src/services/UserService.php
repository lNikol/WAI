<?php

class UserService {
    private $db;
    private $users;

    public function __construct($db) {
        $this->db = $db;
        $this->users = $this->db->users;
    }

    public function saveUser($name, $email, $password) {
        $existing_user = $this->users->findOne(['email' => $email]);
        if ($existing_user) {
            return 'Email already exists';
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $this->users->insertOne([
            'name' => $name,
            'email' => $email,
            'password' => $hashed_password,
        ]);

        return 'success';
    }

    public function authenticateUser($email, $password) {
        $user = $this->users->findOne(['email' => $email]);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
