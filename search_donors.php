<?php
require('connection.php');
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$bloodType = $input['bloodType'];
$latitude = $input['latitude'];
$longitude = $input['longitude'];

// Define the Haversine formula
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius of Earth in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

// Query to get donors with the specified blood type
$sql = "SELECT id, fullname, latitude, longitude, blood_type FROM users WHERE user_type = 'Donor' AND blood_type = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $bloodType);
$stmt->execute();
$result = $stmt->get_result();

$donors = [];

while ($row = $result->fetch_assoc()) {
    $distance = haversineDistance($latitude, $longitude, $row['latitude'], $row['longitude']);
    $row['distance'] = $distance;
    $donors[] = $row;
}

// Sort donors by distance
usort($donors, function($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

echo json_encode(['donors' => $donors]);
?>
