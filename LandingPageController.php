<?php


class LandingPageController
{
    private Database $connection;
    private User     $user;
    private Event    $event;

    /**
     * @param Database $connection
     * @param User $user
     * @param Event $event
     */
    public function __construct(Database $connection, User $user, Event $event)
    {
        $this->connection = $connection;
        $this->user       = $user;
        $this->event      = $event;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param array $property
     * @param float $latestBid
     * @return void               Initiates purchase and completes all validation checks.
     *
     *                            Also updates the book's quantity in the database as well as the customer's balance
     */
    public function initiateConsumption(int $mealId, int $userId): void
    {
        $this->event->setEvents([]);

        $meal = $this->connection->select("meal", [], "id = {$mealId}");

        $unixTS = time();

        $currentTime = date("Y-m-d H:i:s", $unixTS);

        if (!$mealId) {
            $this->event->addEvent("No meal was consumed");
            $this->event->setError(true);
            return;
        }

        $consumedMealArr = [
            "meal_fid"    => $mealId,
            "user_fid"    => $userId,
            "count"       => $meal["count"] + 1,
            "consumed_at" => $currentTime
        ];

        if(!$this->connection->select("consumed_today", [], "user_fid = {$userId} AND meal_fid = {$mealId}")) {

            $this->connection->insert("consumed_today", $consumedMealArr);
            $this->event->addEvent("{$meal["name"]} was eaten with no crumbs left behind!");

            $this->event->setError(false);

            return;
        }

        $this->connection->update("consumed_today", ["count" => $consumedMealArr["count"]], ["user_fid" => $userId, "meal_fid" => $mealId]);
        $this->event->addEvent("Another {$meal["name"]} was eaten with no crumbs left behind!");

        $this->event->setError(false);

    }

    public function alterListing(array $alterMeal): void
    {
        if(!isset($alterMeal["mealToAlter"], $alterMeal["newName"], $alterMeal["newCalories"])) {

            $this->event->addEvent("Insufficient data provided to alter meals");

            $this->event->setError(true);

            return;
        }

        if(empty($this->connection->select("meal", [], "id = {$alterMeal['mealToAlter']['id']}"))) {

            $this->event->addEvent("The meal that's being attempted to alter doesn't exist in the db");

            $this->event->setError(true);
        }


        $this->connection->update("meal", ["name" => $alterMeal["newName"], "calories" => $alterMeal["newCalories"]], ["id" => $alterMeal["mealToAlter"]["id"]]);

        $this->event->addEvent("The meal was successfully updated");

        $this->event->setError(false);
    }

    public function clearTable(): void
    {
        if(!$this->connection->select("consumed_today")) {
            $this->event->addEvent("The day cannot be reset as the consumed list is already empty");
            $this->event->setError(true);
        }

        $this->connection->deleteAll("consumed_today");
        $this->event->addEvent("The day has been successfully reset");
        $this->event->setError(false);
    }

    public function removeListing(int $id): void
    {
        $meal = $this->connection->select("meal", [], "id = {$id}", 1);

        if (empty($meal)) {
            $this->event->addEvent("Unfortunately, this listing doesn't exist and cant be removed.");
            $this->event->setError(true);
            return;
        }

        $mealName = $meal['name'];

        $this->connection->delete("meal", ["id" => $id]);

        $this->event->addEvent("$mealName has been successfully removed.");
        $this->event->setError(false);

    }

}

