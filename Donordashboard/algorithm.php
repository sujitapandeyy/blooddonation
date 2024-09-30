<?php
require('../connection.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

// Get logged-in user's ID
$userId = $_SESSION['donorid'];

if (!isset($userId)) {
    echo "User ID not set. Please log in.";
    exit();
}

// Function to fetch all interactions (ratings) for the logged-in user
function getUserInteractions($userId, $con) {
    $userInteractions = [];

    // Fetch ratings from blood_bank_ratings
    $query = "SELECT blood_bank_id, rating AS value FROM blood_bank_ratings WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Store all interactions (ratings) for the user
    while ($row = $result->fetch_assoc()) {
        $userInteractions[$row['blood_bank_id']] = $row['value'];
    }

    return $userInteractions;
}

// Function to calculate similarity based on rating differences
function ratingSimilarity($userInteractions, $otherInteractions) {
    $similarityScore = 0;
    $count = 0;

    foreach ($userInteractions as $bloodBankId => $userRating) {
        if (isset($otherInteractions[$bloodBankId])) {
            $otherRating = $otherInteractions[$bloodBankId];
            // Calculate the similarity based on the absolute difference in ratings
            $similarityScore += 1 - abs($userRating - $otherRating) / 5; // Assuming rating is out of 5
            $count++;
        }
    }

    // Return average similarity
    return $count > 0 ? $similarityScore / $count : 0;
}

// Function to find users with similar interactions
function getSimilarUsers($userId, $userInteractions, $con) {
    $similarUsers = [];

    // Fetch all ratings from other users (excluding the logged-in user)
    $query = "SELECT user_id, blood_bank_id, rating AS value FROM blood_bank_ratings WHERE user_id != ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allUserInteractions = [];

    // Store interactions for all other users
    while ($row = $result->fetch_assoc()) {
        $allUserInteractions[$row['user_id']][$row['blood_bank_id']] = $row['value'];
    }

    // Compare each user's interactions with the logged-in user's interactions using the rating similarity
    foreach ($allUserInteractions as $otherUserId => $otherInteractions) {
        $similarity = ratingSimilarity($userInteractions, $otherInteractions);

        if ($similarity > 0) { // Only consider users with positive similarity
            $similarUsers[$otherUserId] = ['similarity' => $similarity, 'interactions' => $otherInteractions];
        }
    }

    return $similarUsers;
}

// Function to recommend blood banks based on similar users' ratings
function recommendBloodBanks($userId, $similarUsers, $userInteractions) {
    $recommendedBloodBanks = [];

    foreach ($similarUsers as $similarUserId => $similarData) {
        $similarity = $similarData['similarity'];
        $similarInteractions = $similarData['interactions'];

        foreach ($similarInteractions as $bloodBankId => $interactionValue) {
            if (!isset($userInteractions[$bloodBankId])) { // Only recommend if the user hasn't interacted with it
                // Add weighted interaction value (by similarity score) only if rated highly (e.g., > 3)
                if ($interactionValue > 3) {
                    if (!isset($recommendedBloodBanks[$bloodBankId])) {
                        $recommendedBloodBanks[$bloodBankId] = 0;
                    }
                    $recommendedBloodBanks[$bloodBankId] += ($interactionValue * $similarity);
                }
            }
        }
    }

    arsort($recommendedBloodBanks); // Sort by highest weighted scores
    return array_keys($recommendedBloodBanks); // Return blood bank IDs
}

// Step 1: Get logged-in user's past interactions
$userInteractions = getUserInteractions($userId, $con);

// Step 2: Find similar users based on common interactions using rating similarity
$similarUsers = getSimilarUsers($userId, $userInteractions, $con);

// Step 3: Recommend blood banks based on similar users' preferences
$recommendedBloodBanks = recommendBloodBanks($userId, $similarUsers, $userInteractions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Blood Banks</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <h2 class="text-4xl font-serif mb-12 text-red-600">Recommended Blood Banks</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
        <?php 
        // Check if recommendations are empty
        if (count($recommendedBloodBanks) > 0) {
            foreach ($recommendedBloodBanks as $bloodBankId) {
                // Fetch blood bank details by ID
                $bloodBankStmt = $con->prepare("SELECT fullname, address FROM users WHERE id = ?");
                $bloodBankStmt->bind_param("i", $bloodBankId);
                $bloodBankStmt->execute();
                $bloodBankResult = $bloodBankStmt->get_result()->fetch_assoc();
        
                // Get average rating for the blood bank
                $avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
                $avgRatingStmt->bind_param("i", $bloodBankId);
                $avgRatingStmt->execute();
                $avgResult = $avgRatingStmt->get_result()->fetch_assoc();
                $averageRating = $avgResult['average_rating'] ? round($avgResult['average_rating'], 1) : 'No ratings yet';
        ?>
                <div class="bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <img src="../img/slide1.png" alt="Blood Bank Image" class="w-full h-50 object-cover p-5">
                    <div class="bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg" style="min-height: 230px;">
                        <h3 class="text-xl font-semibold font-serif text-gray-900"><?php echo htmlspecialchars($bloodBankResult['fullname']); ?></h3>
                        <p class="text-gray-600 mt-2">
                            <i class="fa-solid fa-map-marker-alt"></i>
                            <?php
                            $address = htmlspecialchars($bloodBankResult['address']);
                            $words = explode(' ', $address);
                            $firstThreeWords = implode(' ', array_slice($words, 0, 4));
                            echo $firstThreeWords;
                            ?>
                        </p>
                        <h3 class="mt-2">
                            <?php
                            if ($averageRating === 'No ratings yet') {
                                echo htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8');
                            } else {
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $averageRating ? 
                                        '<span class="text-2xl text-yellow-500">&#9733;</span>' : 
                                        '<span class="text-2xl">&#9734;</span>'; 
                                }
                                echo ' (' . htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8') . ')'; // Display numeric rating
                            }
                            ?>
                        </h3>
                        <a href="request.php?id=<?php echo htmlspecialchars($bloodBankId); ?>" 
                           class="mt-4 inline-block bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            View Blood Bank
                        </a>
                    </div>
                </div>
        <?php 
            }
        } else {
            echo "No new blood banks to recommend.";
        }
        ?>
    </div>
</body>
</html>
