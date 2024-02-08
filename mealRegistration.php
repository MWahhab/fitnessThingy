<?php
require_once("database/config.php");
require_once ('Meal.php');

if(!$_SESSION["isAdmin"]) {
    die("You ain't no admin lil man");
}

$name         = htmlspecialchars($_POST['name']);
$calories     = htmlspecialchars($_POST["calories"]);

$mealData = [
    "name"     => $name,
    "calories" => $calories
];

$isValidMealData = Meal::validateMealData($mealData);

if (!$isValidMealData) {
    die('Invalid meal data, cannot register this property.');
}

$meal   = new Meal($name, $calories);

$meal->register($connection);

//$queriedMeal = $connection->select("meal", [], "name = {$name} AND calories = {$calories}");
//$meal->setId($queriedMeal["id"]);
#endofscript