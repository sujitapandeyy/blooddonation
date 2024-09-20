<?php
require('connection.php');
session_start();

function validate($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {

    // Collect and validate input data
    $fullname = validate($_POST['fullname']);
    $email = validate($_POST['email']);
    $password = validate($_POST['password']);
    $confirm_password = validate($_POST['cpassword']);
    $phone = validate($_POST['phone']);
    $address = validate($_POST['address']);
    $latitude = validate($_POST['latitude']);
    $longitude = validate($_POST['longitude']);
    $user_type = validate($_POST['user_type']);

    // Check for empty fields
    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($address) || empty($user_type)) {
        $error = "Please fill all the fields!";
    } elseif (empty($latitude) || empty($longitude)) {
        $error = "Enter correct address";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $fullname)) {
        $error = "Name must contain only letters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif ($password !== $confirm_password) {
        $error = "Password and confirm password do not match!";
    } else {
        // Check if email already exists
        $stmt = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
        $stmt->bind_param("ss", $email, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists for this user type";
        } else {
            // Insert new user into the users table
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $con->prepare("INSERT INTO users (fullname, email, password, phone, address, user_type,latitude,longitude) VALUES (?, ?, ?, ?, ?, ?,?,?)");
            $stmt->bind_param("ssssssdd", $fullname, $email, $hashed_password, $phone, $address, $user_type, $latitude, $longitude);

            if ($stmt->execute()) {
               
                    $success = "User Registration successful! You can now login.";
                
            } else {
                $error = "Failed to register user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="javascript/addressInput.js"></script>
    <style>
        .formerror {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body class="bg-gray-200">
    <?php @include 'header.php'; ?>

    <section id="registration" class="flex items-center justify-center min-h-screen bg-gray-100 pt-32">
        <div class="w-full max-w-4xl p-8 bg-white shadow-lg rounded-lg">
            <h2 class="text-3xl font-bold text-center text-red-500 mb-6">User Register</h2>

            <form action="userregister.php" name="userRegistrationForm" method="post" class="space-y-6">
                <?php if (isset($error)) : ?>
                    <div class="formerror text-center"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                    <!-- Left Section: Personal Information -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="fullname">Full Name</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Full Name" name="fullname" id="userFullname" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="email" placeholder="Enter Email" name="email" id="userEmail" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="password" placeholder="Enter Password" name="password" id="userPassword" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="cpassword">Re-enter Password</label>
                            <input
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                                type="password" placeholder="ReEnter Password" name="cpassword" id="userCpassword" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="phone">Phone Number</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Phone Number" name="phone" id="userPhone" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Location</label>
                            <input id="location" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Address" name="address" required>
                            <div id="suggestions" class="suggestions"></div>
                            <input type="hidden" id="userLat" name="latitude">
                            <input type="hidden" id="userLong" name="longitude">
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="user_type" value="User">
                <button type="submit" id="RegButton" name="register" class="bg-red-500 text-white px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register Now</button>
                <div class="text-center mt-4 text-lg">Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login now</a></div>
            </form>
        </div>
    </section>

    <script>
         $(document).ready(function () {
            $('#location').on('input', function () {
                var address = $(this).val().trim();
                // Show suggestions for each word or space input
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
                $('#display-lat').text('Latitude: ' + lat);
                $('#display-long').text('Longitude: ' + lon);
                $('#suggestions').empty();
            });

            $('#location').on('keypress', function (e) {
                if (e.which == 13) { // Enter key pressed
                    e.preventDefault();
                    var firstSuggestion = $('#suggestions .suggestion').first();
                    if (firstSuggestion.length > 0) {
                        var placeName = firstSuggestion.text();
                        var lat = firstSuggestion.data('lat');
                        var lon = firstSuggestion.data('lon');

                        $('#location').val(placeName);
                        $('#userLat').val(lat);
                        $('#userLong').val(lon);
                        $('#display-lat').text('Latitude: ' + lat);
                        $('#display-long').text('Longitude: ' + lon);
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
