<?php
require_once("database/config.php");
include_once("LandingPageController.php");
include_once ("User.php");
include_once ("Event.php");
require_once ('Meal.php');


if(!$_SESSION["isAdmin"]) {
    die("You ain't no admin lil man");
}

$json        = file_get_contents('php://input');
$requestData = json_decode($json, true);

$alterMeal = [
    'mealId'      => (int)    $requestData['meal']['id'] ?: 0,
    "newName"     => (string) $requestData['newName'] ?: $requestData['meal']['name'] ?? '',
    "newCalories" => (int)    $requestData['newCalories'] ?: $requestData['meal']['calories'] ?? '',
];

$event = new Event();

/**
 * @var User $user
 */
$user = unserialize($_SESSION['user']);

/**
 * @var $connection \database\Database
 */
$controller = new LandingPageController($connection, $user, $event);
$controller->alterListing($alterMeal);

$jsonEvent = json_encode($event);
echo $jsonEvent;

$event->setEvents([]);

