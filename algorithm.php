<?php
require('connection.php');
session_start(); // Ensure this is called once

// Get selected blood bank details
$bloodBankId = intval($_GET['id']);
$selectedBankQuery = $con->prepare("SELECT u.id, u.fullname, u.latitude, u.longitude 
                                     FROM users AS u 
                                     WHERE u.id = ?");
$selectedBankQuery->bind_param('i', $bloodBankId);
$selectedBankQuery->execute();
$selectedBank = $selectedBankQuery->get_result()->fetch_assoc();

// Initialize available blood types if not set
$selectedBank['available_blood_types'] = [];

// Fetch available blood types for the selected blood bank
$bloodGroupQuery = $con->prepare("SELECT bloodgroup FROM blood_details WHERE bloodbank_id = ?");
$bloodGroupQuery->bind_param('i', $bloodBankId);
$bloodGroupQuery->execute();
$bloodGroupResult = $bloodGroupQuery->get_result();
while ($row = $bloodGroupResult->fetch_assoc()) {
    $selectedBank['available_blood_types'][] = $row['bloodgroup'];
}

// Fetch service hours for the selected blood bank
$serviceHoursQuery = $con->prepare("SELECT service_start_time, service_end_time FROM bloodbank WHERE id = ?");
$serviceHoursQuery->bind_param('i', $bloodBankId);
$serviceHoursQuery->execute();
$serviceHoursResult = $serviceHoursQuery->get_result();
$serviceHours = $serviceHoursResult->fetch_assoc();

if ($serviceHours) {
    $selectedBank['service_start_time'] = $serviceHours['service_start_time'];
    $selectedBank['service_end_time'] = $serviceHours['service_end_time'];
} else {
    $selectedBank['service_start_time'] = null;
    $selectedBank['service_end_time'] = null;
}

// Fetch all blood banks for comparison
$allBanksQuery = $con->prepare("SELECT u.id, u.fullname, u.latitude, u.longitude 
                                  FROM users AS u 
                                  WHERE u.user_type = 'BloodBank' AND u.id != ?");
$allBanksQuery->bind_param('i', $bloodBankId);
$allBanksQuery->execute();
$allBanksResult = $allBanksQuery->get_result();

$similarBanks = [];
$nonRecommendedBanks = [];

// Loop through all banks to calculate similarity
while ($bank = $allBanksResult->fetch_assoc()) {
    // Initialize the available blood types
    $bank['available_blood_types'] = [];

    // Fetch available blood types for this bank
    $bloodGroupQuery = $con->prepare("SELECT bloodgroup FROM blood_details WHERE bloodbank_id = ?");
    $bloodGroupQuery->bind_param('i', $bank['id']);
    $bloodGroupQuery->execute();
    $bloodGroupResult = $bloodGroupQuery->get_result();
    while ($row = $bloodGroupResult->fetch_assoc()) {
        $bank['available_blood_types'][] = $row['bloodgroup'];
    }

    // Fetch service hours for the current bank
    $serviceHoursQuery = $con->prepare("SELECT service_start_time, service_end_time FROM bloodbank WHERE id = ?");
    $serviceHoursQuery->bind_param('i', $bank['id']);
    $serviceHoursQuery->execute();
    $serviceHoursResult = $serviceHoursQuery->get_result();
    $serviceHours = $serviceHoursResult->fetch_assoc();

    if ($serviceHours) {
        $bank['service_start_time'] = $serviceHours['service_start_time'];
        $bank['service_end_time'] = $serviceHours['service_end_time'];
    } else {
        $bank['service_start_time'] = null;
        $bank['service_end_time'] = null;
    }

    // Calculate distance
    $distance = calculateDistance($selectedBank['latitude'], $selectedBank['longitude'], $bank['latitude'], $bank['longitude']);
    echo "Distance between " . $selectedBank['fullname'] . " and " . $bank['fullname'] . ": " . $distance . " km<br>";

    // Check service hours overlap
    $serviceOverlap = checkServiceHoursOverlap($selectedBank['service_start_time'], $selectedBank['service_end_time'], $bank['service_start_time'], $bank['service_end_time']);
    $serviceOverlapScore = $serviceOverlap ? 0.9 : 0.2;

    // Calculate blood type match
    $bloodTypeMatch = array_intersect($selectedBank['available_blood_types'], $bank['available_blood_types']);
    $bloodTypeScore = !empty($bloodTypeMatch) ? 1 : 0; // 1 if at least one type matches

    // Calculate distance score based on defined criteria
    if ($distance <= 1) {
        $distanceScore = 1;
    } elseif ($distance <= 5) {
        $distanceScore = 0.6;
    } elseif ($distance <= 10) {
        $distanceScore = 0.4;
    } elseif ($distance <= 50) {
        $distanceScore = 0.2;
    } else {
        $distanceScore = 0;
    }

    // Define a similarity score
    $similarityScore = $serviceOverlapScore + $distanceScore + $bloodTypeScore;

    // Classify banks into similar and non-recommended based on similarity score
    if ($similarityScore > 0) {
        $similarBanks[] = [
            'id' => $bank['id'],
            'fullname' => $bank['fullname'],
            'similarity_score' => $similarityScore,
            'details' => [
                'Service Hours Overlap' => $serviceOverlapScore,
                'Distance' => $distanceScore,
                'Blood Type Match' => $bloodTypeScore,
            ],
        ];
    } else {
        // Add to non-recommended banks if similarity score is 0 or less
        $nonRecommendedBanks[] = [
            'id' => $bank['id'],
            'fullname' => $bank['fullname'],
            'similarity_score' => $similarityScore,
            'details' => [
                'Service Hours Overlap' => $serviceOverlapScore,
                'Distance' => $distanceScore,
                'Blood Type Match' => $bloodTypeScore,
            ],
        ];
    }
}

// Sort by similarity score
usort($similarBanks, function ($a, $b) {
    return $b['similarity_score'] <=> $a['similarity_score'];
});

// Keep only the top 3 similar banks
$topSimilarBanks = array_slice($similarBanks, 0, 3);

// Function to calculate distance (Haversine formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth radius in kilometers
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c; // Distance in kilometers
}

// Function to check service hours overlap
function checkServiceHoursOverlap($start1, $end1, $start2, $end2) {
    return max($start1, $start2) < min($end1, $end2);
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Details for <?= htmlspecialchars($selectedBank['fullname'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Blood Details for <?= htmlspecialchars($selectedBank['fullname'], ENT_QUOTES, 'UTF-8') ?></h1>
    <h2>Available Blood Types:</h2>
    <ul>
        <?php foreach ($selectedBank['available_blood_types'] as $bloodType): ?>
            <li><?= htmlspecialchars($bloodType, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
    
    <h2>Top Similar Blood Banks:</h2>
    <ul>
        <?php foreach ($topSimilarBanks as $similarBank): ?>
            <li>
                <?= htmlspecialchars($similarBank['fullname'], ENT_QUOTES, 'UTF-8') ?> 
                <br>
                Similarity Score: <?= round($similarBank['similarity_score'], 2) ?>
                <br>
                Details: <?= json_encode($similarBank['details']) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Non-Recommended Blood Banks:</h2>
    <ul>
        <?php foreach ($nonRecommendedBanks as $nonRecommendedBank): ?>
            <li>
                <?= htmlspecialchars($nonRecommendedBank['fullname'], ENT_QUOTES, 'UTF-8') ?> 
                <br>
                Similarity Score: <?= round($nonRecommendedBank['similarity_score'], 2) ?>
                <br>
                Details: <?= json_encode($nonRecommendedBank['details']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
