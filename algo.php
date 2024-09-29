<?php
require('connection.php');
session_start();

// Fetch logged-in user's information if available
$user = null;
if (isset($_SESSION['useremail'])) {
    $user_email = $_SESSION['useremail'];
    $user_stmt = $con->prepare("SELECT id, fullname, email, phone, address FROM users WHERE email = ?");
    $user_stmt->bind_param("s", $user_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['id'];  // Get the user's ID for further queries
    } else {
        echo "User not found.";
        exit();
    }
}

// Fetch user's request history from donorblood_request table
// Fetch user's request history from donorblood_request table
// Fetch user's request history from donorblood_request table and join with users table
// Fetch user's request history from donorblood_request table, join with users and donor tables
$request_history_stmt = $con->prepare("
    SELECT 
        dr.donor_email,
        dr.bloodgroup, 
        dr.requester_name, 
        dr.requester_email, 
        dr.requester_phone, 
        dr.donation_address, 
        dr.quantity, 
        dr.message, 
        dr.request_date, 
        dr.status, 
        dr.delivery_time,
        u.latitude,
        u.longitude,
        d.dob -- Fetching DOB from donor table
    FROM 
        donorblood_request dr
    JOIN 
        users u ON dr.requester_email = u.email
    JOIN 
        donor d ON dr.donor_email = d.donor_email -- Join with donor table
    WHERE 
        dr.requester_email = ?
    ORDER BY 
        dr.request_date DESC
");


$request_history_stmt->bind_param("s", $user_email);
$request_history_stmt->execute();
$request_history_result = $request_history_stmt->get_result();
$user_requests = [];
while ($row = $request_history_result->fetch_assoc()) {
    $user_requests[] = $row;
}

// Function to get donor recommendations based on user request history
function getDonorRecommendations($user_requests, $con) {
    $recommendations = [];
    
    foreach ($user_requests as $request) {
        // Fetch all donors
        $donorsQuery = "SELECT * FROM donor"; // Adjust table name as needed
        $donorsResult = $con->query($donorsQuery);
        
        while ($donor = $donorsResult->fetch_assoc()) {
            // Calculate similarity score for each donor
            $similarityScore = calculateSimilarity($request, $donor);
            
            // Store the donor and their score
            if ($similarityScore > 0) { // Only add if there's a similarity
                $recommendations[] = ['donor' => $donor, 'score' => $similarityScore, 'request_email' => $request['donor_email']];
            }
        }
    }

    // Sort recommendations by similarity score in descending order
    usort($recommendations, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    return $recommendations;
}

// Calculate similarity score between request and donor
function calculateSimilarity($request, $donor) {
    $score = 0;

    // Compare blood types
    if ($request['bloodgroup'] === $donor['bloodgroup']) {
        $score += 1; // Increase score for matching blood type
    }
    
    // Calculate age difference (assuming you have a 'dob' field in donor table)
    $donorAge = calculateAge($donor['dob']); // Ensure 'dob' exists in donor data
    $ageDifference = abs($donorAge - $request['age']); // Ensure 'age' exists in request data

    // Define a threshold for age similarity
    if ($ageDifference <= 5) { // Example: age difference of 5 years
        $score += 1; // Increase score if within threshold
    }
    
    // Calculate distance
    $distance = calculateDistance($request['latitude'], $request['longitude'], $donor['latitude'], $donor['longitude']);
    
    // Define distance thresholds (in kilometers)
    if ($distance <= 5) {
        $score += 1; // Increase score for close proximity
    } else if ($distance <= 10) {
        $score += 0.5; // Slightly lower score for further proximity
    }
    
    return $score;
}

// Calculate age from date of birth
function calculateAge($dob) {
    if (!$dob) return 0; // Handle cases where dob might not be set
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    return $today->diff($birthDate)->y; // Returns age in years
}

// Calculate distance using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // Distance in kilometers
}

// Main execution for recommendations
$recommendations = getDonorRecommendations($user_requests, $con);

// Output recommendations for debugging
if (empty($recommendations)) {
    echo "No recommendations found.";
} else {
    foreach ($recommendations as $recommendation) {
        echo "Recommended Donor: " . $recommendation['donor']['fullname'] . " - Score: " . $recommendation['score'] . "<br>";
    }
}
?>
