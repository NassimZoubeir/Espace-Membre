<?php 
 require_once 'inc/functions.php'; 
session_start();

if(!empty($_POST)) {

    $errors = array();
    require_once 'inc/db.php';

    if(empty($_POST['username']) || !preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])){
        $errors['username'] ="Votre pseudo n'est pas valide (alphanumérique)";
    } else {
        $req = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $req->execute([$_POST['username']]);
        $user = $req->fetch();
       if($user) {
        $errors['username'] = 'Ce pseudo est déjà pris';
       }
    }

    if(empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        $errors['email'] ="Votre email n'est pas valide";
     } else {
        $req = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $req->execute([$_POST['email']]);
        $user = $req->fetch();
       if($user) {
        $errors['email'] = 'Cet email est déjà utilisé pour un autre compte';
       }
    }

    if(empty($_POST['password']) || $_POST['password'] != $_POST['password_confirm']){
        $errors['password'] = "Vous devez rentrer un mot de passe valide";
    }

    if (empty($errors)) {

        require_once 'inc/functions.php';

        $req = $pdo->prepare("INSERT INTO users SET username = ?, password = ?, email = ?, confirmation_token = ?, reset_token = ?, remember_token = ?");
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $token = str_random(60);
        $resetToken = "";
        $rememberToken = "";
        $req->execute([$_POST['username'], $password, $_POST['email'], $token, $resetToken, $rememberToken]);
        $user_id = $pdo->lastInsertId();

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
            $_SESSION['flash']['success'] = "Un email de confirmation vous a été envoyé pour valider votre compte";
            header('Location: login.php');
            exit();
        } else {
            echo 'Une erreur est survenue lors de l\'envoi de l\'e-mail.';
        }
    }
            
        }

?>

<?php require 'inc/header.php'; ?>

<h1>S'inscrire</h1>

<?php if(!empty($errors)): ?>

    <div class="alert alert-danger">
        <p>Vous n'avez pas rempli le formulaire correctement</p>
        <ul>
            <?php foreach($errors as $error): ?>
            <li><?= $error; ?></li>
            <?php endforeach; ?> 
        </ul>
    </div>
  <?php endif; ?>

<form action="" method="POST">

    <div class="form-group">
        <label for="">Pseudo</label>
        <input type="text" name="username" class="form-control">
    </div>

     <div class="form-group">
        <label for="">Email</label>
        <input type="text" name="email" class="form-control">
    </div>

     <div class="form-group">
        <label for="">Mot de passe</label>
        <input type="password" name="password" class="form-control">
    </div>

     <div class="form-group">
        <label for="">Confirmez votre mot de passe</label>
        <input type="password" name="password_confirm" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">M'inscrire</button>
    
</form>

<?php require 'inc/footer.php'; ?>