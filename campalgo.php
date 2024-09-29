<?php 
require('connection.php');
session_start(); // Make sure to start the session at the beginning

$loggedInUserEmail = $_SESSION['useremail'] ?? $_SESSION['donoremail'] ?? null;

if (!$loggedInUserEmail) {
    die("You must be logged in to view campaigns.");
}

// Fetch user ID from the email
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $loggedInUserEmail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$loggedInUserId = $user['id']; // Get the logged-in user's ID

// Fetch user-blood bank ratings for the logged-in user
$sql = "SELECT blood_bank_id, rating FROM blood_bank_ratings WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$userBloodbankRatings = [];
while ($row = $result->fetch_assoc()) {
    $userBloodbankRatings[$row['blood_bank_id']] = $row['rating'];
}

// Fetch campaigns with latitude and longitude
$sql = "SELECT id, campaign_name, campaign_date, bloodbank_id, latitude, longitude FROM campaigns WHERE campaign_date >= CURDATE()";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$campaigns = [];
while ($row = $result->fetch_assoc()) {
    $campaigns[$row['id']] = $row; // Store campaigns in an associative array with ID as key
}

// Fetch user location
$sql = "SELECT latitude, longitude FROM users WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$userLocation = $result->fetch_assoc();

if (!$userLocation) {
    die("Location not found for user ID: $loggedInUserId");
}

// Implement content-based filtering algorithm
$recommendedCampaigns = [];
if (!empty($userBloodbankRatings)) {
    $similarityScores = [];
    foreach ($campaigns as $campaign_id => $campaign) {
        $bloodbankId = $campaign['bloodbank_id'];

        // Check if user has rated the blood bank associated with the campaign
        if (isset($userBloodbankRatings[$bloodbankId]) && $userBloodbankRatings[$bloodbankId] > 2) { // Assuming 2 is the minimum rating to consider
            // Calculate cosine similarity with respect to a higher threshold (5 assumed as max rating)
            $similarityScore = cosineSimilarity([$userBloodbankRatings[$bloodbankId]], [1]); 

            // Calculate distance between user and campaign
            $distance = haversineDistance($userLocation['latitude'], $userLocation['longitude'], $campaign['latitude'], $campaign['longitude']);
            
            // If the campaign is within a certain distance (e.g., 50 km), factor in the distance
            if ($distance <= 50) {
                $similarityScores[$campaign_id] = $similarityScore * (1 - $distance / 100); // Adjust similarity by distance
            }
        }
    }

    if (!empty($similarityScores)) {
        arsort($similarityScores);
        $recommendedCampaigns = array_slice(array_keys($similarityScores), 0, 3); // Get top 3 recommended campaigns
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .bg-img {
            background-image: url('img/type.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
    </style>
</head>
<body class="bg-green-100">
<section class="bg-white-100 w-full my-10 bg-img">
    <h2 class="text-4xl font-serif text-center mb-12 text-red-600">Recommended Campaigns</h2>
    <div class="relative max-w-6xl mx-auto">
        <div class="grid grid-cols-4 gap-10 p-2">
            <?php if (!empty($recommendedCampaigns)): ?>
                <?php foreach ($recommendedCampaigns as $campaign_id): ?>
                    <?php 
                    $campaign = $campaigns[$campaign_id]; 
                    ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105 p-2">
                        <img src="../img/slide1.png" alt="Campaign Image" class="w-full h-40 object-cover rounded-lg">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($campaign['campaign_name']); ?></h2>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($campaign['campaign_date']); ?></p>
                            <a href="campaign_detail.php?id=<?php echo $campaign['id']; ?>" class="inline-block mt-2 bg-red-500 hover:bg-red-400 text-white py-1 px-3 rounded-lg shadow-lg transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                View More
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-red-500">No recommended campaigns available.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
</body>
</html>

<?php
// Cosine similarity function
function cosineSimilarity($vector1, $vector2) {
    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;
    $i = 0;
    foreach ($vector1 as $value) {
        $dotProduct += $value * $vector2[$i];
        $magnitude1 += $value * $value;
        $magnitude2 += $vector2[$i] * $vector2[$i];
        $i++;
    }
    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);
    
    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0;
    }
    
    return $dotProduct / ($magnitude1 * $magnitude2);
}

// Haversine distance function (in kilometers)
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius of the earth in kilometers
    $latDiff = deg2rad($lat2 - $lat1);
    $lonDiff = deg2rad($lon2 - $lon1);
    $a = sin($latDiff / 2) * sin($latDiff / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDiff / 2) * sin($lonDiff / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    return $distance;
}
?>
