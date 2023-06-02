<?php
class Auth{

    public function __construct() {
    }

    public function register($db, $username, $password, $email) {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $token = Str::random(60);
        $resetToken = "";
        $rememberToken = "";
        $db->query("INSERT INTO users SET username = ?, password = ?, email = ?, confirmation_token = ?, reset_token = ?, remember_token = ?", [
            $username, 
            $password, 
            $email, 
            $token, 
            $resetToken, 
            $rememberToken]);
        $user_id = $db->lastInsertId();

        // Configuration des paramètres SMTP pour MailDev
        $smtpHost = 'localhost';
        $smtpPort = 1025;
        $smtpUsername = ''; 
        $smtpPassword = ''; 

        ini_set('SMTP', $smtpHost);
        ini_set('smtp_port', $smtpPort);

        // Utiliser la fonction mail pour envoyer l'e-mail
        $to = $_POST['email'];
        $subject = 'Confirmation de votre compte';
        $message = "Afin de valider votre compte, merci de cliquer sur ce lien :\n\nhttp://localhost:8888/projet-php/confirm.php?id=$user_id&token=$token";
        $headers = 'From: Nassim <zoubeirnassim@gmail.com>';


        if (mail($to, $subject, $message, $headers)) {
           App::redirect('login.php');
        } else {
            echo 'Une erreur est survenue lors de l\'envoi de l\'e-mail.';
        }

    }
        public function confirm($db, $user_id, $token, $session) {
            $user = $db->query('SELECT * FROM users WHERE id = ? ', [$user_id])->fetch();
        
            if ($user && $user->confirmation_token == $token) {
                $db->query('UPDATE users SET confirmation_token = NULL, confirmed_at = NOW() WHERE id = ?', [$user_id]);
                $session->write('auth', $user);
                return true;
            }
        
            return false;
        }  
        
        public function restrict($session) {
           
            if(!$session->read('auth')){
               $session->setFlash('danger', "Vous n'avez pas le droit d'accéder à cette page");
                header('Location: login.php');
                exit();
            }
        }
        
}