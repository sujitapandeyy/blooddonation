<?php
require('connection.php');
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $bloodGroup = $_POST['bloodgroup'];
    $userLat = $_POST['latitude'];
    $userLong = $_POST['longitude'];

    // Check if latitude and longitude are missing
    if (empty($userLat) || empty($userLong)) {
        die("Error: Latitude or Longitude is missing. Please try again.");
    }

    // SQL query to find donors within 50 km radius
    $sql = "
        SELECT u.id, u.fullname AS name, u.email, u.phone, u.address, d.gender, d.availability, d.donor_blood_type, d.profile_image, u.latitude, u.longitude,
        ( 6371 * acos( cos( radians(?) ) * cos( radians( u.latitude ) ) * cos( radians( u.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( u.latitude ) ) ) ) AS distance
    FROM users u
    JOIN donor d ON u.id = d.id
    WHERE u.user_type = 'Donor' 
    AND d.donor_blood_type = ? 
    AND d.availability = 'Available'
    HAVING distance < 50
    ORDER BY distance
    LIMIT 30
    ";

    // Prepare the SQL statement
    $stmt = $con->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($con->error));
    }

    // Bind parameters
    $stmt->bind_param("ddds", $userLat, $userLong, $userLat, $bloodGroup);

    // Execute the query
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    // Fetch the results
    $result = $stmt->get_result();
}

// Prepare default image path
$default_image_path = 'img/defaultimage.png';

// Include header
include("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous" defer></script>
    <link rel="icon" href="favIcon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="javascript/addressInput.js"></script>
    <title>Search Results</title>
</head>
<body class="bg-gray-100">
    <main class="pt-28 bg-gradient-to-r from-red-50 to-white">
        <div class="justify-center flex">
            <div class="mb-6 p-6 bg-white rounded-lg shadow-lg justify-center w-1/2">
                <h2 class="text-black text-center text-2xl font-bold mb-6 font-serif">Search Nearby Donor</h2>
                <form action="" method="POST">
                    <div class="flex flex-col space-y-4">
                        <select name="bloodgroup" id="donorBloodgroup" required
                            class="text-gray-800 px-4 py-3 border border-gray-300 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 ease-in-out"
                            aria-label="Blood Type">
                            <option value="" disabled selected>Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>

                        <div class="relative">
                            <input id="location"
                                class="w-full text-gray-800 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 ease-in-out"
                                type="text" placeholder="Enter Address" name="address" required aria-label="Address">
                            <div id="suggestions" class="suggestions absolute z-10 bg-white border border-gray-300 rounded-lg shadow-lg mt-1"></div>
                            <input type="hidden" id="userLat" name="latitude">
                            <input type="hidden" id="userLong" name="longitude">
                        </div>

                        <button type="submit"
                            class="flex items-center justify-center px-4 py-3 text-white bg-red-500 rounded-lg shadow-md hover:bg-red-400 transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i>
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <header class="bg-white rounded-r-full text-white p-4 mr-6 m-6">
            <h1 class="text-2xl text-red-500 font-bold text-center font-serif">Nearby Donors for Blood Group: <?php if (isset($bloodGroup)) echo htmlspecialchars($bloodGroup); ?></h1>
        </header>
        
        <?php if (isset($result) && $result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mx-5">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white shadow-md rounded-lg p-4 mb-4 border border-gray-200 text-center">
                        <div class="flex justify-center">
                            <div class="mb-4 text-center">
                                  <?php $profile_image = !empty($row['profile_image']) ? 'upload/' . htmlspecialchars($row['profile_image']) : $default_image_path ;?>
                                <img src="<?php echo $profile_image; ?>" alt="Profile Image" class="w-20 h-20 rounded-full mx-auto" onerror="this.onerror=null; this.src='<?php echo $default_image_path; ?>';">
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 text-center p-2">
    <?php echo htmlspecialchars($row['name']); ?> 
    <span class="text-balck">(<?php echo htmlspecialchars($row['donor_blood_type']); ?>)</span>
</h3>
                        <!-- <p class="text-gray-600 font-semibold">Blood Type: <span class="text-gray-600"><?php echo htmlspecialchars($row['donor_blood_type']); ?></span></p> -->
                        <p class="text-gray-600 font-semibold">Address: <span class="text-gray-600"><?php 
                            $address = htmlspecialchars($row['address']);
                            $words = explode(' ', $address); 
                            $firstThreeWords = implode(' ', array_slice($words, 0, 1)); 
                            echo $firstThreeWords;
                        ?></span></p>
                        <p class="text-gray-600 font-semibold">Email: <span class="text-gray-600"><?php echo htmlspecialchars($row['email']); ?></span></p>
                        <p class="text-gray-600 font-semibold">Phone: <span class="text-gray-600"><?php echo htmlspecialchars($row['phone']); ?></span></p>
                        <p class="text-gray-600 font-semibold">Gender: <span class="text-gray-600"><?php echo htmlspecialchars($row['gender']); ?></span></p>
                        <p class="text-gray-600 font-semibold">Availability: <span class="text-green-500"><?php echo htmlspecialchars($row['availability']); ?></span></p>
                        <p class="text-gray-600 font-semibold">Distance: <span class="text-blue-600"><?php echo round($row['distance'], 2); ?> km</span></p>
                        
                        <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true): ?>
                            <form action="request_donation.php" method="POST">
                                <input type="hidden" name="donor_id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                <button type="submit" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-300">Request Donation</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-600 text-xl mt-6">No donors found nearby.</p>
        <?php endif; ?>
    </main>
</body>
</html>

<script>
        $(document).ready(function () {
            $('#location').on('input', function () {
                var address = $(this).val().trim();
                if (address.length > 0) {
                    var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address) + "&countrycodes=NP";

                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function (data) {
                            $('#suggestions').empty();
                            if (data.length > 0) {
                                data.forEach(function (place) {
                                    $('#suggestions').append('<div class="suggestion" data-lat="' + place.lat + '" data-lon="' + place.lon + '">' + place.display_name + '</div>');
                                });
                            }
                        },
                        error: function (error) {
                            console.log('Error:', error);
                        }
                    });
                } else {
                    $('#suggestions').empty();
                }
            });

            $(document).on('click', '.suggestion', function () {
                var placeName = $(this).text();
                var lat = $(this).data('lat');
                var lon = $(this).data('lon');

                $('#location').val(placeName);
                $('#userLat').val(lat);
                $('#userLong').val(lon);
                $('#suggestions').empty();
            });

            $('#location').on('keypress', function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                    var firstSuggestion = $('#suggestions .suggestion').first();
                    if (firstSuggestion.length > 0) {
                        var placeName = firstSuggestion.text();
                        var lat = firstSuggestion.data('lat');
                        var lon = firstSuggestion.data('lon');

                        $('#location').val(placeName);
                        $('#userLat').val(lat);
                        $('#userLong').val(lon);
                        $('#suggestions').empty();
                    }
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#location').length) {
                    $('#suggestions').empty();
                }
            });
        });
    </script>

