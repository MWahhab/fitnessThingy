<?php
require_once("database/config.php");
require_once ('Meal.php');

if(!$_SESSION["isAdmin"]) {
    die("You ain't no admin lil man");
}

$name     = htmlspecialchars($_POST['name']);
$calories = htmlspecialchars($_POST["calories"]);

$mealData = [
    "name"     => $name,
    "calories" => $calories
];

if (!Meal::validateRegisterMealData($mealData)) {// unnecessary variable
    die('Invalid meal data, cannot register this property.');
}

$meal = new Meal($name, $calories);

/**
 * @var \database\Database $connection
 */
$meal->register($connection);
