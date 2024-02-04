<?php

include_once ("User.php");
include_once ("Database.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isLoggedIn'])) {
    die('You have to be authenticated to view this page');
}

/**
 * @var User $user
 */
$user           = unserialize($_SESSION['user']);
$userIsLoggedIn = $_SESSION['isLoggedIn'];

$imageLink = 'https://cleobuttera.com/wp-content/uploads/2018/03/lifted-baklava-720x720.jpg';

try {
    $connection = new Database();
} catch (Exception $e)  {
    echo $e->getMessage();
    return;
}

$meals               = $connection->select('meal');
$consumedMeals       = $connection->select("consumed_today", [], "user_fid = {$user->getId()}");

$totalCaloriesConsumed = 0;

foreach($consumedMeals as $consumedMeal) {
    $meal = $connection->selectThroughPivot("consumed_today", "meal", "meal_fid", $consumedMeal['meal_fid']);
    $consumedMeal["meal"] = $meal;
    $totalCaloriesConsumed += $meal['calories'] * $consumedMeal['count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puleadd Weight Loss Assistant</title>
    <!--<link rel="stylesheet" href="fitnessThingy/styles.css">-->
    <style>
        .meal {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .meal img {
            max-width: 100px;
            max-height: 150px;
        }
        .meal-details {
            display: flex;
            align-items: center;
        }
        .meal-info {
            margin-left: 15px;
        }
    </style>
</head>
<body>

<a href="http://localhost/fitnessThingy/mealRegistration.html" class="redirect-admin">
    <input id="register-meal" type="<?= $user->isAdmin() == 1 ? 'button' : 'hidden'?>" value="Register Meals">
</a>

<input id="change-day" type="button" value="Next Day" onclick="changeDays()">

<h1>Welcome to Puleadd Weight Loss Assistant!</h1>

<div>
    You are <?php
    switch (true) {
        case ($totalCaloriesConsumed < 2000):
            echo 2000 - $totalCaloriesConsumed . ' calories away from reaching your daily limit of 2000 calories in order to continue losing weight!';
            break;
        case ($totalCaloriesConsumed > 2000):
            echo $totalCaloriesConsumed - 2000 . ' calories over your daily limit of 2000 calories in order to continue losing weight! Be careful';
            break;
        default:
            echo "You have consumed the exact amount of calories recommended for you to continue losing weight safely!";
            break;
    }
    ?>
</div>
<br>
<div id="consumed-list">
    <h2>The food you've consumed today:</h2>
    <?php if (empty($consumedMeals)) : ?>
        <p>You've eaten nothing! Water fasting??</p>
    <?php else : ?>
        <?php foreach ($consumedMeals as $consumedMeal) : ?>
            <div class="meal-details meal-<?= htmlspecialchars($consumedMeal['id'])?>">
                <img src="<?= $imageLink ?>" alt="<?= htmlspecialchars($consumedMeal['meal']['name']) ?>">
                <div class="meal-info">
                    <p id="consumed-meal-name-<?= htmlspecialchars($consumedMeal['id'])?>">Meal: <?= htmlspecialchars($consumedMeal['meal']['name']) ?></p>
                    <p id="consumed-meal-calories-<?= htmlspecialchars($consumedMeal['id'])?>">Number of Calories: <?= htmlspecialchars($consumedMeal['meal']['calories']) ?></p>
                    <p id="number-eaten-<?= htmlspecialchars($consumedMeal['id'])?>">Number eaten: <?= htmlspecialchars($consumedMeal['count']) ?></p>
                    <p>Last eaten at: <?= htmlspecialchars($consumedMeal['consumed_at']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<h2>The meals available today are:</h2>

<div id="meal-list">
    <?php foreach ($meals as $meal): ?>
        <div class="meal">
            <div class="meal-details meal-<?= htmlspecialchars($meal['id'])?>">
                <img src="<?= $imageLink ?>" alt="<?= htmlspecialchars($meal['name']) ?>">
                <div class="meal-info">
                    <p id="meal-name-<?= htmlspecialchars($meal['id'])?>">Meal: <?= htmlspecialchars($meal['name']) ?></p>
                    <p id="meal-calories-<?= htmlspecialchars($meal['id'])?>">Number of Calories: <?= htmlspecialchars($meal['calories']) ?></p>
                    <br>
                    <p>Press the button below to consume this meal and add it to your daily calorie intake</p>
                    <input type="button" value="Consume" onclick="consumeMeal(<?= htmlspecialchars($meal['id'])?>)">
                    <br>
                    <br>
                    <?php if ($user->isAdmin() == 1): ?>
                        <h2>If you would like to alter this meal, please submit the new details below!</h2>

                        <div class="form-group">
                            <label for="new-name">New Meal Name:</label>
                            <input type="text" id="new-name" name="new-name" title="Enter the new meal name!" required>
                        </div>
                        <div class="form-group">
                            <label for="new-calories">New Number Of Calories:</label>
                            <input type="number" id="new-calories" name="new-calories" title="Enter the new number of calories!" required>
                        </div>

                        <br>

                        <div class="form-group">
                            <!--<?php echo htmlspecialchars(json_encode($meal));?> -->
                            <input type="submit" value="Alter Meal" onclick='alterMeal(<?php echo htmlspecialchars(json_encode($meal));?>)'>
                        </div>

                        <h2>If you would like to delete this meal from the system, please press the button below!</h2>
                        <input type="button" value="Delete Meal" onclick="removeListing(<?= htmlspecialchars($meal['id'])?>">

                    <?php endif; ?>


                </div>
            </div>


        </div>
    <?php endforeach; ?>
</div>


<div id="output">

</div>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>

    function changeValue(meal) {
        let mealObj = JSON.parse(meal);
        let mealId  = mealObj.id;
        document.getElementById(`alter-meal-${mealId}`).value = meal;
    }

    function changeDays() {
        axios.post("http://localhost/fitnessThingy/resetScript.php")
            .then(function (response) {

                console.log(response);

                if (response.data.length <= 0)
                {
                    console.log("Nothing has been deleted");
                    return;
                }
                console.log(response.data);

                resetConsumedList(response.data);
            })
            .catch(function (error) {
                console.log('Error fetching data:', error);
            });
    }

    function resetConsumedList(data) {
        if (!data["error"]) {
            let consumedList = document.getElementById("consumed-list");
            consumedList.innerHTML = "";

            let h2Element = document.createElement("h2");
            h2Element.textContent = "The food you've consumed today:";
            consumedList.appendChild(h2Element);

            let pElement = document.createElement("p");

            pElement.textContent = "You've eaten nothing! Water fasting??";

            consumedList.appendChild(pElement);
        }
    }

    function alterMeal(meal) {


        console.log(meal);

        axios.post('http://localhost/fitnessThingy/alterMeal.php', {
            newName    : document.getElementById("new-name").value,
            newCalories: document.getElementById("new-calories").value,
            meal       : meal
        })
            .then(function (response) {

                console.log(response);

                if (response.data.length <= 0)
                {
                    console.log("No meal has been altered");
                    return;
                }
                console.log(response.data);

                changeMealDetails(response.data, meal);
            })
            .catch(function (error) {
                console.log('Error fetching data:', error);
            });
    }

    function changeMealDetails(data, meal) {

        console.log(data);

        if (!data["error"]) {

            document.getElementById(`meal-name-${meal["id"]}`).innerHTML = "Meal: " + document.getElementById("new-name").value;
            document.getElementById(`meal-calories-${meal["id"]}`).innerHTML = "Number of Calories " + document.getElementById("new-calories").value;

            if (document.getElementById("consumed-meal-" + meal["id"])) {
                document.getElementById(`consumed-meal-name-${meal["id"]}`).innerHTML = "Meal: " + document.getElementById("new-name").value;
                document.getElementById(`consumed-meal-calories-${meal["id"]}`).innerHTML = "Number of Calories " + document.getElementById("new-calories").value;
            }
        }

        let outputDiv = document.getElementById("output");
        outputDiv.innerHTML = "";

        for (let i = 0; i < data["events"].length; i++) {
            let event = data["events"][i];
            let output = document.createElement("p");
            output.textContent = event;

            outputDiv.appendChild(output);
        }
    }



    function consumeMeal(mealId) {
        console.log(mealId);

        axios.post('http://localhost/fitnessThingy/consumeScript.php', {
            mealId: mealId
        })
            .then(function (response) {

                console.log(response);

                if (response.data.length <= 0)
                {
                    console.log("Nothing has been consumed");
                    return;
                }
                console.log(response.data);

                printToScreen(response.data, mealId);
            })
            .catch(function (error) {
                console.log('Error fetching data:', error);
            });
    }

//    function printToScreen(data, mealId) {
//
//        if(!data["error"]) {
//            if(!document.getElementById("number-eaten-" + mealId)) {
//                let newConsumedMeal = document.createElement("div");
//                newConsumedMeal.classList.add("meal-details", `meal-${mealId}`);
//
//                var imgElement = document.createElement("img");
//                imgElement.src = "<?php //= $imageLink ?>//";
//                imgElement.alt = "<?php //= htmlspecialchars($consumedMeal['name']) ?>//";
//                newDiv.appendChild(imgElement);
//
//                let mealInfoDiv = document.createElement("div");
//                mealInfoDiv.classList.add("meal-info");
//
//// Append the meal-info div to the main div
//                newDiv.appendChild(mealInfoDiv);
//
//// You can now append the newDiv to the DOM as needed
//// For example, assuming there's a container with the id "meal-container"
//                document.getElementById("meal-container").appendChild(newDiv);
//
//            } else {
//
//                let countEle = document.getElementById("number-eaten-" + mealId);
//                let count = parseInt(countEle.textContent.split(': ')[1]);
//                let newCount = count + 1;
//                countEle.textContent = "Number eaten: " + newCount;
//            }
//
//        }
//
//            document.getElementById("property-" + property["id"]).querySelector("#current-bidder").innerHTML = "Current Bid Held By: Bidder #" + userId;
//        }
//
//        let outputDiv = document.getElementById("output");
//        outputDiv.innerHTML = "";
//
//        for (let i = 0; i < data.length; i++) {
//            let event = data[i];
//            let output = document.createElement("p");
//            output.textContent = event;
//
//            outputDiv.appendChild(output);
//        }
//    }

    function removeListing(id) {

        console.log(id);

        axios.post('http://localhost/fitnessThingy/removeListingScript.php', {
            propertyId: id


        })
            .then(function (response) {

                console.log(response);

                if (response.data.length <= 0)
                {
                    console.log("Nothing has been bid on");
                    return;
                }
                console.log(response.data);

                alterListing(response.data, id);
            })
            .catch(function (error) {
                console.log('Error fetching data:', error);
            });
    }

    function alterListing (data, mealId) {

        let output = document.getElementById("output");
        output.innerHTML = "";

        let childElement = document.createElement("p");
        childElement.textContent = data["event"];

        if(!data["error"]) {

            alterView(mealId);
        }

    }

    function alterView(mealId) {
        let elementsToRemove = document.getElementsByClassName(`meal-${mealId}`);
        Array.from(elementsToRemove).forEach(element => {
            element.remove();
        });
    }

    function toggleAdminButton($buttonDisplay, buttonId) {
        let buttonIdElement = document.getElementById(buttonId);

        if (buttonIdElement.value == 1) {
            buttonIdElement.style.display = "inline"; // or "block"
            buttonIdElement.value = $buttonDisplay;
            return;
        }

        buttonIdElement.style.display = "none";
    }


</script>
