<?php

include_once("Database.php");
include_once("LandingPageController.php");
include_once("Event.php");
include_once ("User.php");


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $connection = new Database();
} catch (Exception $e) {
    echo $e->getMessage();
    return;
}

$event = new Event();

/**
 * @var User $user
 */
$user = unserialize($_SESSION['user']);
$temp = $user->getId();
$controller = new LandingPageController($connection, $user, $event);
$controller->clearTable();

$json = json_encode($event);
echo $json;

$event->setEvents([]);
