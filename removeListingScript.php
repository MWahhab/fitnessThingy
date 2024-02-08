<?php
require_once("database/config.php");
include_once("LandingPageController.php");
include_once ("User.php");
include_once ("Event.php");

$json        = file_get_contents('php://input');
$requestData = json_decode($json, true);

$user = unserialize($_SESSION['user']);

$event = new Event();

$mealId = (int) $requestData['mealId'];

if(!$mealId) {
    return;
}

/**
 * Comes from the included config
 * @var \database\Database $connection
 */
$controller = new LandingPageController($connection, $user, $event);

$controller->removeListing($mealId);

$json = json_encode($event);

echo $json;

$event->setEvents([]);