<?php 
require('../connection.php');
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

// Fetch donor's latitude and longitude
$donorEmail = $_SESSION['donoremail'];
$donorQuery = $con->prepare("SELECT latitude, longitude FROM users WHERE email = ?");
$donorQuery->bind_param("s", $donorEmail);
$donorQuery->execute();
$donorResult = $donorQuery->get_result()->fetch_assoc();
$donorLatitude = $donorResult['latitude'];
$donorLongitude = $donorResult['longitude'];

// Fetch blood banks with calculated distance
$bloodBankQuery = $con->prepare("
    SELECT *, 
    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + 
    sin(radians(?)) * sin(radians(latitude)))) AS distance 
    FROM users 
    WHERE user_type = 'BloodBank'
    ORDER BY distance
");
$bloodBankQuery->bind_param("ddd", $donorLatitude, $donorLongitude, $donorLatitude);
$bloodBankQuery->execute();
$bloodBankResult = $bloodBankQuery->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Blood Banks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <style>
        .bg-imggg {
            background-image: url('../img/type.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
    </style>
</head>

<body class="font-Roboto">
                <!-- <?php include("donorMenu.php"); ?> -->

    <section class="w-full p-10 ml-14">
            <!-- <?php include("algorithm.php"); ?> -->

        <h2 class="text-4xl font-serif text-center mb-12 text-red-600">Available Blood Banks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
            <?php while ($row = $bloodBankResult->fetch_assoc()) {
                $bloodBankId = $row['id'];
                $distance = round($row['distance'], 2); // Round distance to 2 decimal places

                // Fetch average rating for this blood bank
                $avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
                $avgRatingStmt->bind_param("i", $bloodBankId);
                $avgRatingStmt->execute();
                $avgResult = $avgRatingStmt->get_result()->fetch_assoc();
                $averageRating = $avgResult['average_rating'] ? round($avgResult['average_rating'], 1) : 'No ratings yet';
                ?>
                <div class="relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <img src="../img/slide1.png" alt="Blood Bank Image" class="w-full h-50 object-cover p-5">
                    <div class="bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg" style="min-height: 230px;">
                        <h3 class="text-xl font-semibold font-serif text-gray-900">
                            <?php echo htmlspecialchars($row['fullname']); ?>
                        </h3>
                        <p class="text-gray-600 mt-2">
                            <i class="fa-solid fa-map-marker-alt"></i>
                            <?php
                            $address = htmlspecialchars($row['address']);
                            $words = explode(' ', $address);
                            $firstThreeWords = implode(' ', array_slice($words, 0, 4));
                            echo $firstThreeWords;
                            ?>
                        </p>
                        <p class="text-gray-600 mt-2">
                            Distance: <?= $distance ?> km
                        </p>
                        <h3 class="">
                            <?php
                            if ($averageRating === 'No ratings yet') {
                                echo htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8');
                            } else {
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $averageRating) {
                                        echo '<span class="text-2xl text-yellow-500">&#9733;</span>'; // Filled star
                                    } else {
                                        echo '<span class="text-2xl">&#9734;</span>'; // Empty star
                                    }
                                }
                                echo ' (' . htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8') . ')'; // Display numeric rating
                            }
                            ?>
                        </h3>
                        <a href="request.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                            class="mt-4 inline-block bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            View Blood Bank
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</body>

</html>

<?php
$con->close();
?>
