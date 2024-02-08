<?php


class LandingPageController
{
    private \database\Database $connection;
    private User     $user;
    private Event    $event;

    /**
     * @param \database\Database $connection
     * @param User $user
     * @param Event $event
     */
    public function __construct(\database\Database $connection, User $user, Event $event)
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
     * @param int $mealId
     * @param int $userId
     * @return void               Initiates purchase and completes all validation checks.
     *
     *                            Also updates the book's quantity in the database as well as the customer's balance
     */
    public function initiateConsumption(int $mealId, int $userId): void
    {
        $this->event->setEvents([]);

        $unixTS = time();
        $currentTime = date("Y-m-d H:i:s", $unixTS);

        if (!$mealId) {
            $this->event->addEvent("No meal was consumed");
            $this->event->setError(true);
            return;
        }

        $consumedMeal = $this->connection->select(
            "consumed_today",
            ["count"],
            "user_fid = {$userId} AND meal_fid = {$mealId}",
            1
        );

        if (!$consumedMeal) {
            $consumedMealArr = [
                "meal_fid"    => $mealId,
                "user_fid"    => $userId,
                "count"       => 1,
                "consumed_at" => $currentTime
            ];

            $this->connection->insert("consumed_today", $consumedMealArr);
            $this->event->addEvent("Meal was eaten with no crumbs left behind!");
        } else {

            $newCount = $consumedMeal['count'] + 1;
            $this->connection->update(
                "consumed_today",
                ["count"    => $newCount],
                ["user_fid" => $userId, "meal_fid" => $mealId]
            );
            $this->event->addEvent("Another meal was eaten with no crumbs left behind!");
        }

        $this->event->setError(false);
    }

    public function alterListing(array $alterMeal): void
    {
        if (!Meal::validateUpdateMealData($alterMeal)) {
            $this->event->addEvent("Insufficient data provided to alter meals");

            $this->event->setError(true);

            return;
        }

        if(empty($this->connection->select("meal", [], "id = {$alterMeal['mealId']}"))) {

            $this->event->addEvent("The meal that's being attempted to alter doesn't exist in the db");

            $this->event->setError(true);

            return;
        }

        $this->connection->update(
            "meal",
            [
                "name"     => (string) $alterMeal["newName"],
                "calories" => (int)    $alterMeal["newCalories"]
            ],
                ["id"      => (int)    $alterMeal["mealId"]]
        );

        $this->event->addEvent("The meal was successfully updated");

        $this->event->setError(false);
    }

    public function clearTable(): void
    {
        // wow what the hell did I just see..
        // we're duplicating queries for what reason exactly? We're deleting anyway are we not.. wow
        $this->connection->deleteAll("consumed_today");
        $this->event->addEvent("The day has been successfully reset");
        $this->event->setError(false);
    }

    public function removeListing(int $id): void
    {
        if (!$id) {
            $this->event->addEvent("Unfortunately, this listing doesn't exist and cant be removed.");
            $this->event->setError(true);

            return;
        }

        $meal = $this->connection->select("meal", [], "id = {$id}", 1);

        if (empty($meal)) {
            $this->event->addEvent("Unfortunately, this listing doesn't exist and cant be removed.");
            $this->event->setError(true);
            return;
        }

        $this->connection->delete("meal", ["id" => $id]);
        $this->connection->delete("consumed_today", ["meal_fid" => $id]);

        $this->event->addEvent("{$meal['name']} has been successfully removed.");
        $this->event->setError(false);
    }

}

