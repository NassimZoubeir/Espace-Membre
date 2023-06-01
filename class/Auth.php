<?php
class Auth{

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $password, $email) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $token = Str::random(60);
        $resetToken = "";
        $rememberToken = "";
        $this->db->query("INSERT INTO users SET username = ?, password = ?, email = ?, confirmation_token = ?, reset_token = ?, remember_token = ?", [
            $username, 
            $password, 
            $email, 
            $token, 
            $resetToken, 
            $rememberToken]);
        $user_id = $this->db->lastInsertId();

    }
}