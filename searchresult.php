<? function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth's radius in km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earth_radius * $c; // Distance in km
}

// Check if the form is submitted and retrieve user input
if (isset($_GET['blood_type']) && isset($_GET['latitude']) && isset($_GET['longitude'])) {
    $blood_type = $_GET['blood_type'];
    $latitude = $_GET['latitude'];
    $longitude = $_GET['longitude'];

    // Number of nearest neighbors to find
    $k = 5;

    // Query to find donors of the specified blood type
    $query = "SELECT fullname, email, phone, address, donor_blood_type, latitude, longitude FROM users WHERE user_type = 'Donor' AND donor_blood_type = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('s', $blood_type);
    $stmt->execute();
    $result = $stmt->get_result();

    // Array to hold donors and their distances
    $donors_with_distance = [];

    // Calculate distance to each donor
    while ($row = $result->fetch_assoc()) {
        $distance = haversine_distance($latitude, $longitude, $row['latitude'], $row['longitude']);
        $row['distance'] = $distance;
        $donors_with_distance[] = $row;
    }

    // Sort donors by distance and get the top k donors
    usort($donors_with_distance, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });

    $nearest_donors = array_slice($donors_with_distance, 0, $k);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Nearby Donor</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
    <!-- Add your JavaScript for address suggestions here -->
    <script src="addressinput.js"></script>
</head>
<body>
    <div class="container mx-auto px-4 content">
        <div class="rounded-lg w-full custom-carousel-height flex items-center justify-center">
            <div class="text-white text-center p-4 mb-28 bg-black bg-opacity-50 rounded-lg">
                <h1 class="text-3xl font-bold mb-7">We are here to save life</h1>
                <h2 class="text-xl font-bold mb-4">Search Nearby Donor</h2>
                <form action="searchresult.php" method="GET" class="flex flex-col md:flex-row items-center justify-center">
                    <input type="text" name="blood_type" placeholder="Blood Type" required class="w-full md:w-60 text-gray-800 px-4 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Blood Type">
                    
                    <!-- Address input field with suggestions and lat-long capture -->
                    <div class="relative w-full md:w-60 md:mr-2">
                        <i class="fa-solid fa-home absolute left-0 top-1/2 transform -translate-y-1/2 pl-4 text-xl"></i>
                        <input type="text" placeholder="Enter Address" name="address" id="userAddress" required class="pl-12 py-3 w-full border-b-2 bg-transparent border-gray-400 focus:border-red-600 outline-none input-field">
                        <div id="userSuggestions" class="suggestions"></div>
                        <input type="hidden" id="userLat" name="latitude">
                        <input type="hidden" id="userLong" name="longitude">
                        <div>
                            <p id="displayUserLat"></p>
                            <p id="displayUserLong"></p>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full md:w-30 px-4 py-2 text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-blue-600 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Search Results</h1>
        <?php if (!empty($nearest_donors)): ?>
            <ul>
                <?php foreach ($nearest_donors as $donor): ?>
                    <li>
                        <strong>Name:</strong> <?php echo htmlspecialchars($donor['fullname']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($donor['email']); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($donor['phone']); ?><br>
                        <strong>Address:</strong> <?php echo htmlspecialchars($donor['address']); ?><br>
                        <strong>Distance:</strong> <?php echo round($donor['distance'], 2); ?> km<br>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No donors found nearby.</p>
        <?php endif; ?>
    </div>

    <script>
        // Example JavaScript to update latitude and longitude fields
        function updateLatLong(lat, long) {
            document.getElementById('userLat').value = lat;
            document.getElementById('userLong').value = long;
            document.getElementById('displayUserLat').innerText = "Latitude: " + lat;
            document.getElementById('displayUserLong').innerText = "Longitude: " + long;
        }

        // Dummy function for address suggestions and updating coordinates
        // Implement your own logic here
        document.getElementById('userAddress').addEventListener('input', function () {
            // Simulate an address input and update coordinates
            updateLatLong(40.7128, -74.0060); // Example coordinates for New York City
        });
    </script>
</body>
</html>