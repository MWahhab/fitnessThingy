<?php

require_once("database/config.php");
require_once ("User.php");
require_once ("LoginController.php");

$loginAttempt = new User(htmlspecialchars($_POST['username']), htmlspecialchars($_POST['password']));
$controller   = new LoginController($connection, $loginAttempt);

$user = $controller->isValidLogin();

if (!$user->getId()) {
    die('Invalid login credentials');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    $_SESSION['isLoggedIn'] = true;
    $_SESSION['user']       = serialize($user);
    $_SESSION['isAdmin']    = $user->isAdmin();

    header("location: http://localhost/fitnessThingy/landingPage.php");
    unset($loginAttempt, $user, $controller);
    exit();
}
