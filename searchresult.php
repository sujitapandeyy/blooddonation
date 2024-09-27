<?php
require('connection.php');
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $bloodGroup = $_POST['bloodgroup'];
    $userLat = $_POST['latitude'];
    $userLong = $_POST['longitude'];

    if (empty($userLat) || empty($userLong)) {
        die("Error: Latitude or Longitude is missing. Please try again.");
    }

    $sql = "SELECT * FROM users JOIN donor ON users.id = donor.id WHERE users.user_type = 'Donor'";
    $result = $con->query($sql);

    $donors = array();
    while ($row = $result->fetch_assoc()) {
        $donors[] = $row;
    }

    // Function to check donor availability
    function checkDonorAvailability($donorId, $con) {
        $statusql = "SELECT d.last_donation_date FROM donor d WHERE d.id = ?";
        $stmt = $con->prepare($statusql);
        $stmt->bind_param("i", $donorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $donorData = $result->fetch_assoc();

        $lastDonationDate = $donorData['last_donation_date'];
        $availabilityStatus = "Available";
        $daysUntilEligible = 0;

        if ($lastDonationDate) {
            $currentDate = new DateTime();
            $lastDonationDateObj = new DateTime($lastDonationDate);
            $interval = $currentDate->diff($lastDonationDateObj);
            $daysSinceLastDonation = $interval->days;

            if ($daysSinceLastDonation < 56) {
                $availabilityStatus = "Not Available";
                $daysUntilEligible = 56 - $daysSinceLastDonation;
            }
        }

        return [$availabilityStatus, $daysUntilEligible];
    }

    // Function to perform linear search
    function linearSearch($bloodGroup, $userLat, $userLong, $donors, $con)
    {
        $matchingDonors = array();
        foreach ($donors as $donor) {
            if ($donor['donor_blood_type'] == $bloodGroup) {
                list($availabilityStatus, $daysUntilEligible) = checkDonorAvailability($donor['id'], $con);

                if ($availabilityStatus == "Available") {
                    // Calculate distance between user location and donor location
                    $distance = calculateDistance($userLat, $userLong, $donor['latitude'], $donor['longitude']);

                    if ($distance < 50) { 
                        $donor['distance'] = $distance;
                        $matchingDonors[] = $donor;
                    }
                }
            }
        }

        // Sort the matching donors by distance
        usort($matchingDonors, function ($a, $b) {
            return $a['distance'] - $b['distance'];
        });

        return $matchingDonors;
    }

    // Calculate distance between two points on the earth's surface
    function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        return $distance;
    }

    // Perform the linear search
    $matchingDonors = linearSearch($bloodGroup, $userLat, $userLong, $donors, $con);
    $matchingDonors = array_slice($matchingDonors, 0, 30);
}

// Prepare default image path
$default_image_path = 'img/defaultimage.png';

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
                            <div id="suggestions"
                                class="suggestions absolute z-10 bg-white border border-gray-300 rounded-lg shadow-lg mt-1">
                            </div>
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
            <h1 class="text-2xl text-red-500 font-bold text-center font-serif">Nearby Donors for Blood Group:
                <?php if (isset($bloodGroup))
                    echo htmlspecialchars($bloodGroup); ?></h1>
        </header>

        <?php if (!empty($matchingDonors)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mx-5">
        <?php foreach ($matchingDonors as $donor): ?>
            <div class="bg-white shadow-md rounded-lg p-4 mb-4 border border-gray-200 text-center">
                <div class="flex justify-center">
                    <div class="mb-4 text-center">
                        <?php $profile_image = !empty($donor['profile_image']) ? 'upload/' . htmlspecialchars($donor['profile_image']) : $default_image_path ;?>
                        <img src="<?php echo $profile_image; ?>" alt="Profile Image" class="w-20 h-20 rounded-full mx-auto" onerror="this.onerror=null; this.src='<?php echo $default_image_path; ?>';">
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 text-center p-2">
                    <?php echo htmlspecialchars($donor['fullname']); ?> 
                    <span class="text-black">(<?php echo htmlspecialchars($donor['donor_blood_type']); ?>)</span>
                </h3>
                <p class="text-gray-600 font-semibold">Address: <span class="text-gray-600"><?php 
                    $address = htmlspecialchars($donor['address']);
                    $words = explode(' ', $address); 
                    $firstThreeWords = implode(' ', array_slice($words, 0, 1)); 
                    echo $firstThreeWords;
                ?></span></p>
                <p class="text-gray-600 font-semibold">Email: <span class="text-gray-600"><?php echo htmlspecialchars($donor['email']); ?></span></p>
                <p class="text-gray-600 font-semibold">Phone: <span class="text-gray-600"><?php echo htmlspecialchars($donor['phone']); ?></span></p>
                <p class="text-gray-600 font-semibold">Gender: <span class="text-gray-600"><?php echo htmlspecialchars($donor['gender']); ?></span></p>
                <!-- <p class="text-gray-600 font-semibold">Availability: <span class="text-green-500"><?php echo htmlspecialchars($donor['availability']); ?></span></p> -->
                <p class="text-gray-600 font-semibold">Distance: <span class="text-blue-600"><?php echo round($donor['distance'], 2); ?> km</span></p>
                
                <?php if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] == true): ?>
                    <form action="request_donation.php" method="POST">
                        <input type="hidden" name="donor_id" value="<?php echo htmlspecialchars($donor['id']); ?>">
                        <button type="submit" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-300">Request Donation</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
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