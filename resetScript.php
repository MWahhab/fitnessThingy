<?php

require_once("database/config.php");
include_once("LandingPageController.php");
include_once("Event.php");
include_once ("User.php");

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
