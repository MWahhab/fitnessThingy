<?php
require_once("database/config.php");
require_once ('User.php');

$fullName        = htmlspecialchars($_POST['full-name']);
$email           = htmlspecialchars($_POST['email']);
$password        = htmlspecialchars($_POST['password']);
$confirmPassword = htmlspecialchars($_POST['confirm-password']);

$userData = [
    'full-name'        => $fullName,
    'email'            => $email,
    'password'         => $password,
    'confirm-password' => $confirmPassword
];

$isValidUserData = User::validateUserData($userData);

if (!$isValidUserData) {
    die('Invalid user data, cannot create user.');
}

// countless unnecessary whitespace and ill formatting
$user = new User($email, $password, $fullName);

/**
 * @var \database\Database $connection
 */
$user->register($connection);
