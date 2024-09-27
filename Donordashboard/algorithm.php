<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['donoremail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

// Retrieve the logged-in donor's email
$loggedInEmail = $_SESSION['donoremail'];

// Prepare the query to get total donations
$query = "
    SELECT 
        u.id AS user_id,
        u.fullname AS donor_name,
        COUNT(bd.id) AS total_donations,
        MAX(bd.created_at) AS last_donation
    FROM 
        users u
    LEFT JOIN 
        blood_details bd ON u.id = bd.donor_id
    WHERE 
        u.email = ?
    GROUP BY 
        u.id, u.fullname
";

$stmt = $con->prepare($query);
if ($stmt === false) {
    die("MySQL prepare statement failed: " . htmlspecialchars($con->error));
}

$stmt->bind_param("s", $loggedInEmail);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalDonations = $row['total_donations'] ?? 0;
$lastDonation = $row['last_donation'] ?? null;

// Calculate donation frequency
function calculateDonationFrequency($totalDonations, $lastDonation) {
    if ($lastDonation) {
        $lastDonationDate = new DateTime($lastDonation);
        $today = new DateTime();
        $diff = $today->diff($lastDonationDate);
        $yearsSinceLastDonation = $diff->y + ($diff->m / 12);
        return $yearsSinceLastDonation > 0 ? $totalDonations / $yearsSinceLastDonation : $totalDonations;
    }
    return 0; // If no donations, return 0
}

$donationFrequency = calculateDonationFrequency($totalDonations, $lastDonation);

// Calculate age from date of birth
function calculateAge($dob) {
    return (new DateTime())->diff(new DateTime($dob))->y;
}

// Calculate distance using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a))); // Distance in km
}

// Fetch blood banks visited by a donor
function fetchBloodBanksByDonorEmail($con, $donorEmail) {
    $query = "
        SELECT b.*, u.fullname, u.email, u.phone, u.address, u.latitude, u.longitude
        FROM blood_details bd 
        JOIN bloodbank b ON bd.bloodbank_id = b.id 
        JOIN users u ON b.id = u.id 
        WHERE bd.donor_email = ?";
    
    $stmt = $con->prepare($query);
    if ($stmt === false) {
        return [];
    }
    
    $stmt->bind_param("s", $donorEmail);
    $stmt->execute();
    $bbResult = $stmt->get_result();

    return $bbResult->fetch_all(MYSQLI_ASSOC);
}

// Get recommended blood banks based on collaborative filtering
function getRecommendedBloodBanks($con, $donationFrequency) {
    $donorEmail = $_SESSION['donoremail'];
    $donorQuery = "
        SELECT bd.bloodgroup, d.gender, d.dob, u.latitude, u.longitude 
        FROM blood_details bd 
        JOIN donor d ON bd.donor_id = d.id 
        JOIN users u ON d.id = u.id 
        WHERE bd.donor_email = ?";
    
    $stmt = $con->prepare($donorQuery);
    $stmt->bind_param("s", $donorEmail);
    $stmt->execute();
    $donor = $stmt->get_result()->fetch_assoc();

    if (!$donor) {
        return [
            'recommended_blood_banks' => [],
            'not_recommended_blood_banks' => [],
            'reasons' => ['Donor not found.']
        ];
    }

    $recommendedBloodBanks = [];
    $notRecommendedBloodBanks = [];
    $donorAge = calculateAge($donor['dob']);
    $donorGender = $donor['gender'];
    $donorBloodGroup = $donor['bloodgroup'];
    $donorLat = $donor['latitude'];
    $donorLon = $donor['longitude'];

    $similarDonorQuery = "
        SELECT u.email, bd.bloodgroup, d.gender, d.dob, u.latitude, u.longitude, 
               COUNT(bd.id) AS total_donations,
               MAX(bd.created_at) AS last_donation
        FROM blood_details bd 
        JOIN donor d ON bd.donor_id = d.id 
        JOIN users u ON bd.donor_email = u.email
        WHERE bd.donor_email != ?
        GROUP BY u.email, bd.bloodgroup, d.gender, d.dob, u.latitude, u.longitude
    ";
    
    $similarStmt = $con->prepare($similarDonorQuery);
    $similarStmt->bind_param("s", $donorEmail);
    $similarStmt->execute();
    $similarResult = $similarStmt->get_result();

    while ($similarDonor = $similarResult->fetch_assoc()) {
        $ageDiff = abs($donorAge - calculateAge($similarDonor['dob']));
        $distance = calculateDistance($donorLat, $donorLon, $similarDonor['latitude'], $similarDonor['longitude']);

        // Calculate donation frequency for the similar donor
        $similarDonationFrequency = calculateDonationFrequency($similarDonor['total_donations'] ?? 0, $similarDonor['last_donation'] ?? null);

        // Calculate similarity score
        $similarityScore = calculateSimilarityScore($donorBloodGroup, $similarDonor['bloodgroup'], $donorGender, $similarDonor['gender'], $ageDiff, $distance, $donationFrequency, $similarDonationFrequency);

        // Check if the similar donor is similar enough
        if ($similarityScore > 0.5) {
            $bloodBanks = fetchBloodBanksByDonorEmail($con, $similarDonor['email']);
            foreach ($bloodBanks as $bloodBank) {
                $recommendedBloodBanks[$bloodBank['id']] = [
                    'bloodbank' => $bloodBank,
                    'matched_donor' => [
                        'email' => $similarDonor['email'],
                        'bloodgroup' => $similarDonor['bloodgroup'],
                        'gender' => $similarDonor['gender'],
                        'age' => calculateAge($similarDonor['dob']),
                        'latitude' => $similarDonor['latitude'],
                        'longitude' => $similarDonor['longitude'],
                    ],
                    'recommendation_reasons' => [
                        "Blood group matches.",
                        "Age difference is within 5 years.",
                        "Gender matches.",
                        "Distance is within 50 km.",
                        "Donation frequency is similar."
                    ]
                ];
            }
        } else {
            $reasons = [];
            if ($similarDonor['bloodgroup'] !== $donorBloodGroup) $reasons[] = "Blood group mismatch.";
            if ($ageDiff > 5) $reasons[] = "Age difference greater than 5 years.";
            if ($similarDonor['gender'] !== $donorGender) $reasons[] = "Gender mismatch.";
            if ($distance > 50) $reasons[] = "Distance greater than 50 km.";
            if (abs($similarDonationFrequency - $donationFrequency) > 1) $reasons[] = "Donation frequency significantly different.";

            foreach (fetchBloodBanksByDonorEmail($con, $similarDonor['email']) as $bloodBank) {
                $notRecommendedBloodBanks[] = [
                    'bloodbank_name' => $bloodBank['fullname'],
                    'reason' => implode(", ", $reasons),
                    'similarity_details' => [
                        'bloodgroup' => $similarDonor['bloodgroup'] === $donorBloodGroup,
                        'age' => $ageDiff <= 5,
                        'gender' => $similarDonor['gender'] === $donorGender,
                        'distance' => $distance <= 50,
                        'frequency' => abs($similarDonationFrequency - $donationFrequency) <= 1,
                        'age_diff' => $ageDiff,
                        'distance_value' => $distance,
                        'frequency_value' => $similarDonationFrequency,
                    ]
                ];
            }
        }
    }

    return [
        'recommended_blood_banks' => array_values($recommendedBloodBanks),
        'not_recommended_blood_banks' => $notRecommendedBloodBanks,
    ];
}

function calculateSimilarityScore($donorBloodGroup, $similarDonorBloodGroup, $donorGender, $similarDonorGender, $ageDiff, $distance, $donationFrequency, $similarDonationFrequency) {
    // Weight values can be adjusted based on significance
    $bloodGroupScore = ($donorBloodGroup === $similarDonorBloodGroup) ? 1 : 0;
    $genderScore = ($donorGender === $similarDonorGender) ? 1 : 0;
    $ageScore = ($ageDiff <= 5) ? 1 - ($ageDiff / 10) : 0; // Penalty for age difference
    $distanceScore = ($distance <= 50) ? 1 - ($distance / 100) : 0; // Penalty for distance
    $frequencyScore = (abs($donationFrequency - $similarDonationFrequency) <= 1) ? 1 : 0; // Frequency matching

    // Combine scores (weights can be adjusted)
    return 0.4 * $bloodGroupScore + 0.2 * $genderScore + 0.2 * $ageScore + 0.1 * $distanceScore + 0.1 * $frequencyScore;
}

// Fetch the recommendations
$recommendations = getRecommendedBloodBanks($con, $donationFrequency);

// Display the recommendations
// echo '<h2>Recommended Blood Banks</h2>';
// if (!empty($recommendations['recommended_blood_banks'])) {
//     foreach ($recommendations['recommended_blood_banks'] as $bloodBank) {
//         $reasons = implode(", ", $bloodBank['recommendation_reasons']);
//         echo '<p>' . htmlspecialchars($bloodBank['bloodbank']['fullname']) . ' - Reasons: ' . htmlspecialchars($reasons) . '</p>';
   
//     }
// } else {
//     echo '<p>No recommended blood banks found.</p>';
// }

// echo '<h2>Not Recommended Blood Banks</h2>';
// if (!empty($recommendations['not_recommended_blood_banks'])) {
//     foreach ($recommendations['not_recommended_blood_banks'] as $bloodBank) {
//         echo '<p>' . htmlspecialchars($bloodBank['bloodbank_name']) . ' - Reason: ' . htmlspecialchars($bloodBank['reason']) . '</p>';
//     }
// } else {
//     echo '<p>All blood banks recommended!</p>';
// }

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
    <!-- Recommended Blood Banks Section -->
    <h2 class="text-4xl font-serif ml-52 mb-12 text-red-600">Recommended Blood Banks</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">

        <?php 
        // Check if recommendations are empty
        if (!empty($recommendations['recommended_blood_banks'])) {
            foreach ($recommendations['recommended_blood_banks'] as $bloodBankData) {
                $bloodBank = $bloodBankData['bloodbank'];
                $bloodBankId = $bloodBank['id']; // Assuming blood bank ID is available
                $avgRatingStmt = $con->prepare("SELECT AVG(rating) as average_rating FROM blood_bank_ratings WHERE blood_bank_id = ?");
                $avgRatingStmt->bind_param("i", $bloodBankId);
                $avgRatingStmt->execute();
                $avgResult = $avgRatingStmt->get_result()->fetch_assoc();
                $averageRating = $avgResult['average_rating'] ? round($avgResult['average_rating'], 1) : 'No ratings yet';
        ?>
                <div class="relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <img src="../img/slide1.png" alt="Blood Bank Image" class="w-full h-50 object-cover p-5">
                    <div class="bg-white py-4 mx-2 my-2 px-6 shadow-lg rounded-t-lg" style="min-height: 230px;">
                        <h3 class="text-xl font-semibold font-serif text-gray-900"><?php echo htmlspecialchars($bloodBank['fullname']); ?></h3>
                        <p class="text-gray-600 mt-2">
                            <i class="fa-solid fa-map-marker-alt"></i>
                            <?php
                            $address = htmlspecialchars($bloodBank['address']);
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
                                        '<span class="text-2xl">&#9734;</span>'; // Empty star
                                }
                                echo ' (' . htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8') . ')'; // Display numeric rating
                            }
                            ?>
                        </h3>
                        <a href="request.php?id=<?php echo htmlspecialchars($bloodBank['id']); ?>" 
                           class="mt-4 inline-block bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            View Blood Bank
                        </a>
                    </div>
                </div>
        <?php 
            }
        } else {
            echo '<p class="text-gray-600">No recommended blood banks found.</p>';
        }
        ?>
    </div>
</body>
</html>
