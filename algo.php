<?php
require('connection.php'); // Ensure this file establishes the connection properly
session_start(); // Ensure this is called once

if (isset($_SESSION['useremail'])) {
    // Get the user ID from session
    $user_id = $_SESSION['userid']; // Adjust according to your logic
    echo "Logged in User ID: $user_id<br>"; // Debugging line
} else {
    echo "User is not logged in.<br>";
    exit; // Stop execution if user is not logged in
}

// Collect search queries
$search_queries = array();
$sql = "SELECT * FROM search_queries WHERE user_id = '$user_id'"; // Filter by logged-in user
$result = mysqli_query($con, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $search_queries[] = $row;
    }
} else {
    echo "Error fetching search queries: " . mysqli_error($con) . "<br>";
}

// Collect donor information including latitude and longitude from users table
$donors = array();
$sql = "SELECT d.id, d.donor_blood_type, u.latitude, u.longitude 
        FROM donor d 
        JOIN users u ON d.id = u.id"; // Assuming donor table has user_id column
$result = mysqli_query($con, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $donors[] = $row;
    }
} else {
    echo "Error fetching donors: " . mysqli_error($con) . "<br>";
}

// Debugging output
echo "<h3>Search Queries:</h3><pre>";
print_r($user_id);
print_r($search_queries);
echo "</pre>";

echo "<h3>Donor Information:</h3><pre>";
print_r($donors);
echo "</pre>";

// Haversine formula to calculate distance between two points on a sphere (Earth)
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius_km = 6371;

    $dlat = deg2rad($lat2 - $lat1);
    $dlon = deg2rad($lon2 - $lon1);

    $a = sin($dlat / 2) * sin($dlat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance_km = $earth_radius_km * $c;

    return $distance_km;
}

// Function to get distance similarity score based on distance
function get_distance_similarity($distance_km) {
    if ($distance_km < 1) {
        return 1;
    } elseif ($distance_km < 2) {
        return 0.8;
    } elseif ($distance_km < 5) {
        return 0.6;
    } elseif ($distance_km < 10) {
        return 0.4;
    } elseif ($distance_km < 20) {
        return 0.2;
    } elseif ($distance_km < 40) {
        return 0.1;
    } else {
        return 0;
    }
}

// Content-Based Filtering with Distance and Blood Type Similarity
function content_based_filtering($user_id, $search_queries, $donors) {
    $recommended_donors = array();

    // Extract relevant features from search queries
    $search_features = array();
    foreach ($search_queries as $query) {
        if ($query['user_id'] == $user_id) {
            $search_features[] = array(
                'blood_group' => $query['bloodgroup'],
                'latitude' => $query['latitude'],
                'longitude' => $query['longitude'],
            );
        }
    }

    // Extract relevant features from donors
    foreach ($search_features as $search_feature) {
        foreach ($donors as $donor) {
            // Calculate distance using Haversine formula
            $distance_km = haversine_distance($search_feature['latitude'], $search_feature['longitude'], $donor['latitude'], $donor['longitude']);
            
            // Get distance similarity
            $distance_similarity = get_distance_similarity($distance_km);

            // Check blood type similarity
            $blood_type_similarity = ($donor['donor_blood_type'] == $search_feature['blood_group']) ? 1 : 0;

            // Calculate final similarity score
            $similarity_score = $distance_similarity * $blood_type_similarity;

            // Only add donor to recommendations if similarity is greater than 0
            if ($similarity_score > 0) {
                $recommended_donors[] = array(
                    'donor_id' => $donor['id'],
                    'distance_km' => $distance_km,
                    'similarity_score' => $similarity_score
                );
            }
        }
    }

    // Sort recommended donors by similarity score in descending order
    usort($recommended_donors, function ($a, $b) {
        return $b['similarity_score'] <=> $a['similarity_score'];
    });

    return $recommended_donors;
}

// Function to get similar users based on blood request history
function get_similar_users($user_id, $con) {
    $similar_users = array();

    // Fetch other users' blood requests
    $sql = "SELECT user_id, bloodgroup FROM blood_requests WHERE user_id != '$user_id'";
    $result = mysqli_query($con, $sql);
    
    if ($result) {
        $user_requests = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $user_requests[$row['user_id']][] = $row['bloodgroup'];
        }

        // Fetch current user's blood requests
        $sql_user = "SELECT bloodgroup FROM blood_requests WHERE user_id = '$user_id'";
        $result_user = mysqli_query($con, $sql_user);
        
        $current_user_requests = array();
        while ($row_user = mysqli_fetch_assoc($result_user)) {
            $current_user_requests[] = $row_user['bloodgroup'];
        }

        // Compare with other users
        foreach ($user_requests as $other_user_id => $bloodgroups) {
            $common_requests = array_intersect($current_user_requests, $bloodgroups);
            $total_requests = array_unique(array_merge($current_user_requests, $bloodgroups));

            // Calculate similarity (Jaccard Similarity as example)
            $similarity_score = count($common_requests) / count($total_requests);
            $similar_users[$other_user_id] = $similarity_score;
        }
    }

    // Sort by similarity score
    arsort($similar_users);

    return $similar_users;
}

// Function to get donor recommendations based on similar users' requests
function collaborative_filtering($user_id, $con) {
    $recommended_donors = array();

    // Get similar users
    $similar_users = get_similar_users($user_id, $con);

    if (!empty($similar_users)) {
        foreach ($similar_users as $similar_user_id => $similarity_score) {
            // Fetch blood requests made by similar users
            $sql = "SELECT donor_id FROM blood_requests WHERE user_id = '$similar_user_id'";
            $result = mysqli_query($con, $sql);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $recommended_donors[$row['donor_id']] = $similarity_score;
                }
            }
        }
    }

    // Sort donors by similarity score
    arsort($recommended_donors);

    return $recommended_donors;
}

// Fetch collaborative filtering recommendations
$collaborative_donors = collaborative_filtering($user_id, $con);

// Fetch content-based filtering recommendations
$content_based_donors = content_based_filtering($user_id, $search_queries, $donors);

// Combine recommendations from both algorithms
$final_recommendations = array();

foreach ($collaborative_donors as $donor_id => $collab_score) {
    $content_score = isset($content_based_donors[$donor_id]) ? $content_based_donors[$donor_id]['similarity_score'] : 0;
    
    // Weighted score (e.g., 50% collaborative, 50% content-based)
    $final_score = 0.5 * $collab_score + 0.5 * $content_score;
    
    $final_recommendations[$donor_id] = $final_score;
}

// Sort by final score
arsort($final_recommendations);

// Output the recommendations
echo "Final Recommended Donors for User: $user_id<br>";
if (empty($final_recommendations)) {
    echo "No recommended donors found.<br>";
} else {
    foreach ($final_recommendations as $donor_id => $final_score) {
        echo "Donor ID: " . $donor_id . " | Final Score: " . $final_score . "<br>";
    }
}

// Close the database connection
if ($con) {
    mysqli_close($con);
}
?>
