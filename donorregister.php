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
    $dob = validate($_POST['dob']);
    $gender = validate($_POST['gender']);
    $blood_group = validate($_POST['blood_group']);
    $weight = validate($_POST['weight']);
    $last_donation_date = validate($_POST['last_donation_date']);
    $user_type = validate($_POST['user_type']);

    // Check for empty fields
      if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($address) || empty($dob) || empty($gender) || empty($blood_group) || empty($weight) || empty($user_type)) {
        $error = "Please fill all the fields!";
    } elseif (empty($latitude) || empty($longitude)) {
        $error = "Enter correct address";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $fullname)) {
        $error = "Name must contain only letters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif ($password !== $confirm_password) {
        $error = "Password and confirm password do not match!";
    } elseif (!preg_match('/^\d{10}$/', $phone)) { // Validate phone number
        $error = "Phone number invalid!";
    } else {
        // Check if email already exists
        $stmt = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
        $stmt->bind_param("ss", $email, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists for this user type";
        } else {
            // Calculate availability and eligibility
            $availability = 'Available'; // Default value
            $days_until_eligible = NULL; // Default value

            if (!empty($last_donation_date)) {
                $last_donation_date_obj = new DateTime($last_donation_date);
                $current_date = new DateTime();
                $interval = $current_date->diff($last_donation_date_obj);
                $months = ($interval->y * 12) + $interval->m;

                if ($months < 3) {
                    $availability = 'Not Available';
                    $days_until_eligible = 90 - ($interval->days % 90);
                }
                // Convert the DateTime object to a string format for SQL insertion
                $last_donation_date_str = $last_donation_date_obj->format('Y-m-d');
            } else {
                // If no last donation date is provided, set to NULL
                $last_donation_date_str = NULL;
            }

            // Insert new donor into the users and donor tables
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $con->prepare("INSERT INTO users (fullname, email, password, phone, address, user_type, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $fullname, $email, $hashed_password, $phone, $address, $user_type, $latitude, $longitude);

            if ($stmt->execute()) {
                $user_id = $con->insert_id;
                $stmt = $con->prepare("INSERT INTO donor (id, donor_blood_type, dob, weight, gender, last_donation_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $user_id, $blood_group, $dob, $weight, $gender, $last_donation_date_str);

                if ($stmt->execute()) {
                    header("Location: login.php?error=Donor Registration successful! You can now login!!");
                } else {
                    $error = "Failed to register donor details.";
                }
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
    <title>Donor Registration</title>
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
            <h2 class="text-3xl font-bold text-center text-red-500 mb-6">Donor Register</h2>

            <form action="donorregister.php" name="donorRegistrationForm" method="post" class="space-y-6">
                <?php if (isset($error)) : ?>
                    <div class="formerror text-center"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)) : ?>
                    <div class="formerror text-center text-green-500"><?php echo $success; ?></div>
                <?php endif; ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Section: Personal Information -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="fullname">Full Name</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Full Name" name="fullname" id="donorFullname" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="email" placeholder="Enter Email" name="email" id="donorEmail" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="password" placeholder="Enter Password" name="password" id="donorPassword" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="cpassword">Re-enter Password</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="password" placeholder="ReEnter Password" name="cpassword" id="donorCpassword" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="phone">Phone Number</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Phone Number" name="phone" id="donorPhone" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="dob">Date of Birth</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="date" name="dob" id="donorDob" required>
                        </div>
                    </div>

                    <!-- Right Section: Health Information -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="gender">Gender</label>
                            <select class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" name="gender" id="donorGender" required>
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="blood_group">Blood Group</label>
                            <select class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" name="blood_group" id="donorBloodGroup" required>
                                <option value="" disabled selected>Select Blood Group</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>

                       

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="weight">Weight (kg)</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="number" step="0.1" placeholder="Enter Weight" name="weight" id="donorWeight" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Location</label>
                            <input id="location" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Address" name="address" required>
                            <div id="suggestions" class="suggestions"></div>
                            <input type="hidden" id="userLat" name="latitude">
                            <input type="hidden" id="userLong" name="longitude">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="last_donation_date">Last Donation Date (Optional)</label>
                            <input class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="date" name="last_donation_date" id="donorLastDonationDate">
                        </div>
                        </div>
</div>
<input type="hidden" name="user_type" value="Donor">
                <button type="submit" id="RegButton" name="register" class="bg-red-500 text-white px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register Now</button>
                <div class="text-center mt-4 text-lg">Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Login now</a></div>
                </div>
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