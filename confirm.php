<?php 
$user_id = $_GET['id'];
$token = $_GET['token'];
require 'inc/db.php';
$req = $pdo->prepare('SELECT confirmation_token FROM users WHERE id= ?');
$req->execute([$user_id]);
$user = $req->fetch();

if($user && $user->confirmation_token == $token ) {
    session_start();
    $_SESSION['auth'] = $user;
    header('Location: account.php');
    die('ok');
} else {
    die('pas ok');
}