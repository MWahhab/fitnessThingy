<?php

require_once("database/config.php");
require_once ("User.php");
require_once ("LoginController.php");

$loginAttempt = new User(htmlspecialchars($_POST['username']), htmlspecialchars($_POST['password']));

/**
 * @var $connection database\Database
 */
$controller   = new LoginController($connection, $loginAttempt);

$user = $controller->isValidLogin();

if (!$user->getId()) {
    die('Invalid login credentials');
}


    $_SESSION['isLoggedIn'] = true;
    $_SESSION['user']       = serialize($user);
    $_SESSION['isAdmin']    = $user->isAdmin();

    header("location: http://localhost/fitnessThingy/landingPage.php");
    unset($loginAttempt, $user, $controller);
    exit();
