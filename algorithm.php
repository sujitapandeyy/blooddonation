<?php

require('connection.php');
session_start();

// Define constants
const NUM_SIMILAR_USERS = 10;
const NUM_RECOMMENDATIONS = 5;

// Function to execute a database query
function executeQuery($con, $query) {
    $result = mysqli_query($con, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($con));
    }
    return $result;
}

// Function to get all ratings
function get_all_ratings($con): array {
    $query = "SELECT * FROM blood_bank_ratings";
    $result = executeQuery($con, $query);
    $ratings = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $ratings[] = $row;
    }
    return $ratings;
}

// Function to get blood banks rated by a user
function get_blood_banks_rated_by_user(int $user_id, $con): array {
    $query = "SELECT blood_bank_id FROM blood_bank_ratings WHERE user_id = $user_id";
    $result = executeQuery($con, $query);
    $blood_banks = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $blood_banks[] = $row['blood_bank_id'];
    }
    return $blood_banks;
}

// Function to get rating for a blood bank by a user
function get_rating_for_blood_bank(int $user_id, int $blood_bank_id, $con): float {
    $query = "SELECT rating FROM blood_bank_ratings WHERE user_id = $user_id AND blood_bank_id = $blood_bank_id";
    $result = executeQuery($con, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['rating'] ?? 0;
}

// Function to calculate user similarity
function calculate_user_similarity(int $user1, int $user2, array $ratings): float {
    $user1_ratings = array();
    $user2_ratings = array();

    foreach ($ratings as $rating) {
        if ($rating['user_id'] == $user1) {
            $user1_ratings[$rating['blood_bank_id']] = $rating['rating'];
        }
        if ($rating['user_id'] == $user2) {
            $user2_ratings[$rating['blood_bank_id']] = $rating['rating'];
        }
    }

    $dot_product = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;

    foreach ($user1_ratings as $blood_bank_id => $rating) {
        if (isset($user2_ratings[$blood_bank_id])) {
            $dot_product += $rating * $user2_ratings[$blood_bank_id];
        }
        $magnitude1 += pow($rating, 2);
    }

    foreach ($user2_ratings as $rating) {
        $magnitude2 += pow($rating, 2);
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    return ($magnitude1 && $magnitude2) ? $dot_product / ($magnitude1 * $magnitude2) : 0;
}

// Function to get similar users
function get_similar_users(int $target_user_id, array $ratings, int $num_similar_users): array {
    $similar_users = array();

    foreach ($ratings as $rating) {
        if ($rating['user_id'] != $target_user_id) {
            $similarity = calculate_user_similarity($target_user_id, $rating['user_id'], $ratings);
            $similar_users[$rating['user_id']] = $similarity;
        }
    }

    arsort($similar_users);
    return array_slice($similar_users, 0, $num_similar_users, true);
}

// Function to calculate distance between two locations
function calculate_distance(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earth_radius = 6371; // Earth radius in kilometers
    $dlat = deg2rad($lat2 - $lat1);
    $dlon = deg2rad($lon2 - $lon1);
    $a = sin($dlat / 2) * sin($dlat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c; // Distance in kilometers
}

// Function to get recommendations
function get_recommendations(int $target_user_id, int $num_recommendations, $con): array {
    // Get ratings for all users
    $ratings = get_all_ratings($con);

    // Get similar users
 $similar_users = get_similar_users($target_user_id, $ratings, NUM_SIMILAR_USERS);

    // Get blood banks rated by similar users
    $blood_banks = array();
    foreach ($similar_users as $similar_user_id => $similarity) {
        $blood_banks = array_merge($blood_banks, get_blood_banks_rated_by_user($similar_user_id, $con));
    }
    $blood_banks = array_unique($blood_banks); // Remove duplicates

    // Get target user's location
    $query = "SELECT latitude, longitude FROM users WHERE id = $target_user_id";
    $result = executeQuery($con, $query);
    $target_user_location = mysqli_fetch_assoc($result);

    // Calculate distance between target user and blood banks
    $distances = array();
    foreach ($blood_banks as $blood_bank_id) {
        $query = "SELECT latitude, longitude FROM users WHERE id = $blood_bank_id"; // Assuming blood bank ID corresponds to user ID
        $result = executeQuery($con, $query);
        $blood_bank_location = mysqli_fetch_assoc($result);

        if ($blood_bank_location) {
            $distance = calculate_distance(
                $target_user_location['latitude'],
                $target_user_location['longitude'],
                $blood_bank_location['latitude'],
                $blood_bank_location['longitude']
            );
            $distances[$blood_bank_id] = $distance;
        }
    }

    // Calculate weighted rating for each blood bank
    $weighted_ratings = array();
    foreach ($blood_banks as $blood_bank_id) {
        $weighted_rating = 0;
        foreach ($similar_users as $similar_user_id => $similarity) {
            $rating = get_rating_for_blood_bank($similar_user_id, $blood_bank_id, $con);
            if ($rating) {
                $weighted_rating += $rating * $similarity;
            }
        }
        $weighted_ratings[$blood_bank_id] = $weighted_rating;
    }

    // Sort blood banks by weighted rating
    $recommended_blood_banks = array();
    foreach ($weighted_ratings as $blood_bank_id => $weighted_rating) {
        $recommended_blood_banks[] = array(
            'blood_bank_id' => $blood_bank_id,
            'weighted_rating' => $weighted_rating,
            'distance' => $distances[$blood_bank_id] ?? null // Add distance if exists
        );
    }
    usort($recommended_blood_banks, function($a, $b) {
        return $b['weighted_rating'] - $a['weighted_rating'];
    });

    return array_slice($recommended_blood_banks, 0, $num_recommendations);
}

// Example usage
if (isset($_SESSION['Uloggedin'])) {
    if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] === true): 
        $email = $_SESSION['useremail'];
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];
        }
    endif;
    $num_recommendations = NUM_RECOMMENDATIONS;
    $recommendations = get_recommendations($user_id, $num_recommendations, $con);

    echo "Recommended blood banks for user $user_id:<br>";
    echo "Similar users:<br>";
    foreach ($similar_users as $similar_user_id => $similarity) {
            echo "User $similar_user_id (similarity: $similarity)<br>";
        }
        
        echo "<br>Recommendations:<br>";
        foreach ($recommendations as $recommendation) {
            echo "Blood bank ID: {$recommendation['blood_bank_id']} (weighted rating: {$recommendation['weighted_rating']}, distance: {$recommendation['distance']} km)<br>";
        }
    }?>