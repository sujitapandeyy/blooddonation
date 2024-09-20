<?php
require ('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['bankemail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}

// Get the blood bank ID from the session
$bankEmail = $_SESSION['bankemail'];
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $bankEmail);
$stmt->execute();
$stmt->bind_result($bloodbank_id);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campaignName = $_POST['campaign_name'];
    $contactNumber = $_POST['contact_number'];
    $campaignDate = $_POST['campaign_date'];
    $description = $_POST['description'];
    $address = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $sql = "INSERT INTO campaigns (campaign_name, contact_number, campaign_date, description, location, latitude, longitude, bloodbank_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssssdi", $campaignName, $contactNumber, $campaignDate, $description, $address, $latitude, $longitude, $bloodbank_id);

    if ($stmt->execute()) {
        // header("Location: editDonor.php?success=Profile updated successfully!");
        header("Location: addCampaign.php?error=New record created successfully!!");
    } else {
        header("Location:addCampaign.php?error=Failed to create campaign, try again");
    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Campaign</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="../javascript/addressInput.js"></script>

</head>

<body class="bg-gray-200">
    <?php @include ("bloodbankmenu.php"); ?>
    <section class="ml-72 p-8 max-w-4xl">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header text-3xl font-bold text-center justify-center mb-4">Add Campaign</h1>
                </div>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <?php
                $errorMessage = $_GET['error'];
                $errorClass = ($errorMessage === 'New campaign created successfully!!') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                ?>
                <div class="w-full mb-6 p-4 rounded-md text-center font-semibold <?php echo $errorClass; ?>">
                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            <?php endif; ?>

            <form role="form" action="" method="post">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="campaignName">Campaign Name</label>
                    <input
                        id="campaignName"
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Enter Campaign's Name" name="campaign_name" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="contactNumber">Contact Number</label>
                    <input
                        id="contactNumber"
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Enter Phone number" name="contact_number" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="campaignDate">Campaign Date</label>
                    <input
                        id="campaignDate"
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        type="date" name="campaign_date" required>
                </div>

                <div class="mb-4">
                    <label for="location" class="block text-gray-700">Address</label>
                    <input id="location" type="text" name="location"  class="w-full p-2 border border-gray-300 rounded">
                    <div id="suggestions" class="suggestions"></div>
                    <input type="hidden" id="userLat" name="latitude">
                    <input type="hidden" id="userLong" name="longitude" >
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="description">Description</label>
                    <textarea
                        id="description"
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                        placeholder="Enter a description" name="description" required></textarea>
                </div>

                <div class="flex items-center justify-center">
                    <button type="submit"
                        class="px-20 rounded-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 focus:outline-none focus:shadow-outline">Submit</button>
                </div>
            </form>
        </div>
    </section>
    
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


</body>

</html>
