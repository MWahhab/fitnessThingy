<?php

class Meal
{
    private int    $id;
    private string $name;
    private int    $calories;

    /**
     * @param string $name
     * @param int $calories
     */
    public function __construct(string $name, int $calories)
    {
        $this->name     = $name;
        $this->calories = $calories;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCalories(): int
    {
        return $this->calories;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }



    public function register(\database\Database $connection): void
    {
        if($connection->select("meal", [], "name = '{$this->getName()}'")) {
            die("This meal already exists");
        }

        $mealData = [
            "name"     => $this->getName(),
            "calories" => $this->getCalories()
        ];

        $connection->insert("meal", $mealData);
        header("Location: http://localhost/fitnessThingy/landingPage.php");
    }

    // better naming in the future
    public static function validateRegisterMealData(array $data): bool
    {
        if(!isset($data["name"], $data["calories"])) {
            return false;
        }

        return true;
    }

    // we need this in order to validate that we have at least one field updating
    public static function validateUpdateMealData(array $data): bool
    {
        if (!isset($data['mealId']) || strlen($data['mealId']) < 1) {
            return false;
        }

        if(!isset($data["newName"], $data["newCalories"])) {
            return false;
        }

        if (!(strlen(trim($data["newName"])) || (int) $data["newCalories"] > 0)) {
            return false;
        }

        return true;
    }
}