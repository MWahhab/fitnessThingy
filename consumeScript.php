<?php

require_once("database/config.php");
include_once("LandingPageController.php");
include_once("User.php");
include_once("Event.php");

$json = file_get_contents('php://input');
$requestData = json_decode($json, true);

$mealId = (int)$requestData['mealId'];

if (!$mealId) {
    return;
}

$event = new Event();

/**
 * @var User $user
 */
$user = unserialize($_SESSION['user']);
$temp = $user->getId();
$controller = new LandingPageController($connection, $user, $event);
$controller->initiateConsumption($mealId, $temp);

$json = json_encode($event);
echo $json;

$event->setEvents([]);
