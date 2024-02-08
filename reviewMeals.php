<?php

include_once ("User.php");
require_once("database/config.php");
include_once ("LandingPageController.php");

if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isAdmin']) {
    die('You have to be authenticated to view this page');
}

/**
 * @var User $user
 */
$user           = unserialize($_SESSION['user']);

$imageLink = 'https://t3.ftcdn.net/jpg/06/77/21/80/360_F_677218014_VrMyeS8jY1u0kqxSZQLfiCfEpzgixztB.jpg';

$controller     = new AuctionHouseController($connection, $user);

$properties = $connection->select('property', []);

foreach($properties as $property) {
    if(strtotime(str_replace("/", "-", $property["closing_at"])) < time()) {
        $connection->update("property", ["expired" => 1], ["id" => $property["id"]]);
    }
}

$expiredProperties    = $connection->select('property', [], "current_bidder IS NULL AND expired = 1");
$winningBidProperties = $connection->select('property', [], "current_bidder IS NOT NULL AND expired = 1");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commie Housing</title>
    <style>
        .property {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .property img {
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

<a href="http://localhost/biddingSite/reviewBids.php" class="redirect-admin">
    <input id="reviewBids" type="hidden" value="<?= $user->isAdmin() ?>">
</a>

<h1>You're viewing the expired/won bids!</h1>

<div>The listings that've got a winning bid:
    <?php if (empty($winningBidProperties)) : ?>
        <p>No bids won so far</p>
    <?php else : ?>
    <?php foreach ($winningBidProperties as $winningBid) : ?>
    <div id="property-<?= htmlspecialchars($winningBid['id'])?>" class="property-details">
        <img src="<?= $imageLink ?>" alt="<?= htmlspecialchars($winningBid['address']) ?>">
        <div class="property-info">
            <h2><?= htmlspecialchars($winningBid['address']) ?></h2>
            <p>Property Type: <?= htmlspecialchars($winningBid['property_type']) ?></p>
            <p>Bedrooms: <?= htmlspecialchars($winningBid['bedrooms']) ?></p>
            <p>Bathrooms: <?= htmlspecialchars($winningBid['bathrooms']) ?></p>
            <p>Parking: <?= htmlspecialchars($winningBid['parking']) ?></p>
            <p>Description: <?= htmlspecialchars($winningBid['description']) ?></p>
            <p>Starting Bid: £<?= htmlspecialchars($winningBid['starting_bid']) ?></p>
            <p>Current Bid: <?= htmlspecialchars($winningBid['current_bid']) == 0 ?
                    "No bids so far!" : "£" . htmlspecialchars($winningBid['current_bid']) ?></p>
            <p>Winning Bid Held By: <?= htmlspecialchars($winningBid['current_bidder']) == NULL ?
                    "No bids so far!" : "Bidder #" . htmlspecialchars($winningBid['current_bidder']) ?></p>
            <p>Bidding Ended At: <?= htmlspecialchars($winningBid['closing_at']) ?></p>
            <p>Status: <?= strtotime(str_replace("/", "-", $winningBid["closing_at"])) < time() ? "Won" :
                    "Still up for auction"?></p>

            <h2>
                Either:
            </h2>

            <input type="button" value="Approve and remove property upon bidder acknowledgement" onclick="alterView(<?= htmlspecialchars($winningBid['id'])?>); <?= $controller->reviewResult($winningBid['id'], true);?>">
            <input type="button" value="Reject and remove property upon bidder acknowledgement" onclick="alterView(<?= htmlspecialchars($winningBid['id'])?>); <?= $controller->reviewResult($winningBid['id'], false);?>">

            <br>

            <h3>
                OR reject and relist the property below
            </h3>
            <div class="registration-form">
                <form action="http://localhost/biddingSite/relistProperty.php" method="post">

                    <input type="hidden" id="id" value="<?= $winningBid['id']?>">

                    <h3>Register Below</h3>
                    <div class="form-group">
                        <label for="starting-bid">Starting Bid:</label>
                        <input type="number" id="starting-bid" name="starting-bid" required>
                    </div>
                    <div class="form-group">
                        <label for="closing-date">Closing Date:</label>
                        <input type="date" id="closing-date" name="closing-date" title="Day/Month/Year" required>
                    </div>
                    <div class="form-group">
                        <label for="closing-time">Closing Time:</label>
                        <input type="time" id="closing-time" name="closing-time" step="1" title="Hours:Minutes:Seconds" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Reject and Relist">
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<h2>The property listings that have expired without any bids are below:</h2>

<?php if (empty($expiredProperties)) : ?>
    <p>No unbidded properties expired so far</p>
<?php else : ?>
<div id="property-list">
    <?php foreach ($expiredProperties as $property): ?>
        <div class="property">
            <div id="property-<?= htmlspecialchars($property['id'])?>" class="property-details">
                <img src="<?= $imageLink ?>" alt="<?= htmlspecialchars($property['address']) ?>">
                <div class="property-info">
                    <h2><?= htmlspecialchars($property['address']) ?></h2>
                    <p>Property Type: <?= htmlspecialchars($property['property_type']) ?></p>
                    <p>Bedrooms: <?= htmlspecialchars($property['bedrooms']) ?></p>
                    <p>Bathrooms: <?= htmlspecialchars($property['bathrooms']) ?></p>
                    <p>Parking: <?= htmlspecialchars($property['parking']) ?></p>
                    <p>Description: <?= htmlspecialchars($property['description']) ?></p>
                    <p>Starting Bid: £<?= htmlspecialchars($property['starting_bid']) ?></p>
                    <p>Current Bid: <?= htmlspecialchars($property['current_bid']) == 0 ?
                            "No bids placed!" : "£" . htmlspecialchars($property['current_bid']) ?></p>
                    <p>Winning Bid Held By: <?= htmlspecialchars($property['current_bidder']) == NULL ?
                            "No bids placed!" : "Bidder #" . htmlspecialchars($property['current_bidder']) ?></p>
                    <p>Bidding Ended At: <?= htmlspecialchars($property['closing_at']) ?></p>
                    <p>Status: <?= strtotime(str_replace("/", "-", $property["closing_at"])) < time() ? "Expired" :
                            "Still up for auction"?></p>

                    <h2>
                        Either:
                    </h2>

                    <input type="button" value="Remove property" onclick="removeListing(<?= $property['id']; ?>); alterView(<?= $property['id']; ?>);">

                    <br>

                    <h3>
                        OR relist the property below
                    </h3>
                    <div class="registration-form">
                        <form action="http://localhost/biddingSite/relistProperty.php" method="post">

                            <input type="hidden" id="id" name="id" value="<?= $property['id']?>">

                            <h3>Register Below</h3>
                            <div class="form-group">
                                <label for="starting-bid">Starting Bid:</label>
                                <input type="number" id="starting-bid" name="starting-bid" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="closing-date">Closing Date:</label>
                                <input type="date" id="closing-date" name="closing-date" title="Day/Month/Year" required>
                            </div>
                            <div class="form-group">
                                <label for="closing-time">Closing Time:</label>
                                <input type="time" id="closing-time" name="closing-time" step="1" title="Hours:Minutes:Seconds" required>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Reject and Relist">
                            </div>
                        </form>
                    </div>

                </div>
            </div>


        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>


<div id="output">

</div>
</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>

    function removeListing(propertyId) {

        console.log(propertyId);

        axios.post('http://localhost/biddingSite/removeListingScript.php', {
            propertyId: propertyId


        })
            .then(function (response) {

                console.log(response);

                if (response.data.length <= 0)
                {
                    console.log("Nothing has been bid on");
                    return;
                }
                console.log(response.data);

                alterListing(response.data, propertyId);
            })
            .catch(function (error) {
                console.log('Error fetching data:', error);
            });
    }

    function alterListing (data, propertyId) {

        let output = document.getElementById("output");
        output.innerHTML = "";

        let childElement = document.createElement("p");
        childElement.textContent = data[1];

        if(data[0]) {

            alterView(propertyId);
        }

    }

    function alterView(propertyId) {
        let removeElement = document.getElementById(`property-${propertyId}`);
        removeElement.remove();
    }

</script>
