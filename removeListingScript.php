<?php
include_once ("Database.php");
include_once("LandingPageController.php");
include_once ("User.php");
include_once ("Event.php");

$json        = file_get_contents('php://input');
$requestData = json_decode($json, true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = unserialize($_SESSION['user']);

try {
    $connection = new Database();
} catch (Exception $e)  {
    echo $e->getMessage();
    return;
}

$event = new Event();

$mealId = (int) $requestData['id'];

if(!$mealId) {
    return;
}

$controller = new LandingPageController($connection, $user, $event);

$controller->removeListing($mealId);

$json  = json_encode($event);

echo $json;

$event->setEvents([]);