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

    public static function validateMealData(array $data): bool
    {
        if(!isset($data["name"], $data["calories"])) {
            return false;
        }

        return true;
    }
}