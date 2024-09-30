<?php
require('../connection.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['Adminemail'])) {
    header("Location: ../login.php?error=Login first");
    exit();
}
function validate($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// Handle BloodBank registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Retrieve form inputs
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['cpassword']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = validate($_POST['address']);
    $latitude = validate($_POST['latitude']);
    $longitude = validate($_POST['longitude']);   
    $user_type = 'BloodBank'; 
    $service_type = htmlspecialchars(trim($_POST['servicehour']));
    $custom_hours_start = isset($_POST['customHoursStart']) ? htmlspecialchars(trim($_POST['customHoursStart'])) : null;
    $custom_hours_end = isset($_POST['customHoursEnd']) ? htmlspecialchars(trim($_POST['customHoursEnd'])) : null;

    // Handle image upload
    $default_image = 'img/slide1.png';
    $image_path = $default_image;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../upload/';
        $upload_file = $upload_dir . basename($_FILES['image']['name']);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            header("Location: bankregister.php?error=Invalid file type.");
            exit();
        } elseif ($_FILES['image']['size'] > 5000000) { // 5MB limit
            header("Location: bankregister.php?error=File size exceeds limit.");
            exit();
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
            $image_path = $upload_file;
        } else {
            header("Location: bankregister.php?error=Failed to move uploaded file.");
            exit();
        }
    }

    // Validate form inputs
    if (empty($fullname) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        header("Location: bankregister.php?error=Please fill all the fields!!");
        exit();
    } elseif (empty($latitude) || empty($longitude)) {
        $error = "Enter correct address";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $fullname)) {
        header("Location: bankregister.php?error=Name must contain only letters.");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: bankregister.php?error=Invalid email format.");
        exit();
    } elseif ($password != $confirm_password) {
        header("Location: bankregister.php?error=Password and confirm password do not match.");
        exit();
    } else {
        // Check if the email already exists for the BloodBank user type
        $user_exist_query = $con->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'BloodBank'");
        $user_exist_query->bind_param("s", $email);
        $user_exist_query->execute();
        $result = $user_exist_query->get_result();

        if ($result && $result->num_rows > 0) {
            header("Location: bankregister.php?error=Email already exists for this user type.");
            exit();
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

             // Set service start and end time based on service_type
        if ($service_type == '24-hour') {
            $service_start_time = '00:00:00'; // Midnight
            $service_end_time = '23:59:59'; // End of day
        } else if ($service_type == 'Custom') {
            // Use the custom hours provided by the user
            $service_start_time = ($custom_hours_start) ? date("H:i:s", strtotime($custom_hours_start)) : null;
            $service_end_time = ($custom_hours_end) ? date("H:i:s", strtotime($custom_hours_end)) : null;
        } else {
            $service_start_time = null;
            $service_end_time = null;
        }
            // Insert into users table
            $sql = $con->prepare("INSERT INTO users (fullname, email, password, phone, address, user_type,longitude,latitude) VALUES ( ?, ?, ?, ?, ?, ?,?,?)");
            $sql->bind_param("ssssssss", $fullname, $email, $hashed_password, $phone, $address, $user_type,$longitude,$latitude);

            if ($sql->execute()) {
                $bloodbank_id = $con->insert_id;
                $sql_bloodbank = $con->prepare("INSERT INTO bloodbank (id, service_type, service_start_time, service_end_time,image) VALUES (?, ?, ?, ?,?)");
                $sql_bloodbank->bind_param("issss", $bloodbank_id, $service_type, $service_start_time, $service_end_time, $image_path);

                if ($sql_bloodbank->execute()) {
                    header("Location: BloodBankResult.php?success=Registration successful! BloodBank can login now!!");
                } else {
                    header("Location: bankregister.php?error=Failed to register blood bank details.");
                }
            } else {
                header("Location: bankregister.php?error=Failed to register user.");
            }
            exit();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BloodBank Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script src="../javascript/addressInput.js"></script>
</head>

<body class="bg-gray-100">
    <section id="popup" class="flex  h-auto bg-gray-100">
        <div class="flex w-full max-w-4xl ml-3 bg-white shadow-lg rounded-lg overflow-hidden">
            <div id="bloodBankRegistrationForm" class="w-full p-6">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" name="bloodBankRegistrationForm"
                    method="post" enctype="multipart/form-data">
                    <h2 class="text-3xl font-bold text-center mb-6 text-red-500">BloodBank Register</h2>
                    <?php if (isset($_GET['error'])) { ?>
                        <p class="text-red-500 mb-4 text-center">*<?php echo htmlspecialchars($_GET['error']); ?></p>
                    <?php } ?>
                    <div class="space-y-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">BloodBank Name</label>
                            <input type="text" placeholder="Enter BloodBank Name" name="fullname" id="bankname" required
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Blood Bank Email</label>
                            <input type="email" placeholder="Enter Email" name="email" id="bloodBankEmail" required
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Password</label>
                            <input type="password" placeholder="Enter Password" name="password" id="bloodBankPassword"
                                required
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Confirm Password</label>
                            <input type="password" placeholder="Re-enter Password" name="cpassword"
                                id="bloodBankCpassword" required
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Phone Number </label>
                            <input type="text" placeholder="Enter Phone Number" name="phone" id="bloodBankPhone"
                                required
                                class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="servicehour">Service Hours</label>
                            <select name="servicehour" id="servicehour" required
                                class="w-full md:w-60 text-gray-800 px-4 py-2 mb-2 md:mb-0 md:mr-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                aria-label="Service Hours" onchange="toggleCustomHours()">
                                <option value="" disabled selected>Select Service Hours</option>
                                <option value="24-hour">24 Hours</option>
                                <option value="Custom">Custom Hours</option>
                            </select>
                        </div>

                        <div id="customHours" class="mb-4 hidden">
                            <label class="block text-gray-700 font-bold mb-2" for="customHoursStart">Custom
                                Hours</label>
                            <div class="flex space-x-4">
                                <input type="time" name="customHoursStart" id="customHoursStart"
                                    placeholder="Start Time"
                                    class="w-full md:w-60 text-gray-800 px-4 py-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <input type="time" name="customHoursEnd" id="customHoursEnd" placeholder="End Time"
                                    class="w-full md:w-60 text-gray-800 px-4 py-2 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>


                        </div>

                        <script>
                            function toggleCustomHours() {
                                var select = document.getElementById('servicehour');
                                var customHours = document.getElementById('customHours');
                                if (select.value === 'Custom') {
                                    customHours.classList.remove('hidden');
                                } else {
                                    customHours.classList.add('hidden');
                                }
                            }
                        </script>

                        <!-- <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="address">Campaign Location</label>
                    <input type="text" id="location" placeholder="Enter location" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline"
                    > -->

                    <!-- <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2" for="address">Location</label>
                            <input id="location" class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline" type="text" placeholder="Enter Address" name="address" required>
                            <div id="suggestions" class="suggestions"></div>
                            <input type="hidden" id="userLat" name="latitude">
                            <input type="hidden" id="userLong" name="longitude">
                        </div>
                    </div> -->

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="address">Campaign Location</label>
                        <input type="text" name="address" id="location" placeholder="Enter location"
                        class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                        <div id="suggestions" class="suggestions "></div>
                        <input type="hidden" id="userLat" name="latitude">
                            <input type="hidden" id="userLong" name="longitude">
                        <div>
                            <p id="display-lat"></p>
                            <p id="display-long"></p>
                        </div>
                    </div>
                    <!-- Image Upload Field -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="image">Upload profile</label>
                        <input type="file" name="image" id="image"
                            class="shadow border rounded w-full p-2 text-gray-700 focus:outline-none focus:shadow-outline">
                    </div>
                    <input type="hidden" name="user_type" value="BloodBank">
                    <button type="submit" id="RegButton" name="register"
                        class="bg-red-500 text-white my-5 px-4 py-2 rounded-lg w-full text-lg hover:bg-red-600">Register
                        Now</button>
                </form>
            </div>
        </div>
    </section>
</body>
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