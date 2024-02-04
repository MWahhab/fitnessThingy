<?php
include_once ("Database.php");
include_once("LandingPageController.php");
include_once ("User.php");
include_once ("Event.php");


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!$_SESSION["isAdmin"]) {
    die("You ain't no admin lil man");
}

$json        = file_get_contents('php://input');
$requestData = json_decode($json, true);

$meal        = (array)  $requestData['meal'];
$newName     = (string) $requestData['newName'];
$newCalories = (int)    $requestData['newCalories'];

if (!$newName || !$newCalories || empty($meal)) {
    return;
}

$alterMeal = [
    "mealToAlter" => $meal,
    "newName"     => $newName,
    "newCalories" => $newCalories
];

try {
    $connection = new Database();
} catch (Exception $e)  {
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
$controller->alterListing($alterMeal);

$jsonEvent   = json_encode($event);
echo $jsonEvent;

$event->setEvents([]);

